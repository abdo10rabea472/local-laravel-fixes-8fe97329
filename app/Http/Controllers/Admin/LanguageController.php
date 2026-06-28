<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function setDefault(Language $language)
    {
        $language->update(['is_default' => true, 'is_active' => true]);
        return back()->with('success', 'Default language updated.');
    }

    /**
     * Show translation editor: lists every key from the default-language PHP files
     * and lets the admin fill in the value for $language. Saved to
     * resources/lang/{code}/{group}.php.
     */
    public function translations(Language $language)
    {
        $defaultCode = optional(Language::where('is_default', true)->first())->code ?? 'en';
        $sourceDir   = base_path("resources/lang/{$defaultCode}");
        $targetDir   = base_path("resources/lang/{$language->code}");

        $groups = [];
        if (is_dir($sourceDir)) {
            foreach (glob($sourceDir.'/*.php') as $file) {
                $group = basename($file, '.php');
                $sourceKeys = include $file;
                $targetFile = $targetDir.'/'.$group.'.php';
                $targetKeys = is_file($targetFile) ? (include $targetFile) : [];
                $groups[$group] = [
                    'keys'   => is_array($sourceKeys) ? array_keys($sourceKeys) : [],
                    'source' => is_array($sourceKeys) ? $sourceKeys : [],
                    'target' => is_array($targetKeys) ? $targetKeys : [],
                ];
            }
        }

        $activeTab = 'languages';
        return view('admin.settings.languages-translations', compact('language', 'groups', 'defaultCode', 'activeTab'));
    }

    public function saveTranslations(Request $request, Language $language)
    {
        $payload = $request->input('t', []); // ['group' => ['key' => 'value', ...]]
        if (!is_array($payload)) {
            return back()->with('error', 'Invalid payload.');
        }

        $targetDir = base_path("resources/lang/{$language->code}");
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        foreach ($payload as $group => $entries) {
            if (!is_string($group) || !preg_match('/^[a-z0-9_\-]+$/i', $group) || !is_array($entries)) {
                continue;
            }
            // Merge with existing so untouched keys are preserved.
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
            file_put_contents(
                $file,
                "<?php\n\nreturn ".var_export($existing, true).";\n"
            );
        }

        // Clear translation cache.
        if (function_exists('opcache_reset')) { @opcache_reset(); }

        return back()->with('success', 'Translations saved for '.$language->name.'.');
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
