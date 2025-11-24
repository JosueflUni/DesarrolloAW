<?php
// controladores/SupervisorController.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Supervisor.php';

/**
 * Controlador para operaciones del Supervisor
 */
class SupervisorController {
    private $supervisorModel;
    private $nombreEmpleado;

    public function __construct() {
        // Verificar autenticación
        if (!SessionManager::isLoggedIn() || SessionManager::getRol() !== 'SUPERVISOR') {
            header('Location: /dawb/ProyectoFinal/public/index.php');
            exit;
        }

        $this->supervisorModel = new Supervisor();
        $this->nombreEmpleado = SessionManager::getNombreEmpleado();
    }

    /**
     * Mostrar dashboard principal
     */
    public function dashboard() {
        // 1. Obtener todos los caminos del supervisor
        $misCaminos = $this->supervisorModel->getMisCaminos($this->nombreEmpleado);
        
        // 2. Determinar cuál mostrar (URL o el primero)
        $caminoSeleccionadoId = $_GET['camino_id'] ?? ($misCaminos[0]['numCamino'] ?? null);
        
        $caminoActual = null;
        if ($misCaminos) {
            foreach ($misCaminos as $c) {
                if ($c['numCamino'] == $caminoSeleccionadoId) {
                    $caminoActual = $c;
                    break;
                }
            }
            // Si el ID de la URL no coincide, usar el primero
            if (!$caminoActual) {
                $caminoActual = $misCaminos[0];
                $caminoSeleccionadoId = $caminoActual['numCamino'];
            }
        }

        // 3. Obtener datos pasando el ID CORRECTO (Aquí estaba el problema lógico)
        // Notarás que ahora pasamos $caminoSeleccionadoId en lugar de $this->nombreEmpleado
        $data = [
            'nombreEmpleado' => $this->nombreEmpleado,
            'nombreCompleto' => SessionManager::getSessionInfo()['nombre_completo'],
            'misCaminos' => $misCaminos,
            'miCamino' => $caminoActual,
            'jaulasCamino' => $this->supervisorModel->getJaulasCamino($this->nombreEmpleado), // Este usa nombreEmpleado porque filtra internamente
            'personalCamino' => $this->supervisorModel->getPersonalCamino($this->nombreEmpleado),
            'estadisticas' => $this->supervisorModel->getEstadisticasCamino($caminoSeleccionadoId), // <--- CORREGIDO
            'alertasMedicas' => $this->supervisorModel->getAlertasMedicas($caminoSeleccionadoId),   // <--- CORREGIDO
            'distribucionEspecies' => $this->supervisorModel->getDistribucionEspecies($this->nombreEmpleado)
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
        
        $estadisticas = $this->supervisorModel->getEstadisticasCamino($this->nombreEmpleado);
        echo json_encode($estadisticas);
    }

    /**
     * Obtener alertas médicas (AJAX)
     */
    public function getAlertas() {
        header('Content-Type: application/json');
        
        $alertas = $this->supervisorModel->getAlertasMedicas($this->nombreEmpleado);
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