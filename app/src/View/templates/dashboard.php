<?php
$user = $user ?? null;
$username = $user['username'] ?? 'Unknown';
$userId = $user['id'] ?? 0;
$userRole = $user['role'] ?? 'user';
$lastLogin = $user['last_login_at'] ?? null;
$clickCounter = (int) ($user['click_counter'] ?? 0);
$isAdmin = !empty($isAdmin);

$content = '<h1>Dashboard</h1>';
$content .= '<p><strong>Username:</strong> ' . htmlspecialchars($username) . ' <strong>Role:</strong> ' . htmlspecialchars($userRole) . '</p>';
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

// Users list table
$usersList = $usersList ?? [];
$content .= '<div style="margin: 2rem 0; overflow-x: auto;">';
$content .= '<h2>Users</h2>';
$content .= '<table style="width: 100%; border-collapse: collapse; margin-top: 0.5rem;">';
$content .= '<thead><tr style="background: #f3f4f6;">';
$content .= '<th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Username</th>';
$content .= '<th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Role</th>';
$content .= '<th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Click count</th>';
if ($isAdmin) {
    $content .= '<th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Lock / Unlock</th>';
    $content .= '<th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Mute</th>';
    $content .= '<th style="padding: 0.5rem; text-align: left; border: 1px solid #ddd;">Delete</th>';
}
$content .= '</tr></thead><tbody>';
foreach ($usersList as $u) {
    $content .= '<tr>';
    $content .= '<td style="padding: 0.5rem; border: 1px solid #ddd;">' . htmlspecialchars($u['username']) . '</td>';
    $content .= '<td style="padding: 0.5rem; border: 1px solid #ddd;">';
    $isSelf = ((int) $u['id']) === $userId;
    $currentRole = $u['role'] ?? 'user';
    if ($isAdmin && !$isSelf) {
        $content .= '<form method="post" action="/dashboard/users/change-role" style="display: inline;">';
        $content .= '<input type="hidden" name="id" value="' . (int) $u['id'] . '">';
        $content .= '<select name="role" onchange="this.form.submit()" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">';
        $content .= '<option value="user"' . ($currentRole === 'user' ? ' selected' : '') . '>user</option>';
        $content .= '<option value="admin"' . ($currentRole === 'admin' ? ' selected' : '') . '>admin</option>';
        $content .= '</select>';
        $content .= '</form>';
    } else {
        $content .= htmlspecialchars($currentRole);
    }
    $content .= '</td>';
    $content .= '<td style="padding: 0.5rem; border: 1px solid #ddd;">' . (int) $u['click_counter'] . '</td>';
    if ($isAdmin) {
        $content .= '<td style="padding: 0.5rem; border: 1px solid #ddd;">';
        if ($isSelf) {
            $content .= '—';
        } else {
            $content .= '<form method="post" action="/dashboard/users/toggle-lock" style="display: inline;">';
            $content .= '<input type="hidden" name="id" value="' . (int) $u['id'] . '">';
            $content .= '<button type="submit" class="btn ' . ($u['is_locked'] ? 'btn-primary' : 'btn-outline') . '" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">' . ($u['is_locked'] ? 'Unlock' : 'Lock') . '</button>';
            $content .= '</form>';
        }
        $content .= '</td>';
        $content .= '<td style="padding: 0.5rem; border: 1px solid #ddd;">';
        $content .= '<form method="post" action="/dashboard/users/toggle-mute" style="display: inline;">';
        $content .= '<input type="hidden" name="id" value="' . (int) $u['id'] . '">';
        $content .= '<button type="submit" class="btn ' . ($u['is_muted'] ? 'btn-primary' : 'btn-outline') . '" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">' . ($u['is_muted'] ? 'Unmute' : 'Mute') . '</button>';
        $content .= '</form></td>';
        $content .= '<td style="padding: 0.5rem; border: 1px solid #ddd;">';
        $content .= '<form method="post" action="/dashboard/users/delete" style="display: inline;" onsubmit="return confirm(\'Delete this user?\');">';
        $content .= '<input type="hidden" name="id" value="' . (int) $u['id'] . '">';
        $content .= '<button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.875rem; color: #b91c1c;">Delete</button>';
        $content .= '</form></td>';
    }
    $content .= '</tr>';
}
$content .= '</tbody></table></div>';

// Show success/error messages
if (!empty($success ?? '')) {
    $content .= '<div style="margin: 1rem 0; padding: 0.75rem; background: #d1fae5; color: #065f46; border-radius: 4px;">' . htmlspecialchars($success) . '</div>';
}
if (!empty($error ?? '')) {
    $content .= '<div style="margin: 1rem 0; padding: 0.75rem; background: #fee2e2; color: #991b1b; border-radius: 4px;">' . htmlspecialchars($error) . '</div>';
}

// User registration form (admin only)
if ($isAdmin) {
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
$content .= '<label style="display: block; margin-bottom: 0.75rem;">';
$content .= '<strong>Role:</strong><br>';
$content .= '<select name="role" style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; box-sizing: border-box;">';
$content .= '<option value="user">user</option>';
$content .= '<option value="admin">admin</option>';
$content .= '</select>';
$content .= '</label>';
$content .= '<button type="submit" class="btn btn-primary" style="width: 100%;">Save</button>';
$content .= '</form>';
$content .= '</div>';
}

$content .= '<div style="margin-top: 2rem;">';
$content .= '<form method="post" action="/logout" style="display: inline;"><button type="submit" class="btn btn-outline">Logout</button></form>';
$content .= '</div>';

$nav = '<a href="/dashboard">Dashboard</a> <form method="post" action="/logout" style="display: inline;"><button type="submit" class="btn btn-outline">Logout</button></form>';
require __DIR__ . '/layout.php';
