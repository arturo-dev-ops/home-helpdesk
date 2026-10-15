<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

startAppSession();
$controller = new AuthController(
    new UserRepository(Database::getInstance()->getConnection())
);
$controller->logout($_SERVER['REQUEST_METHOD'], $_POST);
