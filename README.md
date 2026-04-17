# CO1418 Assignment 2 – Student Shop Web Application

## Submission Details
- **Student Name:** Maximos Philippou
- **Student ID:** 21311292
- **Website URL:** https://vesta.uclan.ac.uk/~mphilippou4/
  
## Dummy Account (for testing)
- **Credentials:** Available on the README.md file uploaded on blackboard

- ## Test Discount Code
- **GRAD25** offers 25% off and is Server-side validated against 'tbl_offers' on every cart render and at checkout
It can be applied from the cart pages

## Database Setup
Import the `database.sql` file I submitted via phpMyAdmin to recreate the full schema, it icludes the additional discount-code columns I added to `tbl_offers` (`offer_code`, `offer_discount`).
The connection settings are in `connect.php`. Vesta credentials are already populated for the live site.

## Marking Criteria — Feature Map
The numbering below matches the order in which the criteria appear in the assignment brief,
broken down by mark band. Each line points to the file(s) that implement the feature.
---
### Pass band (42)
1. **Server-side operation** — All four Assignment 1 pages converted to PHP and are live on Vesta (`index.php`, `products.php`, `item.php`, `cart.php`).
 Supporting pages: `login.php`, `register.php`, `logout.php`, `checkout.php`, `cart_actions.php`, `404.php`. Helpers: `connect.php`, `cart_helper.php`.
2. **Connects to a database using PHP** — `connect.php` opens a `mysqli_connect` connection with Vesta credentials and dies cleanly on failure.
3. **Applies SQL to query information** — `index.php` dies query `tbl_offers` iterates the result with `mysqli_fetch_assoc`.
4. **Includes a video demo** — Submitted via Blackboard alongside this zip.

### Third band (45+)
5. **Suitable presentation of offers** — `index.php` echoes a styled `.offer-card` div per offer (HTML emitted from PHP). Card style matches the Assignment 1 product-card visual language (`styles.css` `.offer-card`).
6. **Lab demo** — Will be presented in the scheduled session.
7. **README file** — This file.

### Lower-second band (50+)
8. **Basic login functionality** — `login.php` validates email/password against `tbl_users` using a prepared statement and `password_verify()`.
9. **PHP sessions applied appropriately** — `session_start()` at the top of every page that touches `$_SESSION`. Session-controlled access enforced for: posting reviews (`item.php`), adding to cart (`products.php`, `item.php`), checking out (`checkout.php`).
10. **Personalised greeting and menu** — `index.php` displays "Welcome back, [name]" when logged in. Every page's nav swaps between "Login" and "Logout" based on `$_SESSION['user_id']`.

### Upper-second band (60+)
11. **Product information retrieved from the database** — `products.php` queries `tbl_products`, iterates the result, and renders title, image, description, price, stock label, "View More", and "Add to Cart" for each row. Guests are redirected to `login.php` when attempting to add to cart.
12. **Functional user registration** — `register.php` validates input, hashes the password with bcrypt, inserts into `tbl_users` via prepared statement, verifies success with `mysqli_stmt_affected_rows`, and redirects to login with a success flag.
13. **Appropriate form validation** — Both client-side (JS in `register.php`, `item.php`) and server-side (PHP) validation on every form. Server-side uniqueness check on email enforced at register.

### 70+ band
14. **PHP-powered item page** — `item.php` reads the product ID from the `?id=` GET parameter (which is casted to int), queries `tbl_products` with a prepared statement, and renders. No `sessionStorage` used.
15. **Product reviews and average score calculation** — `item.php` queries `tbl_reviews` joined to `tbl_users` for reviewer names, and uses SQL `AVG(review_rating)` to calculate the rating. Displayed both numerically (`X / 5`) and graphically (filled-star icons via `renderStars()`).
16. **Allows verified users to post reviews** — Logged-in users see a review form (title, rating, description). Inserts go into `tbl_reviews` via prepared statement, with both client- and server-side validation.

### 80+ band (First)
17. **PHP-powered cart page** — Cookie-based cart in `cart_helper.php`. Discount codes pulled from `tbl_offers` and re-validated server-side on every cart render. Checkout (`checkout.php`) re-fetches prices from the database (never trusts cookie data), inserts into `tbl_orders`, verifies via `mysqli_stmt_affected_rows`, and shows a thank-you confirmation. Implements the Post/Redirect/Get pattern so refresh does not re-submit the order.
18. **Secure password storage and verification** — `password_hash($pw, PASSWORD_BCRYPT)` on register; `password_verify()` on login. Passwords are never stored or transmitted in plaintext.
19. **Wider security considerations** — See the Security Features section below.
20. **Professional-looking website with no significant usability flaws** — See the Polish & Usability section below.
---

