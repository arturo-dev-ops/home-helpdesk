<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Incidencias Casa</title>
    <?php require __DIR__ . '/../partials/pwa-head.php'; ?>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<main>
    <header>
        <h1>Incidencias Casa</h1>
        <p class="subtitle">
            Bienvenido, <?= escapeHtml($currentUser['nombre']) ?>.
            <?php if ($currentUser['rol'] === 'admin'): ?>
                Aquí puedes revisar, gestionar y resolver las incidencias de la casa.
            <?php else: ?>
                Crea incidencias y consulta el estado de las que has enviado.
            <?php endif; ?>
        </p>
        <form action="logout.php" method="post" class="logout-form">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
            <button type="submit" class="button-secondary">Cerrar sesión</button>
        </form>
    </header>

    <?php if (is_string($success)): ?>
        <p class="notice success"><?= escapeHtml($success) ?></p>
    <?php endif; ?>
    <?php if (is_string($error)): ?>
        <p class="notice error"><?= escapeHtml($error) ?></p>
    <?php endif; ?>
    <?php if ($loadError !== null): ?>
        <p class="notice error"><?= escapeHtml($loadError) ?></p>
    <?php endif; ?>

    <?php if ($currentUser['rol'] === 'familiar'): ?>
    <section class="panel" aria-labelledby="new-incident-heading">
        <h2 id="new-incident-heading">Nueva incidencia</h2>
        <?php if ($loadError !== null): ?>
            <p class="empty">Configura MySQL para empezar.</p>
        <?php else: ?>
            <form action="guardar_incidencia.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
                <input type="hidden" name="action" value="create">
                <div class="form-grid">
                    <div>
                        <label for="titulo">Título (máximo 4 palabras)</label>
                        <input id="titulo" name="titulo" type="text" maxlength="255" required>
                    </div>
                    <button type="submit">Guardar incidencia</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <section class="panel" aria-labelledby="incident-list-heading">
        <h2 id="incident-list-heading">
            <?php if ($currentUser['rol'] === 'admin'): ?>Gestión de incidencias<?php else: ?>Mis incidencias<?php endif; ?>
        </h2>
        <?php if ($currentUser['rol'] === 'admin'): ?>
            <nav class="status-menu" aria-label="Filtrar incidencias por estado">
                <?php foreach ($statusLabels as $status => $label): ?>
                    <a
                        class="<?= $selectedStatus === $status ? 'active' : '' ?>"
                        href="index.php?estado=<?= rawurlencode($status) ?>"
                        <?= $selectedStatus === $status ? 'aria-current="page"' : '' ?>
                    >
                        <?= escapeHtml($label) ?> <span><?= $statusCounts[$status] ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
        <?php if ($loadError !== null): ?>
            <p class="empty">El listado estará disponible cuando se conecte la base de datos.</p>
        <?php elseif ($incidents === []): ?>
            <p class="empty">
                <?php if ($selectedStatus !== ''): ?>
                    No hay incidencias con estado «<?= escapeHtml($statusLabels[$selectedStatus]) ?>».
                <?php else: ?>
                    Todavía no hay incidencias registradas.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Incidencia</th>
                        <th>Solicitante</th>
                        <th>Estado</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <?php if ($currentUser['rol'] === 'admin'): ?><th>Actualizar</th><?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($incidents as $incident): ?>
                        <tr>
                            <td><?= escapeHtml($incident->getTitle()) ?></td>
                            <td><?= escapeHtml($incident->getUserName()) ?></td>
                            <td><?= escapeHtml($statusLabels[$incident->getStatus()]) ?></td>
                            <td><?= escapeHtml($incident->getEnteredAt()) ?></td>
                            <td><?= escapeHtml($incident->getExitedAt() ?? '—') ?></td>
                            <?php if ($currentUser['rol'] === 'admin'): ?>
                            <td>
                                <form action="guardar_incidencia.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="incident_id" value="<?= $incident->getId() ?>">
                                    <select name="estado" aria-label="Nuevo estado para <?= escapeHtml($incident->getTitle()) ?>">
                                        <option value="sin_hacer" <?= $incident->getStatus() === Incident::STATUS_PENDING ? 'selected' : '' ?>>Sin hacer</option>
                                        <option value="en_proceso" <?= $incident->getStatus() === Incident::STATUS_IN_PROGRESS ? 'selected' : '' ?>>En proceso</option>
                                        <option value="hecho" <?= $incident->getStatus() === Incident::STATUS_DONE ? 'selected' : '' ?>>Hecho</option>
                                    </select>
                                    <button type="submit">Guardar</button>
                                </form>
                                <?php if ($incident->getStatus() === Incident::STATUS_DONE): ?>
                                    <form action="guardar_incidencia.php" method="post" class="delete-form" data-confirm="¿Eliminar permanentemente esta incidencia?">
                                        <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
                                        <input type="hidden" name="action" value="delete_completed">
                                        <input type="hidden" name="incident_id" value="<?= $incident->getId() ?>">
                                        <button type="submit" class="button-danger">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<script src="assets/js/confirm-delete.js" defer></script>
</body>
</html>
