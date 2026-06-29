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
