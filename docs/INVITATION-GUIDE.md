# Usage Guide — General-Purpose Invitation System

> 🇮🇩 Versi Bahasa Indonesia: [PANDUAN-UNDANGAN.md](PANDUAN-UNDANGAN.md)

This system works for **any kind of event** and **for many people**: weddings
(Islamic, Christian, Catholic, Buddhist), corporate functions, meetings, and
tech meetups. The entire look of an invitation is configured from the admin
panel — no code changes required.

---

## 1. One-time setup (server admin)

Run once when first setting up the app. **Database commands are run by the
admin**, never automatically by the application.

```bash
cp .env.example .env
php artisan key:generate

# Configure the DB connection in .env, then:
php artisan migrate

# Make uploaded music/images publicly reachable:
php artisan storage:link

# Build front-end assets:
npm install && npm run build
```

Log into the committee panel at `/panitia`.

---

## 2. Pick & design a template

1. Open **Desain Undangan** (paint-brush icon).
2. Choose a template — the content fields below adapt to the event type.
3. Toggle **the cover ("envelope") screen** on/off.
4. Fill in **Content**:
   - Title, subtitle, date, time, venue, Google Maps — used by every template.
   - Opening quote / verse (e.g. QS. Ar-Rum: 21 or Genesis 2:24).
   - **Weddings**: couple names & parents, ceremony & reception schedule.
   - **Corporate / Tech**: host, speakers, dress code, and an **agenda/rundown**.
5. Click **Simpan Desain** (Save). Changes apply instantly.

Empty fields are hidden automatically — only fill what you need.

---

## 3. Upload your own invitation & music

Under **Music & Images**:

- **Background music** — **`.mp3` only** (max 12 MB), validated on upload.
  Autoplay can be disabled. Falls back to
  `storage/app/public/audio/background.mp3` if nothing is uploaded.
- **Cover image**, **detail image**, **main photo**, and a **photo gallery**.

Want full control? Pick the **Custom** template and either upload your own
invitation image or paste **HTML** into the "Custom HTML" box. The RSVP form is
still appended below it automatically.

### Recommended image sizes & orientation

A single image is used for **both mobile and desktop** — it is cropped
automatically (`object-cover`) to fit the screen, so **there is no separate
mobile-image upload**. Since invitations are *mobile-first*, prefer **portrait**
images. Max file size **8 MB**; aim for < 1 MB for fast loading.

| Field | Recommended size | Orientation | Ratio | Notes |
|-------|------------------|-------------|-------|-------|
| **Cover image (envelope)** | 1080 × 1440 px | Portrait | 3:4 | Full-screen cover. Mobile crops to 3:4; desktop widens (~16:10) |
| **Detail / content image** | 1080 × 1350 px | Portrait | 4:5 | Extra image (classic template) |
| **Main photo** | 800 × 800 px | Square | 1:1 | Shown as a circle in the header — center the subject |
| **Gallery** | 1000 × 1000 px | Square | 1:1 | Grid; every item is cropped square |
| **Share/OG image** *(optional, via `.env`)* | 1200 × 630 px | Landscape | 1.91:1 | Link preview (WhatsApp/social) |

> **Auto-crop tip:** keep the important part of the image **centered** — edges
> may be trimmed to fit the screen. To avoid clipped text, type it into the
> content fields (Title, Date, …) rather than baking it into the image.

> **Need different desktop (landscape) and mobile (portrait) images?** A
> two-variant upload isn't available in the designer yet — ask the maintainer if
> you need it.

---

## 4. Available templates

| Template | Best for | Signature |
|----------|----------|-----------|
| **Classic (Gold)** | General / default | Gold theme, two cover images (backward-compatible) |
| **Wedding — Islamic** | Akad & reception | Bismillah, "Walimatul 'Urs", green palette |
| **Wedding — Christian** | Ceremony | Cross ✝, blue palette |
| **Wedding — Catholic** | Ceremony | Cross ✝, maroon palette |
| **Wedding — Buddhist** | Ceremony | Dharma wheel ☸, warm gold |
| **Corporate** | Galas, formal events | Clean, host org & rundown |
| **Tech Meetup** | Conferences/meetups | Violet accent, monospace flavor, agenda |
| **Custom** | Anything | Your own image / HTML |

Each template applies its own colour palette across buttons, cards, and accents.

---

## 5. Guests & sharing

1. Open **Tamu** (Guests), add guests or **import from Excel**.
2. Each guest gets a **unique link** `/i/<code>` and a **QR code**.
3. Share via the **WhatsApp** button (25 rotating message templates).
4. Guests open the link, view the themed invitation, and **RSVP**.

---

## 6. Event day: QR check-in

Use **Scan Kehadiran** (Scan Attendance) to check guests in by camera or a
hardware scanner. Attendance is logged automatically.

---

## FAQ

- **Music won't autoplay?** Browsers block autoplay until interaction; it starts
  on first tap/click. Make sure the `.mp3` is valid.
- **Uploads not showing?** Ensure `php artisan storage:link` has been run.
- **Reuse for a new event?** Just change the template/content in **Desain
  Undangan** and refresh the **Guest** list — no code changes.

---

_Supported by [PT SRIWIJAYA UTAMA KARYA](https://sriwijayahost.id). See
[LICENSE](../LICENSE) for terms._
