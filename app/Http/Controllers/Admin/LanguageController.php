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
