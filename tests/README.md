# Tests

Both suites run against a real WordPress site with the **Deskman Studio**
theme and the **Deskman Studio Manager** plugin active.

## PHP integration tests (77 checks)

These cover the registry, the page and routes, the theme link, field sanitisation and
colour helpers. They also cover the enquiry endpoint: honeypot, nonce, field errors, the
lead rules' email typo / throwaway inbox / placeholder phone checks, logo validation,
storage in Deskman → Forms, the notification email, spam quarantine and the shared rate
limit. Finally they cover the admin panel, permissions and logo clean-up. The suite never
sends email and deletes what it creates.

```bash
WP_PATH=/path/to/wordpress php tests/php/run.php
```

Without DNS (CI, sandboxes), turn off the plugin's `lead_check_mx` setting
(Deskman → Settings → Leads & spam filtering), or every email domain is reported as unreachable.

## End-to-end tests (Playwright + Node's built-in test runner, 9 tests)

These cover the gallery (cards, thumbnails, Templates highlighted in the nav), the
coming-soon behaviour, live updates (name, phone, hours, colours, logo, reset), logo
validation, and the four device widths, including the mobile menu inside the preview.
They check phone, tablet and laptop screens for horizontal overflow, and send a full
enquiry: client validation, then a real 201 submission.

```bash
npm install --no-save playwright   # or link an existing install into node_modules
BASE_URL=http://localhost:8080 node --test tests/e2e/templates.test.mjs
```

The enquiry test stores a real enquiry ("E2E Test Dental") and counts towards the
hourly per-IP rate limit (5 by default). Run it against a local or staging site.

## Throwaway local site

[SQLite Database Integration](https://github.com/WordPress/sqlite-database-integration)
lets you run everything without MySQL. Copy its `db.copy` to `wp-content/db.php`,
install WordPress, then symlink the theme and plugin from this repository. Activate both
and serve with `php -S localhost:8080 -t <site> router.php`, using a router that sends
unknown paths to `index.php`.
