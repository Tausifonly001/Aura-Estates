# Aura Estates - Ultra-Premium Real Estate Portal

## 1. Technical Breakdown

### Data Flow
1.  **MySQL Database**: Stores `properties`, `users`, and `inquiries` in a normalized schema.
2.  **PHP Backend (PDO)**:
    -   `config/database.php` establishes a secure PDO connection.
    -   `models/Property.php` handles data logic (CRUD).
    -   `controllers/PropertyController.php` acts as an intermediary, formatting data.
    -   `api/properties.php` instantiates the controller and returns JSON.
3.  **AngularJS Frontend**:
    -   `PropertyController` in `assets/js/app.js` initializes.
    -   `$http.get('api/properties.php')` fetches the JSON data asynchronously.
    -   Data is assigned to `$scope.properties`.
    -   `ng-repeat` in `index.html` iterates over `$scope.properties` and renders the DOM.
    -   **$digest Cycle**: When `$http` returns, AngularJS triggers a `$digest` cycle, checking watchers for changes (like `$scope.properties`). It detects the change and updates the DOM automatically.
4.  **Real-time Filtering**:
    -   The filter inputs (price slider, type buttons) are bound to `$scope.filters` via `ng-model`.
    -   The `ng-repeat` directive uses a custom filter function `filter:propertyFilter`.
    -   When `ng-model` changes, the `$digest` cycle runs, the filter function re-evaluates each item, and the DOM updates instantly without a server round-trip. This is much faster than server-side reloading because the data is already in the client's memory.

### GSAP Animations
-   **Hero**: Timeline animation runs on load (`window.onload`), animating title, subtitle, and background with different delays and easings.
-   **Scroll**: `ScrollTrigger` is used to trigger animations when property cards enter the viewport. `stagger` is used to create a cascading effect.

## 2. Security Implementation

-   **SQL Injection Prevention**: We use **PDO Prepared Statements** (`$conn->prepare()`, `bindParam()`) for ALL database queries. This separates the SQL code from the data, making injection impossible.
-   **XSS Protection**: Inputs are sanitized using `htmlspecialchars(strip_tags())` before being saved to the database. Output encoding is handled by AngularJS by default (context-aware escaping).
-   **Password Hashing**: User passwords are hashed using `password_hash()` (BCrypt) and verified with `password_verify()`. Plain text passwords are never stored.
-   **Session Management**: Admin access is protected by PHP Sessions (`session_start()`). `admin/dashboard.php` checks for `$_SESSION['user_id']` and role before rendering.
-   **CSRF**: (Basic implementation included via session checks, for full production CSRF tokens should be added to forms).

## 3. Performance Optimization

-   **Mobile-First Tailwind**: CSS is utility-first and can be purged to a very small size in a build step.
-   **AngularJS Single Page feel**: Filtering happens on the client side, eliminating page reloads and reducing server load.
-   **Lazy Loading**: Property images are standard `<img>` tags here but in a full production build, we would use `loading="lazy"` attribute (added to code) or a directive.
-   **GSAP Efficiency**: GSAP is highly optimized for performance, using `requestAnimationFrame` and GPU acceleration (transforms/opacity).
-   **Database**: Indexes on Primary Keys and Foreign Keys ensure fast lookups.

## 4. Deployment Instructions (Shared Hosting)

1.  **Upload Files**: Upload the entire `aura-estates` folder to your `public_html` or `www` directory.
2.  **Database Setup**:
    -   Create a new MySQL database via your hosting control panel (cPanel/Plesk).
    -   Import `database.sql` into this new database using phpMyAdmin.
    -   **IMPORTANT**: Delete `install.php` and `database.sql` from the server after import.
3.  **Configuration**:
    -   Edit `config/database.php`.
    -   Update `$host`, `$db_name`, `$username`, and `$password` with your live database credentials.
4.  **Permissions**: Ensure `assets/` folder has read permissions.
5.  **Access**:
    -   Public: `https://yourdomain.com/`
    -   Admin: `https://yourdomain.com/admin/login.php`
    -   Default Admin Credentials: `admin@aura.com` / `admin123`

## 5. Folder Structure
```
/aura-estates
├── /admin             # Admin Panel (Protected)
├── /api               # JSON Endpoints
├── /assets
│   ├── /css           # Styles
│   └── /js            # App Logic (Angular + GSAP)
├── /config            # Database Connection
├── /models            # PHP Classes (Data Layer)
├── index.html         # Main Application
├── install.php        # DB Installer (Delete after use)
└── database.sql       # Database Schema
```
