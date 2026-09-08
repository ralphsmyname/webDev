# Hinlo Airsoft Zone — Database & CRUD Setup

Kini nga setup nagdugang og tinuod nga MySQL backend para sa tulo ka forms sa site (Join, Contacts, ug home page review form), apil ang gamay nga admin panel sa `/admin` para sa full CRUD (**Create, Read, Update, Delete**) sa tanang impormasyon nga makolekta sa mga forms.

## 1. Unsa ang gidugang

text
config/database.php        PDO connection (reads env vars, safe defaults)
config/.htaccess            blocks direct web access to this folder
includes/csrf.php           session bootstrap + CSRF token helpers
includes/functions.php      sanitize/validate helpers, flash messages, json_response()
includes/admin_auth.php     login guard for /admin pages
actions/register.php        handles the Join Us form (Create)
actions/contact.php         handles the Contacts form (Create)
actions/review.php          handles the home page review form (Create, unpublished by default)
admin/setup.php             one-time "create the first admin account" screen
admin/login.php             /admin/logout.php     session-based auth
admin/index.php             dashboard with counts
admin/registrations.php     list / delete / confirm registrations   (R, U, D)
admin/registration_form.php add / edit a registration                (C, U)
admin/messages.php          list / mark read / delete contact messages
admin/reviews.php           list / publish / delete reviews
admin/review_form.php       add / edit a review
sql/schema.sql              full database schema
sql/.htaccess               blocks direct web access to this folder
.env.example                environment variable template


Ang `join.php`, `contacts.php`, ug `index.php` gi-update aron ang ilang mga forms makasubmit na gyud gamit ang CSRF token ug `name` attributes ngadto sa bag-ong `actions/*.php` endpoints.

Ang `js/main.js` gi-rewrite aron i-submit ang matag `[data-demo-form]` gamit ang `fetch()`. Makapakita usab kini og field-level errors nga gibalik sa server, ug magpakita og success/error message — **walay page reload**.

## 2. Database setup

### Quick start gamit ang XAMPP

1. **I-copy ang site ngadto sa `htdocs`.**

   Ibalhin ang tibuok `site` folder (o ang sulod niini) ngadto sa imong XAMPP `htdocs` directory.

   Pananglitan sa Windows:

   `C:\xampp\htdocs\hinlo\`

   O sa macOS:

   `/Applications/XAMPP/xamppfiles/htdocs/hinlo/`

2. **I-start ang Apache ug MySQL** gikan sa XAMPP Control Panel.

3. **I-import ang schema.**

   Ablihi ang:

   `http://localhost/phpmyadmin`

   Dayon:

   * I-click ang **Import**
   * Pilia ang `sql/schema.sql`
   * I-click ang **Go**

   Kini maghimo sa `hinlo_airsoft` database ug sa upat ka tables.

4. **Ayaw usba ang `config/database.php`** kung stock/default XAMPP setup imong gigamit.

   Ang default values mao ni:

   * host: `localhost`
   * database: `hinlo_airsoft`
   * user: `root`
   * password: empty
   * port: `3306`

   Parehas kini sa kasagarang stock XAMPP installation.

   Usba lang kini kung:

   * adunay password ang imong MySQL root account; o
   * lahi ang MySQL port nga imong gigamit.

5. **Bisitaha ang site:**

   `http://localhost/hinlo/index.php`

   I-adjust ang folder name kung lahi ang imong gigamit.

6. **Himoa ang imong admin account.**

   Adto sa:

   `http://localhost/hinlo/admin/setup.php`

   Kausa ra kini gamiton. Pagsulod og username ug password.

   Ang page dili na motugot og setup pag-usab kung adunay existing admin account.

7. **Pag-login sa admin panel:**

   `http://localhost/site/admin/login.php`

Human niini, kompleto na ang basic setup.

Kung mag-submit ka sa **Join Us**, **Contacts**, o **Review** form sa live site, ang data makita na sa phpMyAdmin:

* `registrations`
* `contact_messages`
* `reviews`

Makita usab kini sulod sa `/admin` panel.

### XAMPP-specific nga mga nota

* Ang XAMPP Apache dili mobasa sa `.htaccess` nga adunay `Require all denied` rules kung ang `AllowOverride All` wala ma-enable para sa `htdocs`.

* Sa ubang XAMPP installations, mahimo nga naka-off kini pinaagi sa default.

* Dili kini makaapekto sa actual functionality kay ang `config/`, `includes/`, ug `sql/` folders walay page output nga kinahanglan ma-access direkta.

* Kung gusto nimo nga ma-apply gyud ang extra protection, ablihi:

  `xampp/apache/conf/httpd.conf`

* Pangitaa ang:

```apache
<Directory "C:/xampp/htdocs">
```

