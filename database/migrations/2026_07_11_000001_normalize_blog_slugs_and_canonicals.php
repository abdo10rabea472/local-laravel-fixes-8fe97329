<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $usedSlugs = [];

        DB::table('blog_posts')
            ->select(['id', 'title', 'slug'])
            ->orderBy('id')
            ->chunkById(100, function ($posts) use (&$usedSlugs) {
                foreach ($posts as $post) {
                    $base = $this->normalizeSlug($post->slug ?: $post->title);
                    $slug = $base;
                    $counter = 2;

                    while (isset($usedSlugs[$slug])) {
                        $slug = $base.'-'.$counter++;
                    }

                    $usedSlugs[$slug] = true;

                    DB::table('blog_posts')
                        ->where('id', $post->id)
                        ->update([
                            'slug' => $slug,
                            'canonical_url' => 'blog/'.$slug,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Data normalization cannot be safely reversed.
    }

    private function normalizeSlug(?string $value): string
    {
        $value = trim((string) $value);

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = parse_url($value, PHP_URL_PATH) ?: $value;
        }

        $value = urldecode(preg_replace('/[?#].*$/', '', $value) ?? $value);
        $value = trim($value, "/ \t\n\r\0\x0B");
        $value = preg_replace('#^(?:index\.php/)?blog/#iu', '', $value) ?? $value;

        if (str_contains($value, '/')) {
            $parts = preg_split('#/+#', $value, -1, PREG_SPLIT_NO_EMPTY);
            $value = $parts ? end($parts) : $value;
        }

        $slug = Str::slug($value);

        if ($slug === '') {
            $slug = preg_replace('/[^\pL\pN]+/u', '-', $value) ?: '';
            $slug = trim($slug, '-');
            $slug = preg_replace('/-+/u', '-', $slug) ?: '';
        }

        return $slug !== '' ? mb_strtolower($slug, 'UTF-8') : 'post-'.Str::lower(Str::random(8));
    }
};