## Security Features
Implementing criterion 19 ("wider security considerations").

 Techniques:
 **Prepared statements with `bind_param`** on every query that touches user input
 **`htmlspecialchars()` on every echoed DB value or session value** `on all pages`
 **`password_hash(PASSWORD_BCRYPT)` + `password_verify()`** on `register.php` and `login.php` pages
 **Strong password rules** — minimum 8 chars, at least one number, at least one uppercase. Enforced both client-side (JS) and server-side (PHP)
 **`session_regenerate_id(true)` after successful login** to prevent session fixation on login
 **Hardened session cookie** — `HttpOnly`, `Secure`, `SameSite=Lax`. Mitigates XSS session theft and CSRF applied implemented on `connect.php`
 **`error_reporting` display suppressed in production** so DB paths and variable names cannot leak via warnings applied implemented on `connect.php`
 **POST-only cart actions** — `cart_actions.php` rejects GET requests
 **Whitelisted redirect targets** to prevent open-redirect attacks implemented on  `cart_actions.php` 
 **Whitelisted product filter values** so the `WHERE` clause cannot be tampered via the URL implemented on `products.php` 
 **Server-side product price re-fetch at checkout** — checkout never trusts prices/titles from the cookie; everything is re-pulled from the DB before order insert implemented on `checkout.php`
 **Server-side discount re-validation on every cart render and again at checkout** — bypasses tampered or expired codes implemented on `cart.php`and `checkout.php`
 **Type-cast all IDs to `int`** before using in queries or building IN-clause placeholders implemented on all cart/checkout/product code
 **Logged-in check enforced server-side** for all add-to-cart, review-post, and checkout actions (not just hidden in UI) implemented on `cart_actions.php`, `item.php`, `checkout.php`
 **PRG (Post/Redirect/Get) pattern on checkout** so refreshing the confirmation page does not re-submit the order implemented on `checkout.php`

## Polish & Usability Features
Implementing criterion 20 ("professional-looking, no significant usability flaws").

### Accessibility
- Semantic HTML (`<nav>`, `<footer>`, headings in correct order).
- All form inputs have either an associated `<label for="...">` or an `aria-label` attribute (cart quantity inputs, discount-code input, action buttons).
- `aria-label` on action buttons that might be ambiguous out of context (Update, Remove, Apply discount, Add to cart with product name).
- Filled-star icons paired with numeric rating so the average score is not communicated by colour alone.

### Usability
- Per-page `<title>` so browser tabs and screen readers correctly announce which page the user is on.
- Custom **404 page** (`404.php` + `.htaccess`) — matches the site design with full nav, footer, and quick links back to the homepage and products page. Sends a real HTTP 404 status.
- Cart **badge** in the navbar showing item count, visible from every page.
- Stock-status **filter** on the products page (good / low / out of stock).
- Live **password-strength indicator** on the registration form.
- **Welcome-back banner** on the homepage for logged-in users.
- **Flash messages** for cart actions (success and error styles).
- Responsive layout — hamburger nav on mobile, single-column cart layout below 600px breakpoint.
- "Back to top" button on the products page.
- Server-side and client-side validation produce **clearly visible error messages** for every form.
- Friendly **empty-cart message** with a "Continue shopping" link instead of a blank page.
- Product images use descriptive `alt` text drawn from the product title.

### Code quality
- Tabs for indentation, snake_case PHP identifiers, camelCase JavaScript identifiers — consistent across the codebase.
- Comments above every logic block.
- All resources organised in a flat root folder for simple deployment.

---

## Technologies Used
- **Front-end:** HTML5, CSS3, JavaScript
- **Back-end:** PHP, MySQL (via mysqli)
- **Server:** UCLan Vesta Server (vesta.uclan.ac.uk)

## Resources and References
- UCLan CO1418 Lecture materials and lab worksheets
- PHP Manual: https://www.php.net/manual/en/
- W3Schools PHP MySQL: https://www.w3schools.com/php/php_mysql_intro.asp
