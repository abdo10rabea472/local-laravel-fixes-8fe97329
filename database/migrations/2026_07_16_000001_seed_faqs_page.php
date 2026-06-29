<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! Page::where('slug', 'faqs')->exists()) {
            Page::create([
                'slug'            => 'faqs',
                'title'           => 'Frequently Asked Questions',
                'content'         => '',
                'seo_title'       => 'FAQs | UNI-LAB MARKET',
                'seo_description' => 'Find quick answers about ordering lab equipment, shipping, returns, and more.',
                'status'          => true,
                'sort_order'      => 1,
            ]);
        }
    }

    public function down(): void
    {
        Page::where('slug', 'faqs')->delete();
    }
};
