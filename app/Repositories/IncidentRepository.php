<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Incident.php';

final class IncidentRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return Incident[]
     */
    public function findAll(?string $status = null): array
    {
        if ($status !== null && !Incident::isValidStatus($status)) {
            throw new InvalidArgumentException('El filtro de estado no es válido.');
        }

        $sql = 'SELECT i.id, i.user_id, u.nombre AS user_name, i.titulo, i.estado,
                       i.fecha_entrada, i.fecha_salida
                FROM incidencias i
                INNER JOIN users u ON u.id = i.user_id';

        if ($status !== null) {
            $sql .= ' WHERE i.estado = :estado';
        }

        $sql .= ' ORDER BY i.fecha_entrada DESC, i.id DESC';
        $statement = $this->connection->prepare($sql);
        $statement->execute($status === null ? [] : ['estado' => $status]);

        return array_map(
            static function (array $row): Incident {
                return Incident::fromArray($row);
            },
            $statement->fetchAll()
        );
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $counts = [
            Incident::STATUS_PENDING => 0,
            Incident::STATUS_IN_PROGRESS => 0,
            Incident::STATUS_DONE => 0,
        ];
        $statement = $this->connection->query(
            'SELECT estado, COUNT(*) AS total FROM incidencias GROUP BY estado'
        );

        foreach ($statement->fetchAll() as $row) {
            $counts[(string) $row['estado']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * @return Incident[]
     */
    public function findByUserId(int $userId): array
    {
        $statement = $this->connection->prepare(
            'SELECT i.id, i.user_id, u.nombre AS user_name, i.titulo, i.estado,
                    i.fecha_entrada, i.fecha_salida
             FROM incidencias i
             INNER JOIN users u ON u.id = i.user_id
             WHERE i.user_id = :user_id
             ORDER BY i.fecha_entrada DESC, i.id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return array_map(
            static function (array $row): Incident {
                return Incident::fromArray($row);
            },
            $statement->fetchAll()
        );
    }

    public function create(int $userId, string $title): void
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('Selecciona un familiar válido.');
        }

        $title = Incident::validateTitle($title);
        $statement = $this->connection->prepare(
            'INSERT INTO incidencias (user_id, titulo, estado)
             VALUES (:user_id, :titulo, :estado)'
        );
        $statement->execute([
            'user_id' => $userId,
            'titulo' => $title,
            'estado' => Incident::STATUS_PENDING,
        ]);
    }

    public function updateStatus(int $incidentId, string $status): void
    {
        if ($incidentId < 1 || !Incident::isValidStatus($status)) {
            throw new InvalidArgumentException('La incidencia o el estado no son válidos.');
        }

        $statement = $this->connection->prepare(
            'UPDATE incidencias
             SET estado = :estado,
                 fecha_salida = CASE
                     WHEN :estado_salida = :hecho THEN COALESCE(fecha_salida, CURRENT_TIMESTAMP)
                     ELSE NULL
                 END
             WHERE id = :id'
        );
        $statement->execute([
            'estado' => $status,
            'estado_salida' => $status,
            'hecho' => Incident::STATUS_DONE,
            'id' => $incidentId,
        ]);

        if ($statement->rowCount() === 0) {
            $exists = $this->connection->prepare('SELECT 1 FROM incidencias WHERE id = :id');
            $exists->execute(['id' => $incidentId]);
            if ($exists->fetchColumn() === false) {
                throw new RuntimeException('La incidencia que intentas actualizar no existe.');
            }
        }
    }

    public function deleteCompleted(int $incidentId): void
    {
        if ($incidentId < 1) {
            throw new InvalidArgumentException('La incidencia seleccionada no es válida.');
        }

        $statement = $this->connection->prepare(
            'DELETE FROM incidencias WHERE id = :id AND estado = :estado'
        );
        $statement->execute([
            'id' => $incidentId,
            'estado' => Incident::STATUS_DONE,
        ]);

        if ($statement->rowCount() === 0) {
            $exists = $this->connection->prepare(
                'SELECT estado FROM incidencias WHERE id = :id'
            );
            $exists->execute(['id' => $incidentId]);
            $status = $exists->fetchColumn();

            if ($status === false) {
                throw new RuntimeException('La incidencia que intentas eliminar no existe.');
            }

            throw new RuntimeException('Solo se pueden eliminar incidencias que estén hechas.');
        }
    }
}