* Dayon himoa nga:

```apache
AllowOverride All
```

* Kung ang port `3306` gigamit na sa laing MySQL installation sa imong computer, usba ang port sa XAMPP `my.ini`.

* Human niana, i-update usab ang `DB_PORT` (o ang fallback value sa `config/database.php`) aron parehas sa imong bag-ong MySQL port.

### General (non-XAMPP) setup

1. **Paghimo sa database ug tables:**

```bash
mysql -u root -p < sql/schema.sql
```

2. **I-set ang tinuod nga credentials** isip environment variables.

   Tan-awa ang `.env.example` para sa template.

   Pwede usab nimo usbon diretso ang fallback defaults sa `config/database.php` kung local development pa.

3. **Bisitaha ang `/admin/setup.php`** kausa aron makahimo sa unang admin login.

   Awtomatikong ma-lock ang setup page kung adunay na'y admin account.

4. **Pag-login sa:**

   `/admin/login.php`

## 3. Giunsa pag-flow sa data

### Public forms → Create

Ang matag form mag-POST ngadto sa iyang katumbas nga `actions/*.php` file.

Ang process mao ni:

1. Susihon kung ang request kay `POST` ug kung sakto ang CSRF token.

2. **Sanitize ang input** — tangtangon ang sobra nga whitespace, strips tags gamit ang `sanitize_string()`, ug i-validate ug i-normalize ang email addresses gamit ang `sanitize_email()` ug `FILTER_VALIDATE_EMAIL`.

3. Susihon ang phone format gamit ang regex.

4. **I-validate ang data** — required fields, length limits, ug format checks. Ang mga errors kolektahon per field.

5. Kung valid ang tanan, i-insert ang row gamit ang **parameterized/prepared statement**.

   Dili gamiton ang string-concatenated SQL, busa protected kini batok sa SQL injection.

6. Ibalik ang JSON response.

7. Ang `main.js` mobasa sa JSON ug magpakita sa success message o specific field errors **nga walay page reload**.

### Admin panel → Read / Update / Delete

Ang tanan nga naa sa `/admin` kinahanglan adunay logged-in session.

Gigamit kini sa:

`includes/admin_auth.php`

Ang tanang state-changing actions sama sa:

* delete
* status change
* save

kay kinahanglan nga **POST request** ug susihon batok sa CSRF token.

Ang tanang SQL statements sulod sa `/admin` naggamit usab og prepared statements.

## 4. Security notes

* **Prepared statements everywhere** — walay raw SQL string building gikan sa request data bisan asa sa codebase.

* **CSRF tokens** — ang tanang forms, public man o admin, adunay CSRF protection ug gi-verify gamit ang `hash_equals()`.

* **Passwords hashed with `password_hash()` / `PASSWORD_DEFAULT`** — ang passwords i-hash gamit ang bcrypt. Ang plaintext passwords dili gyud i-store.

* **Output escaping** — ang tanang data nga i-output ngadto sa HTML moagi sa:

  `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`

  Parehas kini sa pattern nga gigamit sa `partials/text.php`.

* **`config/`, `includes/`, ug `sql/` protected** — gibabagan ang direct web access pinaagi sa `.htaccess` ug `Require all denied`.

* Kung nginx imong web server imbes Apache, kinahanglan ka magdugang og equivalent `location` block aron ma-deny ang access sa maong paths.

* **Session timeout** — ang admin sessions mo-expire human sa **30 minutos nga walay activity**.

* **Basic login rate limiting** — adunay limit nga **10 login attempts sulod sa 15 minutos**.

* **Reviews nga gikan sa public users** kay i-save una isip **unpublished**.

* Ang reviews dili dayon makita sa public website hangtod nga i-approve sa admin pinaagi sa `/admin/reviews.php`.

Kini makatabang pagpugong nga random visitors makapost diretso og content nga makita dayon sa public review cards.

## 5. Pagpalapad sa system

Kung gusto nimo nga ang review cards sa home page mogamit na og **live data** imbes nga duha ka static reviews, mahimo kang mo-query gamit:

```sql
SELECT * FROM reviews
WHERE is_published = 1
ORDER BY created_at DESC
LIMIT 2
```

Dayon i-loop ang results sa `index.php` sa parehas nga paagi nga gigamit sa `partials/text.php` pinaagi sa `paragraph()` helper.

Ang:

* `sanitize_*()`
* `verify_csrf()`
* `json_response()`

nga helpers sa `includes/functions.php` ug `includes/csrf.php` gihimo aron magamit pag-usab sa umaabot nga mga forms.

Kung maghimo ka og bag-ong form, sundi ang pattern nga gigamit sa:

`actions/register.php`

aron consistent ang imong validation, CSRF protection, ug database handling.

