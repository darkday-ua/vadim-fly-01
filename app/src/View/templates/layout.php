<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Fly') ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; padding: 1.5rem; max-width: 56rem; margin-inline: auto; }
        nav { margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
        nav a { margin-right: 0.25rem; }
        .error { color: #c00; margin-bottom: 1rem; }
        form label { display: block; margin-bottom: 0.25rem; }
        form input { margin-bottom: 0.75rem; padding: 0.35rem 0.5rem; }
        form button { padding: 0.5rem 1rem; cursor: pointer; }
        .btn { display: inline-block; padding: 0.5rem 1rem; font-size: 1rem; font-weight: 500; text-decoration: none; border-radius: 6px; border: none; cursor: pointer; font-family: inherit; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { background: transparent; color: #374151; border: 1px solid #d1d5db; }
        .btn-outline:hover { background: #f3f4f6; }
        nav .btn { margin-right: 0; }
    </style>
</head>
<body>
    <?php if (!empty($nav)): ?>
    <nav><?= $nav ?></nav>
    <?php endif; ?>
    <main><?= $content ?? '' ?></main>
</body>
</html>
