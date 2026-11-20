# Plaudit

**Collect testimonials, approve them, and show them on any website.** Self-hosted, free, and it runs on cheap PHP hosting, with no Docker or terminal required.

A free, open-source alternative to paid testimonial tools that charge $25–60 a month to show a few quotes on your site.

<!-- Screenshots: wall of love, the public form on a phone, and the widget on a site. -->

## Features

- **A shareable form.** Send customers a link like `/t/your-product`. They leave a star rating, their words, their name and job title, and an optional photo. No account needed.
- **An inbox to review them.** Approve, hide, or feature testimonials. Customers' words can't be edited.
- **A wall of love.** A public page at `/w/your-product` that shows every approved testimonial.
- **An embed widget.** Two lines of HTML show testimonials on any website, as a grid or a carousel, in light or dark, in your brand color.
- **Import and export.** Bring in testimonials you already have from a CSV file, and export everything as CSV or JSON whenever you like.
- **Privacy and safety built in.** Email addresses are never shown publicly, photo metadata (such as GPS location) is stripped, there's spam protection, and nothing a stranger types can run as code on your site.

## Install on shared hosting (no terminal)

Requirements: PHP 8.3 or newer with the `gd`, `pdo_sqlite`, `mbstring` and `fileinfo` extensions. Most hosts have these.

1. Download `plaudit-<version>.zip` from the [Releases](../../releases) page.
2. Unzip it and upload the `plaudit` folder to your hosting, for example to `public_html/testimonials`.
3. Open that address in your browser, for example `https://yoursite.com/testimonials`.
4. Create your owner account. That's it.

On its first visit Plaudit creates its own settings file (with a unique secret key) and its database. If your host can point a domain or subdomain at a folder, point it at `plaudit/public`. Otherwise the included `.htaccess` file sends all traffic there and keeps your settings and database private.

**Upgrading:** upload the new version's files over the old ones, keeping your `.env` file, `database/database.sqlite` and `public/uploads`. Any database changes are applied automatically on the next visit.

## Install with Docker

```bash
git clone https://github.com/NathanaelDousa/plaudit.git && cd plaudit
docker compose up -d --build
```

Then open http://localhost:8080. Your database, photos and settings live in the `plaudit-data` volume. Put it behind an HTTPS reverse proxy (Caddy, Nginx, Traefik) before you share the form link.

## Embed testimonials on your site

Copy the code from your project page. It looks like this:

```html
<div data-plaudit="your-product" data-layout="grid"></div>
<script src="https://yoursite.com/testimonials/widget.js" async></script>
```

| Attribute | Values | Default |
|---|---|---|
| `data-layout` | `grid`, `carousel` | `grid` |
| `data-theme` | `auto`, `light`, `dark` | `auto` |
| `data-limit` | any number | `9` |

The widget renders inside a Shadow DOM, so your site's CSS won't break it and its CSS won't leak into your site. Changes can take up to a minute to appear because of caching.

## Importing from a CSV

The first row must name the columns. `name` and `body` are required, and `title`, `email` and `rating` (1–5) are optional. Imported testimonials land in **Pending** so you can review them first. Rows with missing or invalid values are skipped and counted.

## Development

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan serve
```

Run the tests with `php artisan test`. Build a shared-hosting zip with `scripts/build-release.sh 1.0.0`.

## License

MIT
