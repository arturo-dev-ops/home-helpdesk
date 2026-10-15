<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/User.php';

final class UserRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return User[]
     */
    public function findAllFamiliares(): array
    {
        $statement = $this->connection->query(
            "SELECT id, nombre, email, rol
             FROM users
             WHERE rol = 'familiar'
             ORDER BY nombre, id"
        );

        return array_map(
            static function (array $row): User {
                return User::fromArray($row);
            },
            $statement->fetchAll()
        );
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nombre, email, password, rol FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }
}
