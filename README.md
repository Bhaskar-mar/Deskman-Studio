# Deskman Studio — website

This repository holds the deskmanstudio.com WordPress code:

| Package | Path | Version |
|---|---|---|
| **Deskman Studio** theme | `wp-content/themes/deskman-studio-theme` | 5.1.0 |
| **Deskman Studio Manager** plugin | `wp-content/plugins/deskman-studio-manager` | 7.1.0 |

The theme renders the site; the Manager plugin stores content, projects, leads,
area pages and the chatbot (see the plugin's `readme.txt`).

## 7.1 / 5.1: Website templates

- **`/templates/`**: a gallery of ten industry categories in the site's own design
  (dark hero, section heads, three-step strip, the shared "Start a project" CTA).
  **Dental Clinics** is live; the other nine are "Coming soon". Clicking one
  explains that its templates are being prepared, and its URL redirects back to
  the gallery. It never opens a missing template.
- **`/templates/dental-clinics/`**: the live customiser. It has a quick-start form,
  then a panel for business details, logo upload (PNG/JPEG/WebP, 2 MB, checked by
  type, signature and decoding), brand colours, the headline, introduction, services
  and team. Every change shows immediately in the preview. Reset restores the demo
  copy.
- **Device previews**: the template is a real responsive page in an iframe set to
  exactly 390 / 768 / 1366 / 1440 px, so its own breakpoints apply (mobile menu,
  stacked layout). The frame is then scaled to fit the screen. On phones, the
  customiser switches between Customise and Preview tabs.
- **Start My Project**: sends name, email, phone, business, the template, every
  personalised field and the logo to `POST /wp-json/deskman/v1/template-enquiry`.
  It is validated by the **existing lead rules** in `includes/leads.php`: email typo,
  throwaway-inbox and domain checks, the phone check, spam scoring with quarantine,
  and the shared rate limit. It is stored in **Deskman → Forms** with a "Website
  template" panel, and the studio is emailed. The visitor sees a reference number
  only after the enquiry is actually saved. Nothing is published.
- **Theme**: "Templates" in the header, mobile menu and footer, plus
  `dm_link( 'templates' )`.

### Where things live (plugin)

```
includes/site-templates.php        routes, page, views, REST enquiry, logo storage, admin panel
site-templates/industries.php      ← the industry cards (add industries here)
site-templates/templates.php       ← ready templates
site-templates/dental/fields.php   ← editable fields + demo copy (drives form, preview, validation)
site-templates/dental/preview.php  the dental website
site-templates/views/              gallery + customiser (overridable from {theme}/site-templates/views/)
assets/site-templates/             CSS, JS, self-hosted template font, thumbnails
```

**Adding a template:** copy `site-templates/dental/` to a new folder and adapt
`fields.php` and `preview.php`. Elements bind with `data-bind`, `data-bind-tel`,
`data-bind-mailto`, `data-bind-ml` and `data-bind-hours`. Register the template in
`templates.php`, then point the industry at it in `industries.php`. Thumbnails:
`node tools/screenshot-thumbnail.mjs <preview-url> <out.webp>` for live templates, and
`node tools/generate-thumbnails.mjs` for the "coming soon" cards.

### Installing the update

Upload both zips (Appearance → Themes → Add New → Upload for the theme, then
Plugins → Add New → Upload Plugin for the plugin), choosing **Replace current
with uploaded**. The Templates page is created on the next admin page load.

**Nginx only:** add
`location ^~ /wp-content/uploads/deskman-private/ { deny all; }`.
Uploaded logos have random 32-character names, and Apache is already blocked by
`.htaccess`.

### Content notes

The dental imagery is SVG illustrations that recolour with the customer's brand
(no photo library was available when it was built). Swap in licensed clinic photos
for production sites. Team profiles and testimonials are clearly labelled as example
content.

## Tests

See [`tests/README.md`](tests/README.md).
