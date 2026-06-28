# Multi-Language & Multi-Currency System

A large, multi-phase implementation. I'll break it into 5 phases so each turn produces a working, testable slice. Confirm the plan, then I'll execute Phase 1 immediately and continue phase by phase.

---

## Phase 1 — Database & Core Models

**Migrations**
- `languages` — `id, name, native_name, code (unique, idx), locale, direction (enum ltr/rtl), flag, is_default (bool), is_active (bool, idx), sort_order, timestamps`
- `currencies` — `id, name, code (unique, idx), symbol, symbol_position (enum before/after), decimals (u tinyint), decimal_separator, thousands_separator, exchange_rate (decimal 18,8), is_default, is_active (idx), sort_order, timestamps`
- `translations` — `id, locale (idx), group (idx), key, value (text), unique(locale, group, key)` — DB-backed translations (admin-editable)
- Seed default rows: en (LTR, default), ar (RTL); USD (default), EGP

**Models**: `Language`, `Currency`, `Translation` with proper casts, scopes (`active()`, `ordered()`), and `default()` accessor.

**Services** (`app/Services/`):
- `LanguageService` — cached list, default lookup, switch helper preserving current path
- `CurrencyService` — cached list, convert(amount, from?, to?), format(amount, currency?)
- Both use `Cache::rememberForever` with explicit invalidation on save/delete via model observers

---

## Phase 2 — Routing, Middleware & Helpers

**Routes** — wrap all public frontend routes in a localized group:
```php
Route::prefix('{locale}')->where(['locale' => '[a-z]{2}'])
    ->middleware(['set.locale'])->group(base_path('routes/frontend.php'));
Route::get('/', fn() => redirect('/' . app(LanguageService::class)->default()->code));
```
Admin routes stay unprefixed.

**Middleware**
- `SetLocale` — validates `{locale}` against active languages, sets `App::setLocale()`, shares to views, 404s on invalid code
- `SetCurrency` — reads cookie `currency` (fallback default), binds singleton

**URL generation** — `URL::defaults(['locale' => app()->getLocale()])` so `route()` auto-includes prefix; add `LocaleServiceProvider` to register.

**Helpers** (`app/helpers.php`, autoloaded via composer):
- `current_locale()`, `current_currency()`, `money($amount, $currency=null)`, `switch_locale_url($code)`

---

## Phase 3 — Translation Conversion (Frontend Only)

**Storage strategy**: Hybrid — file-based `resources/lang/{locale}/*.php` for developer keys + DB `translations` table merged via custom `Translator` loader (admin overrides files).

Files to create per locale: `common.php`, `nav.php`, `home.php`, `product.php`, `cart.php`, `checkout.php`, `account.php`, `blog.php`, `auth.php`, `footer.php`.

Convert all `resources/views/` frontend Blade files (excluding `admin/*`) — replace hardcoded Arabic/English strings with `{{ __('group.key') }}`. Done per-folder in sub-turns to keep diffs reviewable:
1. layout + partials + nav + footer
2. home + product listing + product show + reviews
3. cart + checkout + account + auth + blog

---

## Phase 4 — SEO

- Per-route `head()` equivalent: each Blade layout emits `<link rel="alternate" hreflang="{code}" href="{switch_locale_url(code)}">` for every active language + `x-default`
- `<link rel="canonical" href="{current localized url}">`
- Localized `sitemap.xml` controller — one `<url>` per (route × active language) with `<xhtml:link rel="alternate">`
- Localized meta title/description per page via translation keys

---

## Phase 5 — Admin Settings UI

Extend `/admin/settings` with two new tabs (matching existing tab styling):

**Languages tab**
- Table: flag, name, code, locale, direction, default radio, active toggle, sort, actions
- Modal form: create/edit; flag upload to `storage/languages/`
- Bulk reorder (sortable)

**Currencies tab**
- Table: name, code, symbol, rate, default, active, sort
- Modal form: full field set from spec
- "Update rates" button (stub for future API integration with documented hook)

**Translations tab** (bonus, same page)
- Filter by locale + group, inline-edit values, save → invalidates translation cache

Cache invalidation on every admin save via model observers.

---

## Technical Notes

- **Caching**: `languages:all`, `currencies:all`, `translations:{locale}:{group}` — flushed by observers
- **Performance**: middleware reads from cache only (zero queries per request after warm-up); price formatting is pure PHP
- **Backward compatibility**: existing `/products`, `/cart` URLs redirect to `/{default_locale}/...` via fallback route — no broken links
- **No breaking changes** to admin panel (already English from prior phases)
- **Cookie**: `currency` (1 year, SameSite=Lax); locale lives in URL, not cookie, for SEO

---

## Execution Order

I'll execute one phase per turn. Phase 1 (migrations + models + services + seeds) is the foundation — nothing else works without it, and it's safe to ship alone because routes/middleware aren't wired yet.

**Reply "ابدأ" / "go" to start Phase 1, or tell me which phase to prioritize / skip.**
