<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../app/bootstrap.php';

$email = $argv[1] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php tools\\set_password.php correo@casa.local\n");
    exit(1);
}

try {
    $connection = Database::getInstance()->getConnection();
    $statement = $connection->prepare('SELECT id, nombre FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if ($user === false) {
        fwrite(STDERR, "No existe ningún usuario con ese correo.\n");
        exit(1);
    }

    fwrite(STDOUT, 'Nueva contraseña para ' . $user['nombre'] . ' (mínimo 12 caracteres): ');
    $password = rtrim((string) fgets(STDIN), "\r\n");
    fwrite(STDOUT, 'Repite la contraseña: ');
    $confirmation = rtrim((string) fgets(STDIN), "\r\n");

    if (strlen($password) < 12) {
        fwrite(STDERR, "La contraseña debe tener al menos 12 caracteres.\n");
        exit(1);
    }

    if (!hash_equals($password, $confirmation)) {
        fwrite(STDERR, "Las contraseñas no coinciden.\n");
        exit(1);
    }

    $update = $connection->prepare('UPDATE users SET password = :password WHERE id = :id');
    $update->execute([
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $user['id'],
    ]);

    fwrite(STDOUT, "Contraseña configurada para " . $user['nombre'] . ".\n");
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    fwrite(STDERR, "No se pudo actualizar la contraseña. Revisa la conexión a MySQL.\n");
    exit(1);
}
