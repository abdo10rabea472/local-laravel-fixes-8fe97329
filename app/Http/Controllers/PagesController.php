<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class PagesController extends Controller
{
    public function about()
    {
        $page = Page::bySlug('about')->active()->first();

        $stats = Cache::remember('pages.about.stats', 600, function () {
            return [
                'products'   => Product::where('status', true)->count(),
                'categories' => Category::count(),
                'years'      => max(1, now()->year - 2020),
                'customers'  => (int) (\App\Models\User::count() * 1.2 + 50),
            ];
        });

        $team = [
            ['name' => __('app.about_team_member1_name'), 'role' => __('app.about_team_member1_role')],
            ['name' => __('app.about_team_member2_name'), 'role' => __('app.about_team_member2_role')],
            ['name' => __('app.about_team_member3_name'), 'role' => __('app.about_team_member3_role')],
            ['name' => __('app.about_team_member4_name'), 'role' => __('app.about_team_member4_role')],
        ];

        return view('pages.about', compact('stats', 'team', 'page'));
    }
}
