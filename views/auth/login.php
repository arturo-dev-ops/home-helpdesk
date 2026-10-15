<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Incidencias Casa</title>
    <?php require __DIR__ . '/../partials/pwa-head.php'; ?>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<main class="auth-page">
    <section class="panel auth-panel" aria-labelledby="login-heading">
        <h1 id="login-heading">Incidencias Casa</h1>
        <p class="subtitle">Inicia sesión para continuar.</p>

        <?php if (is_string($error)): ?>
            <p class="notice error"><?= escapeHtml($error) ?></p>
        <?php endif; ?>

        <form action="login.php" method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml($token) ?>">
            <div>
                <label for="email">Correo</label>
                <input id="email" name="email" type="email" autocomplete="username" required autofocus>
            </div>
            <div>
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button type="submit">Iniciar sesión</button>
        </form>
    </section>
</main>
</body>
</html>
