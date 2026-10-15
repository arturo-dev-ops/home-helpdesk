<?php
declare(strict_types=1);

final class AuthController
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function showLogin(): void
    {
        if (currentUser() !== null) {
            header('Location: index.php');
            exit;
        }

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        $token = csrfToken();

        require __DIR__ . '/../../views/auth/login.php';
    }

    public function login(string $method, array $input): void
    {
        if ($method !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Método no permitido.');
        }

        if (!hasValidCsrfToken($input['csrf_token'] ?? null)) {
            http_response_code(403);
            exit('El formulario ha caducado. Vuelve a cargar la página.');
        }

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        if (!is_string($email) || !is_string($password)) {
            $this->failLogin();
        }

        try {
            $user = $this->users->findByEmail(trim($email));
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            http_response_code(503);
            exit('No se pudo acceder a la base de datos. Revisa la configuración de MySQL.');
        }

        if (
            $user === null
            || !is_string($user['password'])
            || !password_verify($password, $user['password'])
        ) {
            $this->failLogin();
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nombre' => (string) $user['nombre'],
            'email' => (string) $user['email'],
            'rol' => (string) $user['rol'],
        ];
        unset($_SESSION['csrf_token']);

        header('Location: index.php');
        exit;
    }

    public function logout(string $method, array $input): void
    {
        if ($method !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Método no permitido.');
        }

        if (!hasValidCsrfToken($input['csrf_token'] ?? null)) {
            http_response_code(403);
            exit('El formulario ha caducado. Vuelve a cargar la página.');
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();

        header('Location: login.php');
        exit;
    }

    private function failLogin(): void
    {
        $_SESSION['error'] = 'Correo o contraseña incorrectos.';
        header('Location: login.php');
        exit;
    }
}
