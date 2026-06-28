<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $usedSlugs = [];
        $updates = [];

        DB::table('blog_posts')
            ->orderBy('id')
            ->select(['id', 'title', 'slug', 'created_at', 'published_at'])
            ->get()
            ->each(function ($post) use (&$usedSlugs, &$updates) {
                $base = $this->normalizeSlug($post->slug ?: $post->title);
                $slug = $base;
                $counter = 2;

                while (isset($usedSlugs[$slug])) {
                    $slug = $base.'-'.$counter++;
                }

                $usedSlugs[$slug] = true;
                $updates[] = [
                    'id' => $post->id,
                    'slug' => $slug,
                    'published_at' => $post->published_at ?: ($post->created_at ?: now()),
                ];
            });

        foreach ($updates as $post) {
            DB::table('blog_posts')
                ->where('id', $post['id'])
                ->update(['slug' => 'repairing-post-'.$post['id']]);
        }

        foreach ($updates as $post) {
            DB::table('blog_posts')
                ->where('id', $post['id'])
                ->update([
                    'slug' => $post['slug'],
                    'published_at' => $post['published_at'],
                ]);
        }
    }

    public function down(): void
    {
        // Data repair migration; intentionally not destructive.
    }

    private function normalizeSlug(?string $value): string
    {
        $value = trim((string) $value);
        $slug = Str::slug($value);

        if ($slug === '') {
            $slug = preg_replace('/[^\pL\pN]+/u', '-', $value) ?: '';
            $slug = trim($slug, '-');
            $slug = preg_replace('/-+/u', '-', $slug) ?: '';
        }

        return $slug !== '' ? mb_strtolower($slug, 'UTF-8') : 'post-'.Str::lower(Str::random(8));
    }
};