<?php
// controladores/SupervisorController.php - VERSIÓN CORREGIDA

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Supervisor.php';

class SupervisorController {
    private $supervisorModel;
    private $nombreEmpleado;

    public function __construct() {
        if (!SessionManager::isLoggedIn() || SessionManager::getRol() !== 'SUPERVISOR') {
            header('Location: /dawb/ProyectoFinal/public/index.php');
            exit;
        }

        $this->supervisorModel = new Supervisor();
        $this->nombreEmpleado = SessionManager::getNombreEmpleado();
    }

    /**
     * ⭐ DASHBOARD CORREGIDO
     */
    public function dashboard() {
        // 1. Obtener todos los caminos del supervisor
        $misCaminos = $this->supervisorModel->getMisCaminos($this->nombreEmpleado);
        
        // 2. Determinar cuál camino mostrar
        $caminoSeleccionadoId = $_GET['camino_id'] ?? ($misCaminos[0]['numCamino'] ?? null);
        
        $miCamino = null;
        if ($misCaminos) {
            foreach ($misCaminos as $c) {
                if ($c['numCamino'] == $caminoSeleccionadoId) {
                    $miCamino = $c;
                    break;
                }
            }
            if (!$miCamino) {
                $miCamino = $misCaminos[0];
                $caminoSeleccionadoId = $miCamino['numCamino'];
            }
        }

        // 3. ⭐ CORRECCIÓN CRÍTICA: Obtener datos del camino ESPECÍFICO seleccionado
        $data = [
            'nombreEmpleado' => $this->nombreEmpleado,
            'nombreCompleto' => SessionManager::getSessionInfo()['nombre_completo'],
            'misCaminos' => $misCaminos,
            'miCamino' => $miCamino,
            'jaulasCamino' => $this->supervisorModel->getJaulasCaminoPorId($caminoSeleccionadoId),
            'personalCamino' => $this->supervisorModel->getPersonalCaminoPorId($caminoSeleccionadoId),
            'estadisticas' => $this->supervisorModel->getEstadisticasCamino($caminoSeleccionadoId),
            'alertasMedicas' => $this->supervisorModel->getAlertasMedicas($caminoSeleccionadoId),
            'distribucionEspecies' => $this->supervisorModel->getDistribucionEspeciesPorId($caminoSeleccionadoId)
        ];

        require __DIR__ . '/../vistas/supervisor/dashboard.php';
    }

    /**
     * Obtener información del camino (AJAX)
     */
    public function getCamino() {
        header('Content-Type: application/json');
        
        $camino = $this->supervisorModel->getMiCamino($this->nombreEmpleado);
        echo json_encode($camino);
    }

    /**
     * Obtener jaulas del camino (AJAX)
     */
    public function getJaulas() {
        header('Content-Type: application/json');
        
        $jaulas = $this->supervisorModel->getJaulasCamino($this->nombreEmpleado);
        echo json_encode($jaulas);
    }

    /**
     * Obtener detalle de una jaula (AJAX)
     */
    public function getDetalleJaula() {
        header('Content-Type: application/json');
        
        $numJaula = $_GET['jaula'] ?? null;
        
        if (!$numJaula) {
            http_response_code(400);
            echo json_encode(['error' => 'Número de jaula requerido']);
            return;
        }

        $detalle = $this->supervisorModel->getDetalleJaula($numJaula, $this->nombreEmpleado);
        
        if (isset($detalle['error'])) {
            http_response_code(403);
        }

        echo json_encode($detalle);
    }

    /**
     * Obtener personal del camino (AJAX)
     */
    public function getPersonal() {
        header('Content-Type: application/json');
        
        $personal = $this->supervisorModel->getPersonalCamino($this->nombreEmpleado);
        echo json_encode($personal);
    }

    /**
     * Obtener estadísticas (AJAX)
     */
    public function getEstadisticas() {
        header('Content-Type: application/json');
        
        $caminoId = $_GET['camino_id'] ?? null;
        
        if (!$caminoId) {
            // Obtener el primer camino del supervisor
            $caminos = $this->supervisorModel->getMisCaminos($this->nombreEmpleado);
            $caminoId = $caminos[0]['numCamino'] ?? null;
        }
        
        if (!$caminoId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de camino requerido']);
            return;
        }
        
        $estadisticas = $this->supervisorModel->getEstadisticasCamino($caminoId);
        echo json_encode($estadisticas);
    }

    /**
     * Obtener alertas médicas (AJAX)
     */
    public function getAlertas() {
        header('Content-Type: application/json');
        
        $caminoId = $_GET['camino_id'] ?? null;
        
        if (!$caminoId) {
            $caminos = $this->supervisorModel->getMisCaminos($this->nombreEmpleado);
            $caminoId = $caminos[0]['numCamino'] ?? null;
        }
        
        if (!$caminoId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de camino requerido']);
            return;
        }
        
        $alertas = $this->supervisorModel->getAlertasMedicas($caminoId);
        echo json_encode($alertas);
    }

    /**
     * Obtener distribución de especies (AJAX)
     */
    public function getEspecies() {
        header('Content-Type: application/json');
        
        $especies = $this->supervisorModel->getDistribucionEspecies($this->nombreEmpleado);
        echo json_encode($especies);
    }

    /**
     * Generar reporte
     */
    public function generarReporte() {
        header('Content-Type: application/json');
        
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

        $reporte = $this->supervisorModel->generarReporte(
            $this->nombreEmpleado, 
            $fechaInicio, 
            $fechaFin
        );

        if (!$reporte) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al generar reporte']);
            return;
        }

        echo json_encode($reporte);
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'SupervisorController.php') {
    $controller = new SupervisorController();
    $action = $_GET['action'] ?? 'dashboard';

    switch ($action) {
        case 'dashboard':
            $controller->dashboard();
            break;
        case 'camino':
            $controller->getCamino();
            break;
        case 'jaulas':
            $controller->getJaulas();
            break;
        case 'detalle_jaula':
            $controller->getDetalleJaula();
            break;
        case 'personal':
            $controller->getPersonal();
            break;
        case 'estadisticas':
            $controller->getEstadisticas();
            break;
        case 'alertas':
            $controller->getAlertas();
            break;
        case 'especies':
            $controller->getEspecies();
            break;
        case 'reporte':
            $controller->generarReporte();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Acción no encontrada']);
    }
}
?>