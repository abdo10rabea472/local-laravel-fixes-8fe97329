## Tasks

### 1. Link admin product-catalog settings with the public products page
- Re-enable translation-aware fallback in `resources/views/products/index.blade.php` so the title/subtitle come from `SiteSetting` (`catalog_page_title`, `catalog_page_subtitle`) and fall back to translation keys `app.products_page_title` / `app.products_page_subtitle` when empty.
- Apply SEO values from `$seo` (already passed by `ProductCatalogController`) to the page `<title>` / meta.
- Add a small note in admin edit view linking to the public page (`route('products.index')`).

### 2. Add full translation keys for public pages
Add `__('app.*')` keys and update views:
- `resources/views/pages/blog/index.blade.php` — finish any remaining hardcoded strings (sidebar, share buttons, dates).
- `resources/views/pages/track-order.blade.php` — every label, button, status, empty state.
- `resources/views/pages/contact.blade.php` — hero, form labels, info cards, success/error messages.
- `resources/views/pages/about.blade.php` — hero, sections, CTA (will be replaced by dynamic page content; see task 4).
- Append matching keys to both `resources/lang/en/app.php` and `resources/lang/ar/app.php` using prefixes `track_*`, `contact_*`, `about_*`, `blog_*`.

### 3. Wire contact page info (phone/email/address/hours) to admin settings
- Read existing keys from `admin/settings?tab=contact` (likely `contact_phone`, `contact_email`, `contact_address`, `contact_hours` or similar) — confirm by reading `SettingsController` and the contact tab blade.
- Replace hardcoded values in `resources/views/pages/contact.blade.php` with `site_setting('contact_phone')`, etc., falling back to translation strings.
- If any of these keys don't exist in the contact tab, add them to that tab so admins can edit them.

### 4. Manage About page (and others) from `admin/pages`
- The `Page` model + `admin/pages` CRUD already exists. Replace the static `pages/about.blade.php` route with a dynamic render: route `/about` resolves the `Page` with slug `about` and renders its content (title, body, SEO fields).
- Seed/create an `about` page row if missing so it appears in the admin list.
- Use the same dynamic render for any other slug already routed statically when a matching `Page` exists.

### 5. Admin Pages list UX improvements
In `resources/views/admin/pages/index.blade.php`:
- Convert all Arabic UI labels to English (table headers, buttons, empty state, status badges).
- In the Actions column, add a **View page** button (eye icon) linking to the public URL `url('/' . app()->getLocale() . '/' . $page->slug)` next to Edit/Delete.
- Polish the design: card-style table, consistent badge colors, hover states matching the rest of the admin theme, responsive on mobile.

## Technical Notes
- Reuse helpers `site_setting()` / `site_setting_url()` already present in `app/helpers.php`.
- Page lookup helper: `Page::where('slug', $slug)->where('is_published', true)->firstOrFail()`.
- Clear `opcache_reset()` on settings save (already done by SettingsController).
- No DB schema changes required; only a seeder/migration to insert the `about` row if it's absent.
