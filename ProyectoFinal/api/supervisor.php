<?php
// api/supervisor.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Supervisor.php';

// Verificar autenticación
if (!SessionManager::isLoggedIn() || SessionManager::getRol() !== 'SUPERVISOR') {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$supervisorModel = new Supervisor();
$nombreEmpleado = SessionManager::getNombreEmpleado();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'camino':
            // Obtener información del camino
            $camino = $supervisorModel->getMiCamino($nombreEmpleado);
            echo json_encode($camino);
            break;

        case 'jaulas':
            // Obtener jaulas del camino
            $jaulas = $supervisorModel->getJaulasCamino($nombreEmpleado);
            echo json_encode($jaulas);
            break;

        case 'detalle_jaula':
            // Obtener detalle de una jaula
            $numJaula = $_GET['jaula'] ?? null;
            
            if (!$numJaula) {
                http_response_code(400);
                echo json_encode(['error' => 'Parámetro jaula requerido']);
                exit;
            }
            
            $detalle = $supervisorModel->getDetalleJaula($numJaula, $nombreEmpleado);
            
            if (isset($detalle['error'])) {
                http_response_code(403);
            }
            
            echo json_encode($detalle);
            break;

        case 'personal':
            // Obtener personal del camino
            $personal = $supervisorModel->getPersonalCamino($nombreEmpleado);
            echo json_encode($personal);
            break;

        case 'estadisticas':
            // Obtener estadísticas del camino
            $estadisticas = $supervisorModel->getEstadisticasCamino($nombreEmpleado);
            echo json_encode($estadisticas);
            break;

        case 'alertas':
            // Obtener alertas médicas
            $alertas = $supervisorModel->getAlertasMedicas($nombreEmpleado);
            echo json_encode($alertas);
            break;

        case 'especies':
            // Obtener distribución de especies
            $especies = $supervisorModel->getDistribucionEspecies($nombreEmpleado);
            echo json_encode($especies);
            break;

        case 'reporte':
            // Generar reporte
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
            
            $reporte = $supervisorModel->generarReporte($nombreEmpleado, $fechaInicio, $fechaFin);
            
            if (!$reporte) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al generar reporte']);
                exit;
            }
            
            echo json_encode($reporte);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Supervisor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}