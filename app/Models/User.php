<?php
declare(strict_types=1);

final class User
{
    private int $id;
    private string $nombre;
    private string $email;
    private string $rol;

    public function __construct(int $id, string $nombre, string $email, string $rol)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->rol = $rol;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['nombre'],
            (string) $row['email'],
            (string) $row['rol']
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRol(): string
    {
        return $this->rol;
    }
}
