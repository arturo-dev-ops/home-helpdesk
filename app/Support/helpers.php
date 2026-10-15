<?php
declare(strict_types=1);

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function currentUser(): ?array
{
    $user = $_SESSION['user'] ?? null;
    if (
        !is_array($user)
        || !isset($user['id'], $user['nombre'], $user['email'], $user['rol'])
        || !in_array($user['rol'], ['admin', 'familiar'], true)
    ) {
        return null;
    }

    return $user;
}

function requireAuthenticatedUser(): array
{
    $user = currentUser();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function hasValidCsrfToken(mixed $submittedToken): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? null;

    return is_string($submittedToken)
        && is_string($sessionToken)
        && hash_equals($sessionToken, $submittedToken);
}
