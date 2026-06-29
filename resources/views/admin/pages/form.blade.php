@extends('admin.settings.layout')

@section('settings-content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="text-base font-bold text-slate-800">{{ $page->exists ? __('app.admin_pages_form_edit_title') . ': ' . $page->title : __('app.admin_pages_form_create_title') }}</h3>
        <p class="text-xs text-slate-500 mt-1">{{ $page->exists ? __('app.admin_pages_form_edit_sub') : __('app.admin_pages_form_create_sub') }}</p>
    </div>

    <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        @if($page->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_title_label') }}</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_slug_label') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
        </div>

        @php
            $isFaqsPage = $page->slug === 'faqs';
            $isAboutPage = $page->slug === 'about';
            $faqItems = [];
            if ($isFaqsPage) {
                $decoded = json_decode((string) old('content', $page->content), true);
                if (is_array($decoded)) {
                    foreach ($decoded as $row) {
                        if (is_array($row) && isset($row['q'], $row['a'])) {
                            $faqItems[] = ['q' => (string) $row['q'], 'a' => (string) $row['a'], 'category' => (string) ($row['category'] ?? '')];
                        }
                    }
                }
                if (empty($faqItems)) {
                    $faqItems[] = ['q' => '', 'a' => '', 'category' => ''];
                }
            }

            $aboutDefaults = [
                'hero' => [
                    'title'    => __('app.about_hero_title'),
                    'subtitle' => __('app.about_hero_subtitle'),
                ],
                'story' => [
                    'title' => __('app.about_story_title'),
                    'p1'    => __('app.about_story_p1', ['years' => 5]),
                    'p2'    => __('app.about_story_p2'),
                ],
                'stats' => [
                    ['label' => __('app.about_stat_products'),   'value' => '', 'color' => 'violet'],
                    ['label' => __('app.about_stat_categories'), 'value' => '', 'color' => 'indigo'],
                    ['label' => __('app.about_stat_customers'),  'value' => '', 'color' => 'emerald'],
                    ['label' => __('app.about_stat_years'),      'value' => '', 'color' => 'amber'],
                ],
                'cards' => [
                    ['icon' => 'fa-bullseye',  'title' => __('app.about_mission_title'), 'desc' => __('app.about_mission_desc'), 'color' => 'violet'],
                    ['icon' => 'fa-eye',       'title' => __('app.about_vision_title'),  'desc' => __('app.about_vision_desc'),  'color' => 'indigo'],
                    ['icon' => 'fa-handshake', 'title' => __('app.about_values_title'),  'desc' => __('app.about_values_desc'),  'color' => 'emerald'],
                ],
                'team_title' => __('app.about_team_title'),
                'team' => [
                    ['name' => __('app.about_team_member1_name'), 'role' => __('app.about_team_member1_role')],
                    ['name' => __('app.about_team_member2_name'), 'role' => __('app.about_team_member2_role')],
                    ['name' => __('app.about_team_member3_name'), 'role' => __('app.about_team_member3_role')],
                    ['name' => __('app.about_team_member4_name'), 'role' => __('app.about_team_member4_role')],
                ],
            ];
            $aboutData = $aboutDefaults;
            if ($isAboutPage) {
                $decoded = json_decode((string) old('content', $page->content), true);
                if (is_array($decoded)) {
                    $aboutData = array_replace_recursive($aboutDefaults, $decoded);
                }
            }
        @endphp


        @if($isFaqsPage)
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_faqs_label') }}</label>
                        <p class="text-[11px] text-slate-400 mt-1">
                            {{ __('app.admin_pages_form_faq_total') }}: <span id="faq-total" class="font-bold text-slate-600">{{ count($faqItems) }}</span>
                            — {{ __('app.admin_pages_form_faq_hint') }}
                        </p>
                    </div>
                    <button type="button" id="faq-add" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-4 py-2 rounded-xl">
                        <i class="fa-solid fa-plus"></i> {{ __('app.admin_pages_form_add_faq') }}
                    </button>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" id="faq-search" @lang('app.admin_pages_form_faq_search_placeholder')
                               class="w-full h-11 ps-10 pe-4 bg-white border border-slate-200 rounded-xl text-sm focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    </div>
                    <select id="faq-perpage" class="h-11 px-3 bg-white border border-slate-200 rounded-xl text-sm">
                        <option value="20">20 / {{ __('app.admin_pages_form_per_page') }}</option>
                        <option value="50">50 / {{ __('app.admin_pages_form_per_page') }}</option>
                        <option value="100">100 / {{ __('app.admin_pages_form_per_page') }}</option>
                    </select>
                </div>

                <div id="faq-list" class="space-y-3">
                    @foreach($faqItems as $i => $item)
                    <div class="faq-row bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3" data-idx="{{ $i }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_faq_num') }} <span class="faq-index">{{ $i + 1 }}</span></span>
                            <button type="button" class="faq-remove text-rose-500 hover:text-rose-700 text-xs font-bold inline-flex items-center gap-1">
                                <i class="fa-solid fa-trash"></i> حذف
                            </button>
                        </div>
                        <input type="text" class="faq-q w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm" placeholder="السؤال" value="{{ $item['q'] }}">
                        <textarea class="faq-a w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm" rows="3" placeholder="الإجابة">{{ $item['a'] }}</textarea>
                        @php
                            $defaultCats = ['Shipping', 'Payment', 'Warranty', 'Returns', 'Support', 'Orders', 'General'];
                        @endphp
                        <div class="flex items-center gap-2">
                            <select class="faq-cat flex-1 h-10 px-3 bg-white border border-slate-200 rounded-xl text-xs">
                                <option value="">{{ __('app.admin_pages_form_select_category') }}</option>
                                @foreach($defaultCats as $cat)
                                    <option value="{{ $cat }}" @selected($item['category'] === $cat)>{{ $cat }}</option>
                                @endforeach
                                @if($item['category'] && !in_array($item['category'], $defaultCats, true))
                                    <option value="{{ $item['category'] }}" selected>{{ $item['category'] }}</option>
                                @endif
                            </select>
                            <button type="button" class="faq-cat-new inline-flex items-center gap-1 h-10 px-3 bg-violet-50 hover:bg-violet-100 text-violet-700 rounded-xl text-xs font-bold whitespace-nowrap">
                                <i class="fa-solid fa-plus"></i> تصنيف جديد
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>


                <div id="faq-empty" class="hidden text-center py-10 text-sm text-slate-500 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                    لا توجد نتائج مطابقة للبحث.
                </div>

                <div id="faq-pager" class="flex items-center justify-between gap-3 pt-2">
                    <button type="button" id="faq-prev" class="inline-flex items-center gap-2 h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right"></i> {{ __('app.admin_pages_form_prev') }}
                    </button>
                    <div class="text-xs text-slate-500">
                        {{ __('app.admin_pages_form_page') }} <span id="faq-page" class="font-bold text-slate-700">1</span> {{ __('app.admin_pages_form_of') }} <span id="faq-pages" class="font-bold text-slate-700">1</span>
                        · <span id="faq-shown">0</span> ظاهر
                    </div>
                    <button type="button" id="faq-next" class="inline-flex items-center gap-2 h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        {{ __('app.admin_pages_form_next') }} <i class="fa-solid fa-chevron-left"></i>
                    </button>
                </div>

                <textarea name="content" id="content-editor" class="hidden">{{ old('content', $page->content) }}</textarea>
            </div>
        @elseif($isAboutPage)
            @php
                $sectionClass = 'bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4';
                $inputClass   = 'w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm';
                $areaClass    = 'w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm';
                $labelClass   = 'text-[11px] font-bold text-slate-500 uppercase tracking-wider';
                $colorOptions = ['violet','indigo','emerald','amber','rose','sky','blue','fuchsia','teal','orange'];
            @endphp

            <div id="about-editor" class="space-y-5">
                <p class="text-xs text-slate-500 -mb-1">{{ __('app.admin_pages_form_about_hint') }}</p>

                {{-- Hero --}}
                <div class="{{ $sectionClass }}">
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-bullhorn text-violet-500"></i> {{ __('app.admin_pages_form_about_hero_section') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="{{ $labelClass }}">العنوان</label>
                            <input type="text" data-about="hero.title" value="{{ $aboutData['hero']['title'] }}" class="{{ $inputClass }}">
                        </div>
                        <div class="space-y-1.5">
                            <label class="{{ $labelClass }}">{{ __('app.admin_pages_form_label_subtitle') }}</label>
                            <input type="text" data-about="hero.subtitle" value="{{ $aboutData['hero']['subtitle'] }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                </div>

                {{-- Story --}}
                <div class="{{ $sectionClass }}">
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-book-open text-indigo-500"></i> {{ __('app.admin_pages_form_about_story_section') }}
                    </h4>
                    <div class="space-y-1.5">
                        <label class="{{ $labelClass }}">العنوان</label>
                        <input type="text" data-about="story.title" value="{{ $aboutData['story']['title'] }}" class="{{ $inputClass }}">
                    </div>
                    <div class="space-y-1.5">
                        <label class="{{ $labelClass }}">{{ __('app.admin_pages_form_label_p1') }}</label>
                        <textarea data-about="story.p1" rows="3" class="{{ $areaClass }}">{{ $aboutData['story']['p1'] }}</textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="{{ $labelClass }}">{{ __('app.admin_pages_form_label_p2') }}</label>
                        <textarea data-about="story.p2" rows="3" class="{{ $areaClass }}">{{ $aboutData['story']['p2'] }}</textarea>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="{{ $sectionClass }}">
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-emerald-500"></i> {{ __('app.admin_pages_form_about_stats_section') }}
                    </h4>
                    <p class="text-[11px] text-slate-400">{{ __('app.admin_pages_form_stats_hint') }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($aboutData['stats'] as $i => $stat)
                            <div class="bg-white border border-slate-200 rounded-xl p-3 space-y-2" data-about-stat="{{ $i }}">
                                <div class="text-[11px] font-bold text-slate-400">{{ __('app.admin_pages_form_card_num') }} {{ $i + 1 }}</div>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" data-about="stats.{{ $i }}.label" value="{{ $stat['label'] }}" placeholder="{{ __('app.admin_pages_form_placeholder_label') }}" class="col-span-2 h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                    <input type="text" data-about="stats.{{ $i }}.value" value="{{ $stat['value'] }}" placeholder="{{ __('app.admin_pages_form_placeholder_value') }}" class="h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                </div>
                                <select data-about="stats.{{ $i }}.color" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                    @foreach($colorOptions as $c)
                                        <option value="{{ $c }}" @selected(($stat['color'] ?? 'violet') === $c)>{{ ucfirst($c) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Mission / Vision / Values --}}
                <div class="{{ $sectionClass }}">
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-bullseye text-rose-500"></i> {{ __('app.admin_pages_form_about_cards_section') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($aboutData['cards'] as $i => $card)
                            <div class="bg-white border border-slate-200 rounded-xl p-3 space-y-2">
                                <div class="text-[11px] font-bold text-slate-400">{{ __('app.admin_pages_form_card_num') }} {{ $i + 1 }}</div>
                                <input type="text" data-about="cards.{{ $i }}.icon" value="{{ $card['icon'] }}" placeholder="fa-bullseye" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono">
                                <input type="text" data-about="cards.{{ $i }}.title" value="{{ $card['title'] }}" placeholder="العنوان" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                <textarea data-about="cards.{{ $i }}.desc" rows="3" placeholder="الوصف" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">{{ $card['desc'] }}</textarea>
                                <select data-about="cards.{{ $i }}.color" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                    @foreach($colorOptions as $c)
                                        <option value="{{ $c }}" @selected(($card['color'] ?? 'violet') === $c)>{{ ucfirst($c) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Team --}}
                <div class="{{ $sectionClass }}">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-users text-blue-500"></i> {{ __('app.admin_pages_form_about_team_section') }}
                        </h4>
                        <button type="button" id="about-team-add" class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg">
                            <i class="fa-solid fa-plus"></i> إضافة عضو
                        </button>
                    </div>
                    <div class="space-y-1.5">
                        <label class="{{ $labelClass }}">{{ __('app.admin_pages_form_team_title_label') }}</label>
                        <input type="text" data-about="team_title" value="{{ $aboutData['team_title'] }}" class="{{ $inputClass }}">
                    </div>
                    <div id="about-team-list" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($aboutData['team'] as $i => $m)
                            <div class="about-team-row bg-white border border-slate-200 rounded-xl p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-400">{{ __('app.admin_pages_form_member_num') }} {{ $i + 1 }}</span>
                                    <button type="button" class="about-team-remove text-rose-500 hover:text-rose-700 text-xs font-bold">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                                <input type="text" class="team-name w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs" placeholder="{{ __('app.admin_pages_form_placeholder_name') }}" value="{{ $m['name'] }}">
                                <input type="text" class="team-role w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs" placeholder="{{ __('app.admin_pages_form_placeholder_role') }}" value="{{ $m['role'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <textarea name="content" id="content-editor" class="hidden">{{ old('content', $page->content) }}</textarea>
            </div>
        @else
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_content_label') }}</label>
                <textarea id="content-editor" name="content" rows="20" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('content', $page->content) }}</textarea>
                <p class="text-[11px] text-slate-400">{{ __('app.admin_pages_form_content_hint') }}</p>
            </div>
        @endif


        <div class="border-t border-slate-100 pt-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="h-6 w-1 rounded-full bg-violet-500"></span>
                SEO
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_seo_title') }}</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $page->seo_title) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_seo_keywords') }}</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $page->seo_keywords) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_seo_description') }}</label>
                    <textarea name="seo_description" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('seo_description', $page->seo_description) }}</textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_og_title') }}</label>
                    <input type="text" name="og_title" value="{{ old('og_title', $page->og_title) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_og_image') }}</label>
                    <input type="file" name="og_image" accept="image/*" class="w-full text-sm">
                    @if($page->og_image_url)
                    <div class="flex items-center gap-4 mt-2">
                        <img src="{{ $page->og_image_url }}" alt="" class="h-16 w-auto object-cover rounded-lg">
                        <label class="flex items-center gap-2 text-sm text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_og_image" value="1">
                            {{ __('app.admin_pages_form_remove_image') }}
                        </label>
                    </div>
                    @endif
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_og_description') }}</label>
                    <textarea name="og_description" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">{{ old('og_description', $page->og_description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-slate-100 pt-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_canonical_url') }}</label>
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500">{{ __('app.admin_pages_form_sort_label') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
            </div>
            <div class="flex items-center gap-2 h-11 mt-6">
                <input type="checkbox" name="status" value="1" @checked(old('status', $page->status ?? true)) class="rounded">
                <label class="text-sm font-semibold text-slate-700">{{ __('app.admin_pages_form_active') }}</label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.pages.index') }}" class="h-11 px-6 bg-slate-100 text-slate-700 font-bold rounded-xl flex items-center">{{ __('app.admin_pages_form_cancel') }}</a>
            <button type="submit" class="h-11 px-8 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                {{ $page->exists ? __('app.admin_pages_form_update') : __('app.admin_pages_form_save') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    const IS_FAQS_PAGE  = @json($isFaqsPage ?? false);
    const IS_ABOUT_PAGE = @json($isAboutPage ?? false);

    if (!IS_FAQS_PAGE && !IS_ABOUT_PAGE) {
        tinymce.init({
            selector: '#content-editor',
            license_key: 'gpl',
            height: 600,
            directionality: 'ltr',
            language: 'en',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount emoticons codesample',
            toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table codesample | removeformat code fullscreen preview',
            toolbar_mode: 'wrap',
            menubar: 'edit view insert format tools table help',
            image_advtab: true,
            branding: false,
            promotion: false,
            content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 15px; line-height: 1.7; }',
        });
    }

    if (IS_ABOUT_PAGE) {
        const editor = document.getElementById('about-editor');
        const hidden = document.getElementById('content-editor');
        const form   = hidden.closest('form');
        const teamList = document.getElementById('about-team-list');

        const setDeep = (obj, path, value) => {
            const keys = path.split('.');
            let cur = obj;
            for (let i = 0; i < keys.length - 1; i++) {
                const k = keys[i];
                const nextIsIdx = /^\d+$/.test(keys[i + 1]);
                if (cur[k] == null) cur[k] = nextIsIdx ? [] : {};
                cur = cur[k];
            }
            cur[keys[keys.length - 1]] = value;
        };

        document.getElementById('about-team-add').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'about-team-row bg-white border border-slate-200 rounded-xl p-3 space-y-2';
            row.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400">عضو جديد</span>
                    <button type="button" class="about-team-remove text-rose-500 hover:text-rose-700 text-xs font-bold"><i class="fa-solid fa-trash"></i></button>
                </div>
                <input type="text" class="team-name w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs" placeholder="الاسم">
                <input type="text" class="team-role w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs" placeholder="المسمى الوظيفي">
            `;
            teamList.appendChild(row);
        });

        teamList.addEventListener('click', (e) => {
            const btn = e.target.closest('.about-team-remove');
            if (btn) btn.closest('.about-team-row').remove();
        });

        form.addEventListener('submit', () => {
            const data = {};
            editor.querySelectorAll('[data-about]').forEach(el => {
                setDeep(data, el.dataset.about, el.value);
            });
            data.team = Array.from(teamList.querySelectorAll('.about-team-row')).map(r => ({
                name: r.querySelector('.team-name').value.trim(),
                role: r.querySelector('.team-role').value.trim(),
            })).filter(m => m.name || m.role);
            hidden.value = JSON.stringify(data);
        });
    } else if (IS_FAQS_PAGE) {

        const list      = document.getElementById('faq-list');
        const hidden    = document.getElementById('content-editor');
        const form      = hidden.closest('form');
        const search    = document.getElementById('faq-search');
        const perPageEl = document.getElementById('faq-perpage');
        const pager     = document.getElementById('faq-pager');
        const prevBtn   = document.getElementById('faq-prev');
        const nextBtn   = document.getElementById('faq-next');
        const pageEl    = document.getElementById('faq-page');
        const pagesEl   = document.getElementById('faq-pages');
        const shownEl   = document.getElementById('faq-shown');
        const totalEl   = document.getElementById('faq-total');
        const emptyEl   = document.getElementById('faq-empty');

        let currentPage = 1;
        let perPage = parseInt(perPageEl.value, 10) || 20;

        const rows = () => Array.from(list.querySelectorAll('.faq-row'));

        const reindex = () => rows().forEach((row, i) => {
            row.querySelector('.faq-index').textContent = i + 1;
        });

        const matches = (row, term) => {
            if (!term) return true;
            const q = row.querySelector('.faq-q').value.toLowerCase();
            const a = row.querySelector('.faq-a').value.toLowerCase();
            const c = row.querySelector('.faq-cat').value.toLowerCase();
            return q.includes(term) || a.includes(term) || c.includes(term);
        };

        const render = () => {
            const term = (search.value || '').trim().toLowerCase();
            const all = rows();
            totalEl.textContent = all.length;

            const filtered = all.filter(r => matches(r, term));
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const visibleSet = new Set(filtered.slice(start, end));

            all.forEach(r => { r.style.display = visibleSet.has(r) ? '' : 'none'; });

            pageEl.textContent = currentPage;
            pagesEl.textContent = totalPages;
            shownEl.textContent = visibleSet.size;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages;
            emptyEl.classList.toggle('hidden', filtered.length !== 0);
            pager.classList.toggle('hidden', filtered.length === 0);
        };

        const catOptionsHTML = () => {
            const opts = new Set();
            rows().forEach(r => {
                const sel = r.querySelector('.faq-cat');
                if (sel) Array.from(sel.options).forEach(o => { if (o.value) opts.add(o.value); });
            });
            return '<option value="">— اختر تصنيف —</option>' +
                Array.from(opts).map(v => `<option value="${v}">${v}</option>`).join('');
        };

        const rowTemplate = () => {
            const wrap = document.createElement('div');
            wrap.className = 'faq-row bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3';
            wrap.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">سؤال <span class="faq-index"></span></span>
                    <button type="button" class="faq-remove text-rose-500 hover:text-rose-700 text-xs font-bold inline-flex items-center gap-1">
                        <i class="fa-solid fa-trash"></i> حذف
                    </button>
                </div>
                <input type="text" class="faq-q w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm" placeholder="السؤال">
                <textarea class="faq-a w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm" rows="3" placeholder="الإجابة"></textarea>
                <div class="flex items-center gap-2">
                    <select class="faq-cat flex-1 h-10 px-3 bg-white border border-slate-200 rounded-xl text-xs">${catOptionsHTML()}</select>
                    <button type="button" class="faq-cat-new inline-flex items-center gap-1 h-10 px-3 bg-violet-50 hover:bg-violet-100 text-violet-700 rounded-xl text-xs font-bold whitespace-nowrap">
                        <i class="fa-solid fa-plus"></i> تصنيف جديد
                    </button>
                </div>
            `;
            return wrap;
        };

        document.getElementById('faq-add').addEventListener('click', () => {
            const row = rowTemplate();
            list.insertBefore(row, list.firstChild);
            reindex();
            search.value = '';
            currentPage = 1;
            render();
            row.querySelector('.faq-q')?.focus();
            window.scrollTo({ top: list.offsetTop - 80, behavior: 'smooth' });
        });

        list.addEventListener('click', (e) => {
            const newBtn = e.target.closest('.faq-cat-new');
            if (newBtn) {
                const select = newBtn.parentElement.querySelector('.faq-cat');
                const val = (prompt('اسم التصنيف الجديد:', '') || '').trim();
                if (val) {
                    // Add to every select if not present, then select on current
                    rows().forEach(r => {
                        const s = r.querySelector('.faq-cat');
                        if (s && !Array.from(s.options).some(o => o.value.toLowerCase() === val.toLowerCase())) {
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = val;
                            s.appendChild(opt);
                        }
                    });
                    select.value = val;
                }
                return;
            }

            const btn = e.target.closest('.faq-remove');
            if (!btn) return;
            const all = rows();
            if (all.length <= 1) {
                const row = btn.closest('.faq-row');
                row.querySelectorAll('input, textarea').forEach(el => el.value = '');
                const s = row.querySelector('.faq-cat'); if (s) s.value = '';
                render();
                return;
            }
            btn.closest('.faq-row').remove();
            reindex();
            render();
        });


        let searchTimer;
        search.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { currentPage = 1; render(); }, 120);
        });

        perPageEl.addEventListener('change', () => {
            perPage = parseInt(perPageEl.value, 10) || 20;
            currentPage = 1;
            render();
        });

        prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; render(); window.scrollTo({ top: list.offsetTop - 80, behavior: 'smooth' }); } });
        nextBtn.addEventListener('click', () => { currentPage++; render(); window.scrollTo({ top: list.offsetTop - 80, behavior: 'smooth' }); });

        form.addEventListener('submit', () => {
            const items = [];
            rows().forEach(row => {
                const q = row.querySelector('.faq-q').value.trim();
                const a = row.querySelector('.faq-a').value.trim();
                const cat = row.querySelector('.faq-cat').value.trim();
                if (q && a) {
                    const item = { q, a };
                    if (cat) item.category = cat;
                    items.push(item);
                }
            });
            hidden.value = JSON.stringify(items);
        });

        render();
    }
</script>
@endpush
@endsection

