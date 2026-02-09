<?php
$user = $user ?? null;
$username = $user['username'] ?? 'Unknown';
$userId = $user['id'] ?? 0;
$lastLogin = $user['last_login_at'] ?? null;
$clickCounter = (int) ($user['click_counter'] ?? 0);

$content = '<h1>Dashboard</h1>';
$content .= '<p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>';
$content .= '<p><strong>User ID:</strong> ' . (int) $userId . '</p>';

if ($lastLogin) {
    $lastLoginFormatted = date('Y-m-d H:i:s', strtotime($lastLogin));
    $content .= '<p><strong>Last Login:</strong> ' . htmlspecialchars($lastLoginFormatted) . '</p>';
} else {
    $content .= '<p><strong>Last Login:</strong> Never</p>';
}

$content .= '<div style="margin: 2rem 0; padding: 1.5rem; border: 2px solid #333; border-radius: 8px; display: inline-block;">';
$content .= '<h2 style="margin-top: 0;">Click Counter</h2>';
$content .= '<div style="font-size: 2rem; font-weight: bold; margin: 1rem 0; text-align: center;">' . $clickCounter . '</div>';
$content .= '<div style="display: flex; gap: 1rem; justify-content: center;">';
$content .= '<form method="post" action="/dashboard/click/increment" style="display: inline;">';
$content .= '<button type="submit" style="padding: 0.75rem 2rem; font-size: 1.5rem; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">+</button>';
$content .= '</form>';
$content .= '<form method="post" action="/dashboard/click/decrement" style="display: inline;">';
$content .= '<button type="submit" style="padding: 0.75rem 2rem; font-size: 1.5rem; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">-</button>';
$content .= '</form>';
$content .= '</div>';
$content .= '</div>';

// Show success/error messages
if (!empty($success ?? '')) {
    $content .= '<div style="margin: 1rem 0; padding: 0.75rem; background: #d1fae5; color: #065f46; border-radius: 4px;">' . htmlspecialchars($success) . '</div>';
}
if (!empty($error ?? '')) {
    $content .= '<div style="margin: 1rem 0; padding: 0.75rem; background: #fee2e2; color: #991b1b; border-radius: 4px;">' . htmlspecialchars($error) . '</div>';
}

// User registration form
$content .= '<div style="margin: 2rem 0; padding: 1.5rem; border: 2px solid #333; border-radius: 8px; max-width: 400px;">';
$content .= '<h2 style="margin-top: 0;">Create New User</h2>';
$content .= '<form method="post" action="/dashboard/users/create">';
$content .= '<label style="display: block; margin-bottom: 0.75rem;">';
$content .= '<strong>User Login (Username):</strong><br>';
$content .= '<input type="text" name="username" required autocomplete="username" style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; box-sizing: border-box;">';
$content .= '</label>';
$content .= '<label style="display: block; margin-bottom: 0.75rem;">';
$content .= '<strong>User Password:</strong><br>';
$content .= '<input type="password" name="password" required autocomplete="new-password" minlength="8" style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; box-sizing: border-box;">';
$content .= '<small style="display: block; margin-top: 0.25rem; color: #666; font-size: 0.875rem;">';
$content .= 'Password must be at least 8 characters and contain: letters (a-z, A-Z), numbers (0-9), and special characters';
$content .= '</small>';
$content .= '</label>';
$content .= '<button type="submit" class="btn btn-primary" style="width: 100%;">Save</button>';
$content .= '</form>';
$content .= '</div>';

$content .= '<div style="margin-top: 2rem;">';
$content .= '<form method="post" action="/logout" style="display: inline;"><button type="submit" class="btn btn-outline">Logout</button></form>';
$content .= '</div>';

$nav = '<a href="/dashboard">Dashboard</a> <form method="post" action="/logout" style="display: inline;"><button type="submit" class="btn btn-outline">Logout</button></form>';
require __DIR__ . '/layout.php';
