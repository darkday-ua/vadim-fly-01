<?php
$content = '<h1>Dashboard</h1><p>Logged in as user id ' . (int) ($userId ?? 0) . '.</p>';
$content .= '<form method="post" action="/logout"><button type="submit">Log out</button></form>';
$nav = '<a href="/">Home</a> <a href="/dashboard">Dashboard</a>';
require __DIR__ . '/layout.php';
