# Deskman Studio — Templates feature

This repository contains the **Deskman Studio Templates** WordPress plugin
(`wp-content/plugins/deskman-templates`). It adds:

- a **Templates** page at `/templates/` with ten industry categories;
- a complete, responsive **Dental Clinics** website template;
- a **live customiser** (business details, logo upload, brand colours, content) with
  **mobile / tablet / laptop / desktop** previews of the real responsive page;
- a **Start My Project** enquiry flow that validates, stores and notifies, entirely inside WordPress.

The plugin is theme-agnostic. It renders inside the active theme's header, footer and
navigation (classic themes via `get_header()`/`get_footer()`, block themes via
their `header`/`footer` template parts), so it works with the existing Deskman
Studio theme without modifying it.

## Install

1. Copy `wp-content/plugins/deskman-templates` to the site's `wp-content/plugins/`.
2. Activate **Deskman Studio Templates** in *Plugins*.
   - The **Templates** page is created (or an existing page with the slug `templates` is reused).
   - On classic themes, a *Templates* link is added once to the menu in the
     `primary`/`main`/`menu-1`/`header` location. Block themes that list pages show it
     automatically; otherwise add it in *Appearance → Editor → Navigation*.
3. *Settings → Permalinks*: any "pretty" structure is recommended. Plain permalinks also work, using query strings.

### Matching the Deskman Studio brand

All colours, radii and fonts for the Templates page and customiser live in
`assets/css/tokens.css`. They inherit the theme's fonts and, on block themes, its
`primary`/`secondary` presets. To set exact brand colours, add CSS to the theme
or to *Customizer → Additional CSS*:

```css
:root {
  --dmt-brand: #3355ff;    /* main brand colour (buttons, links) */
  --dmt-brand-2: #7c5cff;  /* gradient partner */
  --dmt-font-display: inherit;
}
```

### Server configuration (Nginx)

Uploaded logos are stored outside the media library in `wp-content/uploads/dmt-private/`.
The files get random 32-character names, and Apache is blocked by the bundled `.htaccess`.
**Nginx ignores `.htaccess`**, so add:

```nginx
location ^~ /wp-content/uploads/dmt-private/ { deny all; }
```

Admins view logos through an authenticated, nonce-protected admin endpoint.

## How it works

```
deskman-templates/
├── deskman-templates.php          bootstrap
├── config/
│   ├── industries.php             ← industry cards (add new industries here)
│   └── templates.php              ← registered website templates
├── includes/
│   ├── class-dmt-registry.php     reads config (filters: dmt_industries, dmt_templates)
│   ├── class-dmt-fields.php       field schema, defaults, sanitisation
│   ├── class-dmt-routes.php       /templates/<industry>/ and /preview/ rewrite rules
│   ├── class-dmt-frontend.php     page shell, assets, preview document
│   ├── class-dmt-enquiries.php    private CPT, REST endpoint, logo handling, email
│   ├── class-dmt-admin.php        enquiry list/detail screens, status, logo viewer
│   ├── class-dmt-privacy.php      personal-data export/erase, privacy policy text
│   └── class-dmt-color.php        contrast-safe brand colour helpers
├── views/                         gallery, customiser, reusable card + field partials
├── templates/dental/
│   ├── fields.php                 ← editable fields + demo defaults (drives the form)
│   ├── preview.php                the dental website markup
│   └── svg/                       brand-aware illustrations and icons
└── assets/                        css, js, fonts (self-hosted), thumbnails
```

- **Template vs. customer data.** A template is code: `preview.php`, CSS and JS, with demo
  defaults in `fields.php`. A visitor's details live only in their browser
  (`sessionStorage`, in that tab) and reach the preview iframe through same-origin
  `postMessage`. The server sees customer data only when they submit **Start My
  Project**, and then stores it as a private enquiry. Nothing is ever published.
- **Device previews.** The iframe is set to exactly 390/768/1366/1440 px wide, so the
  template's own media queries respond as on a real device (mobile navigation, stacked
  layout and so on). The device frame is then scaled to fit the screen.
- **Enquiries.** `POST /wp-json/deskman-templates/v1/enquiries` validates every field
  server-side. It checks the logo by extension, MIME type and real image data, then
  re-encodes it. It applies a honeypot, a signed minimum-fill-time token and a per-IP rate
  limit. Valid enquiries are stored as private `dmt_enquiry` posts (administrators only,
  not exposed in the REST API) and emailed to the site admin email
  (filter: `dmt_enquiry_recipient`). To connect a CRM or another workflow, hook
  `dmt_enquiry_submitted` ( `$post_id, $data` ).

### Adding a new industry template

1. Copy `templates/dental/` to `templates/<id>/` and adapt `fields.php` and `preview.php`.
   Elements bind to fields with `data-bind="<field>"`, `data-bind-tel`, `data-bind-mailto`,
   `data-bind-ml` (multi-line) and `data-bind-hours`.
2. Register it in `config/templates.php`.
3. In `config/industries.php`, set the industry's `'template' => '<id>'`.
4. Create its thumbnail with `node tools/screenshot-thumbnail.mjs <preview-url> <out.webp>`.

Industries whose template is missing stay "Coming soon" automatically. Their URLs
redirect to the gallery with a short explanation and never open a template.

## Content notes

- The dental imagery is drawn as SVG illustrations that recolour with the customer's
  brand. No stock photography was available in the build environment. Swap in licensed
  clinic photos when producing a real site.
- Team profiles and testimonials are clearly labelled as example/demonstration content.
  The template makes no claims about awards, ratings or patient numbers.

## Tests

See [`tests/README.md`](tests/README.md).
