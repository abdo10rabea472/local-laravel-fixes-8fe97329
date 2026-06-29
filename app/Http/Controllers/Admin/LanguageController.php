<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\AuditLog;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::orderBy('sort_order')->orderBy('id')->get();
        $activeTab = 'languages';
        return view('admin.settings.languages', compact('languages', 'activeTab'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['flag'] = $this->handleFlag($request);
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active']  = (bool) ($data['is_active'] ?? true);

        Language::create($data);
        return back()->with('success', 'Language created.');
    }

    public function update(Request $request, Language $language)
    {
        $data = $this->validateData($request, $language->id);
        if ($flag = $this->handleFlag($request)) {
            if ($language->flag) Storage::disk('public')->delete($language->flag);
            $data['flag'] = $flag;
        }
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active']  = (bool) ($data['is_active'] ?? false);

        $language->update($data);
        return back()->with('success', 'Language updated.');
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return back()->with('error', 'Cannot delete the default language.');
        }
        if ($language->flag) Storage::disk('public')->delete($language->flag);
        $language->delete();
        return back()->with('success', 'Language deleted.');
    }

    public function setDefault(Request $request, Language $language)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 403);

        $key = 'lang-default:'.$admin->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'otp' => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }

        $sessionKey = 'lang_default_otp:'.$language->id;

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

            $previous = Language::where('is_default', true)->where('id', '!=', $language->id)->pluck('code')->all();
            $language->update(['is_default' => true, 'is_active' => true]);

            AuditLog::create([
                'action'     => 'language.set_default',
                'actor_type' => 'admin',
                'actor_id'   => $admin->id,
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'context'    => json_encode([
                    'language_id'   => $language->id,
                    'language_code' => $language->code,
                    'previous'      => $previous,
                    'verified_via'  => 'email_otp',
                ]),
            ]);

            return back()->with('success', 'Default language updated securely (OTP verified).');
        }

        // ---------- STEP 1: password + typed code → send OTP ----------
        $request->validate([
            'password'     => ['required', 'string'],
            'confirm_code' => ['required', 'string'],
            'understand'   => ['accepted'],
        ]);

        if (!Hash::check($request->password, $admin->password)) {
            RateLimiter::hit($key, 600);
            throw ValidationException::withMessages(['password' => 'Incorrect password.']);
        }

        if (!hash_equals((string) $language->code, (string) $request->confirm_code)) {
            RateLimiter::hit($key, 600);
            throw ValidationException::withMessages([
                'confirm_code' => 'Confirmation text does not match the language code.',
            ]);
        }

        // Pre-flight: ensure mail is configured before we generate an OTP.
        if ($reason = \App\Support\MailHealth::failureReason()) {
            return back()->with('error', 'Cannot send OTP — '.$reason);
        }

        // Generate 6-digit OTP, store hashed in session, email it.
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->session()->put($sessionKey, [
            'admin_id'   => $admin->id,
            'hash'       => hash('sha256', $otp),
            'expires_at' => time() + 600, // 10 minutes
        ]);

        try {
            Mail::to($admin->email)->send(new AdminOtpMail(
                adminName:    (string) ($admin->name ?? $admin->email),
                otp:          $otp,
                actionTitle:  'Change Default Language',
                actionDetail: "Set \"{$language->name}\" ({$language->code}) as the site's default language.",
                ip:           (string) $request->ip(),
                userAgent:    substr((string) $request->userAgent(), 0, 200),
                expiresInMinutes: 10,
            ));
        } catch (\Throwable $e) {
            $request->session()->forget($sessionKey);
            throw ValidationException::withMessages(['confirm_code' => 'Could not send OTP email: '.$e->getMessage()]);
        }


        AuditLog::create([
            'action'     => 'language.set_default.otp_sent',
            'actor_type' => 'admin',
            'actor_id'   => $admin->id,
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'context'    => json_encode([
                'language_id'   => $language->id,
                'language_code' => $language->code,
                'email_to_mask' => Str::mask((string) $admin->email, '*', 2, max(1, strpos((string) $admin->email, '@') - 4)),
            ]),
        ]);

        return back()
            ->with('otp_sent_for_language', $language->id)
            ->with('success', 'A verification code was emailed to '.Str::mask((string) $admin->email, '*', 2, max(1, strpos((string) $admin->email, '@') - 4)).'. Enter it to complete the change.');
    }

    /**
     * Show translation editor: lists every key from the default-language PHP files
     * and lets the admin fill in the value for $language. Saved to
     * resources/lang/{code}/{group}.php.
     */
    public function translations(Language $language)
    {
        $defaultCode = 'en';
        $sourceDir   = base_path("resources/lang/{$defaultCode}");
        $targetDir   = base_path("resources/lang/{$language->code}");

        $groups = [];

        // 1) Load all existing keys from en/*.php
        if (is_dir($sourceDir)) {
            foreach (glob($sourceDir.'/*.php') as $file) {
                $group = basename($file, '.php');
                $src = include $file;
                $groups[$group]['source'] = is_array($src) ? $src : [];
            }
        }

        // 2) Scan codebase for __('group.key') / trans('group.key') / @lang('group.key')
        $scanDirs = [base_path('resources/views'), base_path('app'), base_path('routes')];
        $pattern = "/(?:__|trans|@lang)\\(\\s*['\"]([a-z0-9_\\-]+)\\.([a-zA-Z0-9_\\.\\-]+)['\"]/";
        foreach ($scanDirs as $dir) {
            if (!is_dir($dir)) continue;
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($rii as $f) {
                if (!$f->isFile()) continue;
                $ext = strtolower($f->getExtension());
                if (!in_array($ext, ['php','blade.php','blade'])) {
                    if (!str_ends_with($f->getFilename(), '.blade.php') && $ext !== 'php') continue;
                }
                $content = @file_get_contents($f->getPathname());
                if ($content === false) continue;
                if (preg_match_all($pattern, $content, $m, PREG_SET_ORDER)) {
                    foreach ($m as $hit) {
                        $g = $hit[1]; $k = $hit[2];
                        if (!isset($groups[$g]['source'][$k])) {
                            $groups[$g]['source'][$k] = $groups[$g]['source'][$k] ?? '';
                        }
                    }
                }
            }
        }

        // 3) Attach target values and key list
        foreach ($groups as $group => &$data) {
            ksort($data['source']);
            $targetFile = $targetDir.'/'.$group.'.php';
            $tgt = is_file($targetFile) ? (include $targetFile) : [];
            $data['target'] = is_array($tgt) ? $tgt : [];
            $data['keys']   = array_keys($data['source']);
        }
        unset($data);
        ksort($groups);

        $activeTab = 'languages';
        return view('admin.settings.languages-translations', compact('language', 'groups', 'defaultCode', 'activeTab'));
    }


    public function saveTranslations(Request $request, Language $language)
    {
        $payload = $request->input('t', []); // ['group' => ['key' => 'value', ...]]
        $newKeys = $request->input('new', []); // [['group'=>..,'key'=>..,'en'=>..,'value'=>..], ...]

        if (!is_array($payload)) $payload = [];
        if (!is_array($newKeys)) $newKeys = [];

        $this->writeGroups($language->code, $payload);

        // Handle new keys: write source (en) + target language at once.
        $enAdds = [];
        $tgtAdds = [];
        foreach ($newKeys as $row) {
            if (!is_array($row)) continue;
            $group = trim((string)($row['group'] ?? ''));
            $key   = trim((string)($row['key'] ?? ''));
            $en    = (string)($row['en'] ?? '');
            $val   = (string)($row['value'] ?? '');
            if ($group === '' || $key === '') continue;
            if (!preg_match('/^[a-z0-9_\-]+$/i', $group)) continue;
            if (!preg_match('/^[a-zA-Z0-9_\.\-]+$/', $key)) continue;
            if ($en !== '')  $enAdds[$group][$key] = $en;
            if ($val !== '') $tgtAdds[$group][$key] = $val;
            // If only english given, still create the slot in target as empty? skip.
        }
        if (!empty($enAdds)) $this->writeGroups('en', $enAdds);
        if (!empty($tgtAdds) && $language->code !== 'en') $this->writeGroups($language->code, $tgtAdds);

        if (function_exists('opcache_reset')) { @opcache_reset(); }

        return back()->with('success', 'Translations saved for '.$language->name.'.');
    }

    /**
     * Translate ONE key via AI and save it immediately, so a partial run is
     * preserved if the API quota / token budget runs out mid-batch.
     * POST { group, key, source } -> { ok, translation }
     */
    public function aiTranslateOne(Request $request, Language $language)
    {
        $data = $request->validate([
            'group'  => ['required', 'string', 'regex:/^[a-z0-9_\-]+$/i', 'max:60'],
            'key'    => ['required', 'string', 'regex:/^[a-zA-Z0-9_\.\-]+$/', 'max:120'],
            'source' => ['required', 'string', 'max:4000'],
            'overwrite' => ['nullable', 'boolean'],
        ]);

        // Skip if already translated (unless overwrite=true)
        $targetFile = base_path("resources/lang/{$language->code}/{$data['group']}.php");
        if (!($data['overwrite'] ?? false) && is_file($targetFile)) {
            $existing = include $targetFile;
            if (is_array($existing) && !empty($existing[$data['key']])) {
                return response()->json([
                    'ok' => true,
                    'translation' => (string) $existing[$data['key']],
                    'skipped' => true,
                ]);
            }
        }

        if (!\App\Services\AiService::isEnabled()) {
            return response()->json([
                'ok' => false,
                'message' => 'AI غير مفعّل. فعّله من إعدادات الموقع → AI.',
            ], 422);
        }

        $sys = "You are a professional UI translator. Translate the user's text from English to {$language->name} ({$language->native_name}, code: {$language->code}). "
             . "Rules: keep placeholders like :name, :count, {0}, {{var}}, %s, HTML tags, and punctuation intact. "
             . "Preserve leading/trailing whitespace. Do not add quotes, explanations, or extra text. Return ONLY the translation.";

        try {
            $ai = new \App\Services\AiService();
            $translation = trim($ai->chat([
                ['role' => 'system', 'content' => $sys],
                ['role' => 'user',   'content' => $data['source']],
            ], maxTokens: 1024, temperature: 0.2, timeout: 45));

            if ($translation === '') {
                return response()->json(['ok' => false, 'message' => 'Empty translation returned.'], 422);
            }

            // Persist immediately to disk for this single key.
            $this->writeGroups($language->code, [$data['group'] => [$data['key'] => $translation]]);
            if (function_exists('opcache_reset')) { @opcache_reset(); }

            return response()->json(['ok' => true, 'translation' => $translation]);
        } catch (\Throwable $e) {
            \Log::error('AI translate exception', ['err' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Export all translation groups of a language as a JSON file.
     */
    public function exportTranslations(Language $language)
    {
        $dir = base_path("resources/lang/{$language->code}");
        $out = [];
        if (is_dir($dir)) {
            foreach (glob($dir.'/*.php') as $file) {
                $group = basename($file, '.php');
                $data = include $file;
                if (is_array($data)) $out[$group] = $data;
            }
        }
        $filename = "translations-{$language->code}-".now()->format('Ymd-His').'.json';
        return response()->streamDownload(function () use ($out) {
            echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    /**
     * Import translations for a language from a JSON file.
     * Expected shape: { "<group>": { "<key>": "<value>", ... }, ... }
     */
    public function importTranslations(Request $request, Language $language)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:application/json,text/plain,text/json', 'max:5120'],
            'mode' => ['nullable', 'in:merge,replace'],
        ]);

        $raw = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return back()->with('error', 'Invalid JSON file.');
        }

        $mode = $request->input('mode', 'merge');
        $payload = [];
        $count = 0;
        foreach ($data as $group => $entries) {
            if (!is_string($group) || !preg_match('/^[a-z0-9_\-]+$/i', $group) || !is_array($entries)) continue;
            $clean = [];
            foreach ($entries as $k => $v) {
                if (!is_string($k) || !preg_match('/^[a-zA-Z0-9_\.\-]+$/', $k)) continue;
                $clean[$k] = (string) $v;
                $count++;
            }
            if ($clean) $payload[$group] = $clean;
        }

        if ($mode === 'replace') {
            $dir = base_path("resources/lang/{$language->code}");
            if (is_dir($dir)) {
                foreach (array_keys($payload) as $group) {
                    $file = $dir.'/'.$group.'.php';
                    if (is_file($file)) @unlink($file);
                }
            }
        }

        $this->writeGroups($language->code, $payload);
        if (function_exists('opcache_reset')) { @opcache_reset(); }

        return back()->with('success', "Imported {$count} translation(s) for {$language->name}.");
    }


    protected function writeGroups(string $code, array $payload): void
    {
        $targetDir = base_path("resources/lang/{$code}");
        if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);

        foreach ($payload as $group => $entries) {
            if (!is_string($group) || !preg_match('/^[a-z0-9_\-]+$/i', $group) || !is_array($entries)) {
                continue;
            }
            $existing = [];
            $file = $targetDir.'/'.$group.'.php';
            if (is_file($file)) {
                $existing = include $file;
                if (!is_array($existing)) $existing = [];
            }
            foreach ($entries as $k => $v) {
                if (!is_string($k)) continue;
                $v = (string) $v;
                if ($v === '') {
                    unset($existing[$k]);
                } else {
                    $existing[$k] = $v;
                }
            }
            ksort($existing);
            file_put_contents($file, "<?php\n\nreturn ".var_export($existing, true).";\n");
        }
    }


    protected function validateData(Request $r, ?int $ignoreId = null): array
    {
        return $r->validate([
            'name'        => ['required', 'string', 'max:80'],
            'native_name' => ['required', 'string', 'max:80'],
            'code'        => ['required', 'string', 'max:10', 'alpha_dash', 'unique:languages,code' . ($ignoreId ? ",{$ignoreId}" : '')],
            'locale'      => ['required', 'string', 'max:20'],
            'direction'   => ['required', 'in:ltr,rtl'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_default'  => ['nullable', 'boolean'],
            'is_active'   => ['nullable', 'boolean'],
            'flag_file'   => ['nullable', 'image', 'max:1024'],
        ]);
    }

    protected function handleFlag(Request $r): ?string
    {
        if (!$r->hasFile('flag_file')) return null;
        return $r->file('flag_file')->store('languages', 'public');
    }
}
