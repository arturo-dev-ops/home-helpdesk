<?php
declare(strict_types=1);

final class Incident
{
    public const STATUS_PENDING = 'sin_hacer';
    public const STATUS_IN_PROGRESS = 'en_proceso';
    public const STATUS_DONE = 'hecho';

    private const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
    ];

    private int $id;
    private int $userId;
    private string $userName;
    private string $title;
    private string $status;
    private string $enteredAt;
    private ?string $exitedAt;

    public function __construct(
        int $id,
        int $userId,
        string $userName,
        string $title,
        string $status,
        string $enteredAt,
        ?string $exitedAt
    ) {
        if (!self::isValidStatus($status)) {
            throw new InvalidArgumentException('El estado de la incidencia no es válido.');
        }

        $this->id = $id;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->title = $title;
        $this->status = $status;
        $this->enteredAt = $enteredAt;
        $this->exitedAt = $exitedAt;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['user_id'],
            (string) $row['user_name'],
            (string) $row['titulo'],
            (string) $row['estado'],
            (string) $row['fecha_entrada'],
            $row['fecha_salida'] === null ? null : (string) $row['fecha_salida']
        );
    }

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::VALID_STATUSES, true);
    }

    public static function validateTitle(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            throw new InvalidArgumentException('El título es obligatorio.');
        }

        if (count(preg_split('/\s+/u', $title) ?: []) > 4) {
            throw new InvalidArgumentException('El título debe tener como máximo 4 palabras.');
        }

        if (mb_strlen($title, 'UTF-8') > 255) {
            throw new InvalidArgumentException('El título no puede superar los 255 caracteres.');
        }

        return $title;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEnteredAt(): string
    {
        return $this->enteredAt;
    }

    public function getExitedAt(): ?string
    {
        return $this->exitedAt;
    }
}
