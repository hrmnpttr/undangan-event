# Undangan — Event Invitation & Guest Management System

> 🇮🇩 **Baca dalam Bahasa Indonesia: [README.id.md](README.id.md)**

A Laravel + Filament application for managing event invitations end-to-end:
digital invitations with RSVP, WhatsApp distribution, QR-code check-in, and
coordination of guest transport and lodging. Originally built for a large
anniversary celebration, it is fully configurable for any event.

- **Supported by [PT SRIWIJAYA UTAMA KARYA](https://sriwijayahost.id)**
- **License:** free for local / non-commercial use, and free for a **single
  public event** as long as the "Supported by PT SRIWIJAYA UTAMA KARYA"
  attribution stays visible. Multi-event or commercial use requires permission —
  see [LICENSE](LICENSE). Contact: [github.com/hrmnpttr](https://github.com/hrmnpttr).

---

## Features

- **Invitation designer & templates (no code):** pick a ready-made template —
  weddings (Islamic / Christian / Catholic / Buddhist), corporate events, or a
  tech meetup — or go fully **Custom** with your own image/HTML. Upload your own
  **background music (mp3 only)**, cover image, main photo, and gallery, and fill
  in event content (schedule/agenda, quote, venue) from **Desain Undangan** in
  the admin panel. See the [usage guide](docs/INVITATION-GUIDE.md).
- **Digital invitation per guest** at `/i/{code}` with a unique code, bilingual
  (English / Indonesian), animated entry cover, and background music.
- **RSVP / confirmation:** number of attendees, "cannot attend" option, and a
  configurable confirmation deadline.
- **Transport (pick-up & drop-off):** guests request pickup with arrival and
  departure dates; committee assigns drivers and pickup schedules.
- **Lodging:** guests indicate accommodation needs and overnight participant
  details (name, gender, spouse) for automatic room assignment.
- **WhatsApp distribution:** 25 rotating message templates, a share modal, and
  per-guest "sent" tracking.
- **QR codes:** generate single or bulk QR PDFs, and check guests in by scanning
  QR at the venue (attendance log).
- **Confirmation card PDF** generated per guest (DomPDF).
- **Excel import/export:** import guest lists; export lodging and pickup lists.
- **Two admin panels (Filament):** a committee panel (`/panitia`) and a driver
  panel (`/transport`), with role-based access.
- **Runtime settings:** event name, tagline, event date, confirmation deadline,
  and lodging-location publish toggle — editable from the admin panel.
- **Security:** two-factor authentication and profile management (Laravel
  Fortify).

## Tech Stack

- PHP 8.2+ · Laravel 12
- Filament 5 (admin panels) · Livewire 4 · Flux UI
- Tailwind CSS 4 · Vite 7
- MySQL (or SQLite for local testing)
- `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (Excel), `html5-qrcode`
  (scanning)

> **Note on Flux UI:** this project uses `livewire/flux`. Some Flux components
> require a license. If `composer install` prompts for credentials for
> `composer.fluxui.dev`, provide your Flux account details or adjust the
> dependency.

## Requirements

- PHP 8.2 or higher with the usual Laravel extensions
- Composer 2
- Node.js 20+ and npm
- A database (MySQL/MariaDB recommended; SQLite works for local use)

## Installation (Local)

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure .env — set your database and event settings (see below)

# 4. Database schema + starter data
php artisan migrate
php artisan db:seed          # optional: seeds default settings + a test user

# 5. Storage symlink (for invitation images / assets)
php artisan storage:link

# 6. Build front-end assets
npm run build                # or: npm run dev  (for live development)

# 7. Serve
php artisan serve
```

Then open `http://localhost:8000/panitia` for the admin panel.

> ⚠️ The commands above (`migrate`, `db:seed`, `storage:link`) modify your
> database and filesystem. Run them yourself on a database you control — do not
> point them at production data.

## Configuration

All event-specific content lives in **`config/undangan.php`** and is driven by
`.env`. Nothing about a specific event is hardcoded in the application logic.

| `.env` key | Purpose |
|---|---|
| `APP_NAME` | Application / organizer name (used in meta tags) |
| `EVENT_NAME` / `EVENT_FULL_NAME` | Short and full event name (invitation text, OG description) |
| `EVENT_TAGLINE` | Event tagline |
| `EVENT_DATE` | Event date (`Y-m-d`) |
| `EVENT_CONFIRMATION_UNTIL` | RSVP deadline (`Y-m-d`) |
| `EVENT_VENUE_NAME` / `EVENT_VENUE_MAPS` | Venue label and a Google Maps URL or address for the "Navigate to Venue" button |
| `EVENT_INVITE_BASE_URL` | Public base URL for guest links (falls back to `APP_URL`) |
| `EVENT_DRIVER_EMAIL_DOMAIN` | Email domain for auto-created driver accounts |
| `EVENT_IMAGE_PREFIX`, `EVENT_IMAGE_LANG_EN/ID` | Invitation hero image filename scheme in `storage/app/public` |
| `EVENT_OG_IMAGE_EN/ID`, `EVENT_PDF_IMAGE_EN/ID` | Social-share and PDF images |

The live values shown to guests (event name, date, deadline, publish toggle) can
also be edited at runtime from **Settings** in the admin panel; those override
the defaults above.

### Invitation images

Hero, social-share, and PDF images are **not** included in this repository — add
your own to `storage/app/public` and point the `EVENT_*_IMAGE*` variables at
them. Hero images follow the pattern `{PREFIX}{LANG}{1|2}{"" | "big"}.png`
(e.g. `MyEventEn1.png`, `MyEventEn1big.png`). Leave `EVENT_IMAGE_PREFIX` empty
to disable the image overlay.

## Roles & Panels

Access is controlled by a `role` on each user:

| Role | Access |
|---|---|
| `panitia` | Full committee/admin access |
| `panitia_penginapan` | Lodging committee |
| `penginapan` | Per-hotel lodging staff |
| `scan` | QR attendance scanning |
| `transport` | Driver panel (`/transport`) |

- **Committee panel:** `/panitia`
- **Driver panel:** `/transport`
- **Public invitation:** `/i/{code}` · **PDF card:** `/i/{code}/pdf`

## Public Routes

| Route | Description |
|---|---|
| `GET /i/{code}` | Guest invitation + RSVP page |
| `GET /i/{code}/pdf` | Download the confirmation card PDF |

## Security Notes

- Keep secrets in `.env` only; it is git-ignored and must never be committed.
- Driver login accounts are created with a securely generated random password
  (readable alphanumeric), shown once to the admin at creation time.
- This project connects to real databases; run migrations, seeders, and tests
  only against databases you own.

## License

Supported by **[PT SRIWIJAYA UTAMA KARYA](https://sriwijayahost.id)**.

- **Free** for local / non-commercial use.
- **Free for one (1) public event**, provided the visible attribution
  *"Supported by PT SRIWIJAYA UTAMA KARYA"* (linked to https://sriwijayahost.id)
  stays on the deployed site — it is already included in the invitation footer.
- **Multi-event, commercial, or hosted/business use** requires permission.

See [LICENSE](LICENSE) for full terms and contact
**[@hrmnpttr](https://github.com/hrmnpttr)** to arrange a commercial license.
