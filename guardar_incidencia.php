<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

startAppSession();

$connection = Database::getInstance()->getConnection();
$controller = new IncidentController(new IncidentRepository($connection));
$controller->store($_SERVER['REQUEST_METHOD'], $_POST);
