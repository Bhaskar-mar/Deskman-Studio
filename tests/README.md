# Tests

Both suites run against a real WordPress site with the plugin active.

## PHP integration tests

Covers the registry, field sanitisation, colour helpers, the REST endpoint (bot
protection, validation, logo checks, storage, notifications, rate limiting),
capabilities and privacy export/erase. The suite creates and removes its own data and
never sends real email.

```bash
WP_PATH=/path/to/wordpress php tests/php/run.php
```

## End-to-end tests (Playwright + Node's built-in test runner)

Covers the Templates page, the coming-soon behaviour, the customiser's live updates
(name, phone, hours, colours, logo and reset), logo validation, the four device widths
(including the mobile menu inside the preview), phone/tablet/laptop screen sizes without
horizontal overflow, and a full enquiry: client validation followed by a real 201
submission.

```bash
npm install --no-save playwright   # or link an existing install into node_modules
BASE_URL=http://localhost:8080 node --test tests/e2e/templates.test.mjs
```

Note: the enquiry test creates a real enquiry ("E2E Test Dental") on the target site.
Run it against a local or staging site.

## Throwaway local site

One way to run everything without MySQL is
[SQLite Database Integration](https://github.com/WordPress/sqlite-database-integration):
copy its `db.copy` to `wp-content/db.php`, install WordPress, symlink this plugin into
`wp-content/plugins/`, activate it, then serve with `php -S localhost:8080` (with a
router that sends unknown paths to `index.php`).
