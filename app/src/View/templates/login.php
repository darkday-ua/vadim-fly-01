<?php
$content = '<h1>Log in</h1>';
if (!empty($error)) {
    $content .= '<p class="error">' . htmlspecialchars($error) . '</p>';
}
$content .= '<form method="post" action="/login">
    <label>Username <input type="text" name="username" required autocomplete="username"></label>
    <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit">Log in</button>
</form>';
$nav = '<a href="/">Home</a>';
require __DIR__ . '/layout.php';
