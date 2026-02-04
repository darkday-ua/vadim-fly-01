# Authentication System

Basic session-based authentication with username/password and last login tracking.

## Database Schema

The `users` table includes:

- `id` - Primary key
- `username` - Unique username (max 191 chars)
- `password_hash` - Bcrypt hashed password
- `last_login_at` - Timestamp of last successful login (nullable)
- `click_counter` - Integer, default 0 (per-user counter)
- `created_at` - Account creation timestamp

## Migration System

### Running Migrations

```bash
php scripts/migrate.php
```

This will:
1. Create a `migrations` table to track executed migrations
2. Run any new migrations in `app/migrations/` (alphabetically)
3. Skip migrations that have already run

### Migration Files

- `001_create_users_table.php` - Creates users table (username, password_hash, last_login_at, click_counter, created_at)
- `002_add_last_login_to_existing_users.php` - Adds `last_login_at` to existing users table
- `003_add_click_counter_to_users.php` - Adds `click_counter` to existing users table

## Authentication Flow

1. **Login attempt** (`Auth::attempt($db, $username, $password)`):
   - Validates username/password against database
   - Returns user ID on success, `null` on failure
   - Uses `password_verify()` for secure password checking

2. **Login** (`Auth::login($db, $userId)`):
   - Stores user ID in session
   - Updates `last_login_at` timestamp in database

3. **Logout** (`Auth::logout()`):
   - Removes user ID from session

4. **Check auth** (`Auth::isLoggedIn()`):
   - Returns `true` if user is logged in

5. **Get user** (`Auth::user($db)`):
   - Returns user data array (id, username, last_login_at, click_counter, created_at) or `null`

6. **Click counter** (`Auth::incrementClickCounter($db)` / `Auth::decrementClickCounter($db)`):
   - Increment or decrement the current user's `click_counter` (decrement does not go below 0)

## Usage in Routes

```php
// Protected route (requires auth)
$router->get('/dashboard', function ($request, $app) {
    $user = $app['auth']->user($app['db']);
    // $user['last_login_at'] contains the timestamp
    return Response::html($view->render('dashboard', ['user' => $user]));
}, true);  // true = requires authentication

// Login route
$router->post('/login', function ($request, $app) {
    $userId = $app['auth']->attempt($app['db'], $username, $password);
    if ($userId !== null) {
        $app['auth']->login($app['db'], $userId);  // Records last_login_at
        return Response::redirect('/dashboard');
    }
    // Show error
});
```

## Password Hashing

Passwords are hashed using PHP's `password_hash()` with bcrypt (default). When creating users:

```php
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$db->execute(
    'INSERT INTO users (username, password_hash) VALUES (?, ?)',
    [$username, $passwordHash]
);
```

## Example: Creating a User

```php
$username = 'newuser';
$password = 'secure_password';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$db->execute(
    'INSERT INTO users (username, password_hash) VALUES (?, ?)',
    [$username, $passwordHash]
);
```
