<?php
declare(strict_types=1);

final class IncidentController
{
    private IncidentRepository $incidents;

    public function __construct(IncidentRepository $incidents)
    {
        $this->incidents = $incidents;
    }

    public function index(): void
    {
        $currentUser = requireAuthenticatedUser();

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $incidents = [];
        $statusCounts = [];
        $selectedStatus = '';
        $loadError = null;

        try {
            if ($currentUser['rol'] === 'admin') {
                $selectedStatus = Incident::STATUS_PENDING;
                $requestedStatus = $_GET['estado'] ?? null;
                if (is_string($requestedStatus) && Incident::isValidStatus($requestedStatus)) {
                    $selectedStatus = $requestedStatus;
                }

                $incidents = $this->incidents->findAll($selectedStatus);
                $statusCounts = $this->incidents->countByStatus();
            } else {
                $incidents = $this->incidents->findByUserId((int) $currentUser['id']);
            }
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $loadError = 'No se pudo conectar o leer la base de datos. Importa database.sql y revisa las variables HOME_HELPDESK_DB_*.';
        }

        $csrfToken = csrfToken();
        $statusLabels = [
            Incident::STATUS_PENDING => 'Sin hacer',
            Incident::STATUS_IN_PROGRESS => 'En proceso',
            Incident::STATUS_DONE => 'Hecho',
        ];
        require __DIR__ . '/../../views/incidents/listado.php';
    }

    public function store(string $method, array $input): void
    {
        if ($method !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Método no permitido.');
        }

        if (!hasValidCsrfToken($input['csrf_token'] ?? null)) {
            http_response_code(403);
            exit('La sesión del formulario ha caducado. Vuelve a cargar la página.');
        }

        $currentUser = requireAuthenticatedUser();

        try {
            $action = $input['action'] ?? '';
            if ($action === 'create') {
                if ($currentUser['rol'] !== 'familiar') {
                    http_response_code(403);
                    exit('Solo los usuarios familiares pueden crear incidencias.');
                }
                $this->createIncident($input, $currentUser);
                $this->redirectWithMessage('success', 'Incidencia creada correctamente.');
            }

            if ($action === 'update_status') {
                if ($currentUser['rol'] !== 'admin') {
                    http_response_code(403);
                    exit('Solo Arturo, como administrador, puede cambiar el estado de las incidencias.');
                }
                $this->updateIncidentStatus($input);
                $this->redirectWithMessage('success', 'Estado de la incidencia actualizado.');
            }

            if ($action === 'delete_completed') {
                if ($currentUser['rol'] !== 'admin') {
                    http_response_code(403);
                    exit('Solo Arturo, como administrador, puede eliminar incidencias.');
                }
                $this->deleteCompletedIncident($input);
                $this->redirectWithMessage('success', 'Incidencia hecha eliminada.');
            }

            throw new InvalidArgumentException('La operación solicitada no es válida.');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $this->redirectWithMessage(
                'error',
                'No se pudo guardar la incidencia. Revisa la configuración y el esquema de MySQL.'
            );
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $this->redirectWithMessage('error', $exception->getMessage());
        }
    }

    private function createIncident(array $input, array $currentUser): void
    {
        $title = $input['titulo'] ?? '';
        if (!is_string($title)) {
            throw new InvalidArgumentException('El título enviado no es válido.');
        }

        $this->incidents->create((int) $currentUser['id'], $title);
    }

    private function updateIncidentStatus(array $input): void
    {
        $incidentId = filter_var($input['incident_id'] ?? null, FILTER_VALIDATE_INT);
        $status = $input['estado'] ?? '';
        if ($incidentId === false || $incidentId === null || !is_string($status)) {
            throw new InvalidArgumentException('Los datos de actualización no son válidos.');
        }

        $this->incidents->updateStatus($incidentId, $status);
    }

    private function deleteCompletedIncident(array $input): void
    {
        $incidentId = filter_var($input['incident_id'] ?? null, FILTER_VALIDATE_INT);
        if ($incidentId === false || $incidentId === null) {
            throw new InvalidArgumentException('La incidencia seleccionada no es válida.');
        }

        $this->incidents->deleteCompleted($incidentId);
    }

    private function redirectWithMessage(string $key, string $message): void
    {
        $_SESSION[$key] = $message;
        header('Location: index.php');
        exit;
    }
}
