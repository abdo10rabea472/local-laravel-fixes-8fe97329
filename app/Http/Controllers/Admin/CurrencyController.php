<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\AuditLog;
use App\Models\Currency;
use App\Support\MailHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('sort_order')->orderBy('id')->get();
        $activeTab = 'currencies';
        return view('admin.settings.currencies', compact('currencies', 'activeTab'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active']  = (bool) ($data['is_active'] ?? true);
        $data['code'] = strtoupper($data['code']);
        Currency::create($data);
        return back()->with('success', 'Currency created.');
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $this->validateData($request, $currency->id);
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active']  = (bool) ($data['is_active'] ?? false);
        $data['code'] = strtoupper($data['code']);
        $currency->update($data);
        return back()->with('success', 'Currency updated.');
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'Cannot delete the default currency.');
        }
        $currency->delete();
        return back()->with('success', 'Currency deleted.');
    }

    public function setDefault(Request $request, Currency $currency)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 403);

        $key = 'cur-default:'.$admin->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'otp' => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }

        $sessionKey = 'cur_default_otp:'.$currency->id;

        // ---------- STEP 2: OTP submission ----------
        if ($request->filled('otp')) {
            $payload = $request->session()->get($sessionKey);
            if (!$payload || ($payload['admin_id'] ?? null) !== $admin->id || ($payload['expires_at'] ?? 0) < time()) {
                $request->session()->forget($sessionKey);
                throw ValidationException::withMessages(['otp' => 'OTP expired. Please restart the process.']);
            }
            $otp = preg_replace('/\D/', '', (string) $request->input('otp'));
            if (!hash_equals((string) $payload['hash'], hash('sha256', $otp))) {
                RateLimiter::hit($key, 600);
                throw ValidationException::withMessages(['otp' => 'Invalid OTP code.']);
            }

            RateLimiter::clear($key);
            $request->session()->forget($sessionKey);

            $previous = Currency::where('is_default', true)->where('id', '!=', $currency->id)->pluck('code')->all();

            DB::transaction(function () use ($currency) {
                // New base currency: its rate is 1, and all OTHER currencies must be
                // re-checked. We mark them so the admin must update them.
                Currency::where('id', '!=', $currency->id)->update(['exchange_rate' => 0]);
                $currency->update(['is_default' => true, 'is_active' => true, 'exchange_rate' => 1]);
            });

            $needsUpdate = Currency::where('id', '!=', $currency->id)->pluck('code')->all();

            AuditLog::create([
                'action'     => 'currency.set_default',
                'actor_type' => 'admin',
                'actor_id'   => $admin->id,
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'context'    => json_encode([
                    'currency_id'    => $currency->id,
                    'currency_code'  => $currency->code,
                    'previous'       => $previous,
                    'reset_rates_for'=> $needsUpdate,
                    'verified_via'   => 'email_otp',
                ]),
            ]);

            $msg = 'Default currency updated securely. ⚠️ Exchange rates for ['
                .implode(', ', $needsUpdate).'] were reset to 0 — please update each rate now.';
            return redirect()->route('admin.settings.currencies.index')->with('warning', $msg);
        }

        // ---------- STEP 1: password + typed code → send OTP ----------
        $request->validate([
            'password'     => ['required', 'string'],
            'confirm_code' => ['required', 'string'],
            'understand'   => ['accepted'],
        ]);

        if (!Hash::check($request->password, $admin->password)) {
            RateLimiter::hit($key, 600);
            return back()
                ->withErrors(['password' => 'Incorrect password.'])
                ->with('default_error_for_currency', $currency->id);
        }

        if (!hash_equals(strtoupper((string) $currency->code), strtoupper((string) $request->confirm_code))) {
            RateLimiter::hit($key, 600);
            return back()
                ->withErrors(['confirm_code' => 'Confirmation text does not match the currency code.'])
                ->with('default_error_for_currency', $currency->id);
        }

        if ($reason = MailHealth::failureReason()) {
            return back()->with('error', 'Cannot send OTP — '.$reason);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->session()->put($sessionKey, [
            'admin_id'   => $admin->id,
            'hash'       => hash('sha256', $otp),
            'expires_at' => time() + 600,
        ]);

        try {
            Mail::to($admin->email)->send(new AdminOtpMail(
                adminName:    (string) ($admin->name ?? $admin->email),
                otp:          $otp,
                actionTitle:  'Change Default Currency',
                actionDetail: "Set \"{$currency->name}\" ({$currency->code}) as the site's base currency. "
                              ."All other currencies' exchange rates will be reset to 0 and must be re-entered.",
                ip:           (string) $request->ip(),
                userAgent:    substr((string) $request->userAgent(), 0, 200),
                expiresInMinutes: 10,
            ));
        } catch (\Throwable $e) {
            $request->session()->forget($sessionKey);
            return back()->with('error', 'Could not send OTP email: '.$e->getMessage().'. Please check your mail settings.');
        }

        AuditLog::create([
            'action'     => 'currency.set_default.otp_sent',
            'actor_type' => 'admin',
            'actor_id'   => $admin->id,
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'context'    => json_encode([
                'currency_id'   => $currency->id,
                'currency_code' => $currency->code,
                'email_to_mask' => Str::mask((string) $admin->email, '*', 2, max(1, strpos((string) $admin->email, '@') - 4)),
            ]),
        ]);

        return back()
            ->with('otp_sent_for_currency', $currency->id)
            ->with('success', 'A verification code was emailed to '.Str::mask((string) $admin->email, '*', 2, max(1, strpos((string) $admin->email, '@') - 4)).'. Enter it to complete the change.');
    }

    protected function validateData(Request $r, ?int $ignoreId = null): array
    {
        return $r->validate([
            'name'                => ['required', 'string', 'max:80'],
            'code'                => ['required', 'string', 'max:10', 'alpha', 'unique:currencies,code' . ($ignoreId ? ",{$ignoreId}" : '')],
            'symbol'              => ['required', 'string', 'max:10'],
            'symbol_position'     => ['required', 'in:before,after'],
            'decimals'            => ['required', 'integer', 'min:0', 'max:6'],
            'decimal_separator'   => ['required', 'string', 'max:4'],
            'thousands_separator' => ['nullable', 'string', 'max:4'],
            'exchange_rate'       => ['required', 'numeric', 'min:0'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'is_default'          => ['nullable', 'boolean'],
            'is_active'           => ['nullable', 'boolean'],
        ]);
    }
}
