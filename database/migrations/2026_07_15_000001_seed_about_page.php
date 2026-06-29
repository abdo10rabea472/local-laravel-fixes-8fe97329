<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! Page::where('slug', 'about')->exists()) {
            Page::create([
                'slug'            => 'about',
                'title'           => 'About Us',
                'content'         => '',
                'seo_title'       => 'About Us | UNI-LAB MARKET',
                'seo_description' => 'Learn more about UNI-LAB MARKET — your trusted partner for laboratory and educational tools.',
                'status'          => true,
                'sort_order'      => 0,
            ]);
        }
    }

    public function down(): void
    {
        Page::where('slug', 'about')->delete();
    }
};
