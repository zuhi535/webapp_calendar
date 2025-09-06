<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Webapp', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="?page=calendar" class="<?= (isset($_GET['page']) && $_GET['page'] === 'calendar') ? 'active' : '' ?>">Naptár</a>
        <a href="?page=projects" class="<?= (isset($_GET['page']) && $_GET['page'] === 'projects') ? 'active' : '' ?>">Projektek</a>
        <a href="?page=notes" class="<?= (isset($_GET['page']) && $_GET['page'] === 'notes') ? 'active' : '' ?>">Jegyzetek</a>
    </nav>
    <main>
        <?= $content ?>
    </main>
    <script src="assets/js/app.js"></script>
</body>
</html>