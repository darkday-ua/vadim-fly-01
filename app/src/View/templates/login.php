<?php
$title = $title ?? 'Login';
$content = '<h1>Welcome to our PoC application</h1>';
if (!empty($error)) {
    $content .= '<p class="error">' . htmlspecialchars($error) . '</p>';
}
$content .= '<form method="post" action="/login">
    <label>Username <input type="text" name="username" required autocomplete="username"></label>
    <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn btn-primary">Login</button>
</form>';
$nav = '';
require __DIR__ . '/layout.php';
