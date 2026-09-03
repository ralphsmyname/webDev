# Hinlo Airsoft Zone — Database & CRUD Setup

This adds a real MySQL backend to the three forms on the site (Join,
Contacts, and the home page review form), plus a small admin panel at
`/admin` for full CRUD (Create, Read, Update, Delete) on everything
those forms collect.

## 1. What was added

```
config/database.php        PDO connection (reads env vars, safe defaults)
config/.htaccess            blocks direct web access to this folder
includes/csrf.php           session bootstrap + CSRF token helpers
includes/functions.php      sanitize/validate helpers, flash messages, json_response()
includes/admin_auth.php     login guard for /admin pages
includes/.htaccess          blocks direct web access to this folder
actions/register.php        handles the Join Us form (Create)
actions/contact.php         handles the Contacts form (Create)
actions/review.php          handles the home page review form (Create, unpublished by default)
admin/setup.php             one-time "create the first admin account" screen
admin/login.php              /admin/logout.php     session-based auth
admin/index.php             dashboard with counts
admin/registrations.php     list / delete / confirm registrations   (R, U, D)
admin/registration_form.php add / edit a registration                (C, U)
admin/messages.php          list / mark read / delete contact messages
admin/reviews.php           list / publish / delete reviews
admin/review_form.php       add / edit a review
sql/schema.sql              full database schema
sql/.htaccess               blocks direct web access to this folder
.env.example                environment variable template
```

`join.php`, `contacts.php`, and `index.php` were updated so their
forms actually submit (with a CSRF token and `name` attributes) to
the new `actions/*.php` endpoints. `js/main.js` was rewritten to
submit every `[data-demo-form]` via `fetch()`, show field-level
errors returned by the server, and show a success/error message —
no page reload.

## 2. Database setup

### Quick start with XAMPP

1. **Copy the site into `htdocs`.** Move the whole `site` folder (or
   its contents) into your XAMPP `htdocs` directory, e.g.
   `C:\xampp\htdocs\hinlo\` on Windows or
   `/Applications/XAMPP/xamppfiles/htdocs/hinlo/` on macOS.
2. **Start Apache and MySQL** from the XAMPP Control Panel.
3. **Import the schema.** Open `http://localhost/phpmyadmin`, click
   **Import**, choose `sql/schema.sql`, click **Go**. This creates
   the `hinlo_airsoft` database and all four tables.
4. **Leave `config/database.php` as-is** — its defaults
   (`localhost`, database `hinlo_airsoft`, user `root`, empty
   password, port `3306`) already match a stock XAMPP install. Only
   edit it if you changed your MySQL root password or port.
5. **Visit the site**: `http://localhost/hinlo/index.php` (adjust
   the folder name to whatever you used in step 1).
6. **Create your admin account**: go to
   `http://localhost/hinlo/admin/setup.php` once, fill in a
   username/password. That page refuses to run again once an admin
   exists.
7. **Log in** at `http://localhost/site/admin/login.php`.

That's it — submit the Join Us, Contacts, or review form on the
live site and the rows will show up in phpMyAdmin (table
`registrations`, `contact_messages`, or `reviews`) and in the
`/admin` panel.

A couple of XAMPP-specific notes:
- XAMPP's Apache doesn't read `.htaccess` `Require all denied`
  rules unless `AllowOverride All` is set for `htdocs` in
  `httpd.conf` (it's off by default in some XAMPP installs). It's
  not a functional problem — those folders (`config/`, `includes/`,
  `sql/`) contain no page output either way — but if you want the
  extra layer of protection to actually take effect, open
  `xampp/apache/conf/httpd.conf`, find the `<Directory "C:/xampp/htdocs">`
  block, and set `AllowOverride All`.
- If port 3306 is already taken by another MySQL install on your
  machine, change it in XAMPP's `my.ini` and update `DB_PORT` (or
  the fallback in `config/database.php`) to match.

### General (non-XAMPP) setup

### General (non-XAMPP) setup

1. Create the database and tables:
   ```
   mysql -u root -p < sql/schema.sql
   ```
2. Set your real credentials as environment variables (see
   `.env.example`), or just edit the fallback defaults in
   `config/database.php` for local development.
3. Visit `/admin/setup.php` once in your browser to create your
   first admin login. That page locks itself automatically as soon
   as one admin account exists.
4. Log in at `/admin/login.php`.

## 3. How data flows

**Public forms → Create.** Each form POSTs to its matching
`actions/*.php` file, which:
1. Confirms the request is POST and the CSRF token matches.
2. **Sanitizes** input — trims whitespace, strips tags
   (`sanitize_string()`), validates and normalizes email addresses
   (`sanitize_email()` via `FILTER_VALIDATE_EMAIL`), and checks phone
   format with a regex.
3. **Validates** — required fields, length limits, format checks —
   collecting per-field error messages.
4. On success, inserts the row with a **parameterized/prepared
   statement** (never string-concatenated SQL, so this is not
   vulnerable to SQL injection) and returns JSON.
5. `main.js` reads that JSON and shows either a success message or
   the specific field errors, without a page reload.

**Admin panel → Read / Update / Delete (and Create where useful).**
Everything under `/admin` requires a logged-in session
(`includes/admin_auth.php`). Every state-changing action (delete,
status change, save) is a POST request checked against the same CSRF
token pattern, and every SQL statement in `/admin` uses prepared
statements as well.

## 4. Security notes

- **Prepared statements everywhere** — no raw SQL string building
  from request data, anywhere in the codebase.
- **CSRF tokens** on every form (public and admin), verified with
  `hash_equals()`.
- **Passwords hashed with `password_hash()` / `PASSWORD_DEFAULT`**
  (bcrypt), verified with `password_verify()`. Plaintext passwords
  are never stored.
- **Output escaping** — everything echoed back into HTML goes
  through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`, matching the
  pattern already used by `partials/text.php`.
- **`config/`, `includes/`, and `sql/` are blocked from direct web
  access** via `.htaccess` (`Require all denied`) — if you're on
  nginx instead of Apache, add an equivalent `location` block
  denying those paths.
- **Session timeout** — admin sessions expire after 30 minutes of
  inactivity (`includes/admin_auth.php`).
- **Basic login rate limiting** on `/admin/login.php` (10 attempts
  per 15 minutes).
- Reviews submitted publicly are inserted as **unpublished** and
  only appear after an admin approves them from `/admin/reviews.php`
  — this stops random visitors from posting live content straight to
  a public review card.

## 5. Extending this

- To make the home page review cards pull live data instead of the
  two static ones, query
  `SELECT * FROM reviews WHERE is_published = 1 ORDER BY created_at DESC LIMIT 2`
  in `index.php` and loop over the results the same way
  `partials/text.php`'s `paragraph()` helper is used elsewhere.
- The `sanitize_*()` / `verify_csrf()` / `json_response()` helpers in
  `includes/functions.php` and `includes/csrf.php` are written to be
  reused by any future form — follow the same pattern in
  `actions/register.php` as a template.
