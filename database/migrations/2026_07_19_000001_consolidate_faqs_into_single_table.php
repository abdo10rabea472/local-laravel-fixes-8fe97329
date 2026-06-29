<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faqs') || ! Schema::hasTable('pages')) {
            return;
        }

        $page = DB::table('pages')->where('slug', 'faqs')->first();
        if (! $page) {
            return;
        }

        // Migrate any legacy JSON FAQs from pages.content into the faqs table.
        $decoded = json_decode((string) ($page->content ?? ''), true);
        if (is_array($decoded)) {
            $now = now();
            $maxSort = (int) (DB::table('faqs')->max('sort_order') ?? 0);
            $i = 0;
            foreach ($decoded as $row) {
                if (! is_array($row) || empty($row['q']) || empty($row['a'])) continue;
                $q = trim((string) $row['q']);
                $a = trim((string) $row['a']);
                $cat = trim((string) ($row['category'] ?? 'General')) ?: 'General';
                $exists = DB::table('faqs')->where('question', $q)->exists();
                if ($exists) continue;
                DB::table('faqs')->insert([
                    'category'   => $cat,
                    'question'   => $q,
                    'answer'     => $a,
                    'sort_order' => $maxSort + (++$i),
                    'active'     => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Drop the legacy 'faqs' page entirely; the /faqs route now reads from the faqs table.
        DB::table('pages')->where('slug', 'faqs')->delete();
    }

    public function down(): void
    {
        // Non-reversible data consolidation.
    }
};
