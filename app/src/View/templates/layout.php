<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Fly') ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; padding: 1.5rem; max-width: 56rem; margin-inline: auto; }
        nav { margin-bottom: 1.5rem; }
        nav a { margin-right: 1rem; }
        .error { color: #c00; margin-bottom: 1rem; }
        form label { display: block; margin-bottom: 0.25rem; }
        form input { margin-bottom: 0.75rem; padding: 0.35rem 0.5rem; }
        form button { padding: 0.5rem 1rem; cursor: pointer; }
    </style>
</head>
<body>
    <?php if (!empty($nav)): ?>
    <nav><?= $nav ?></nav>
    <?php endif; ?>
    <main><?= $content ?? '' ?></main>
</body>
</html>
