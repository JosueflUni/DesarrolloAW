<?php
// api/guarda.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Guarda.php';

// Verificar autenticación
if (!SessionManager::isLoggedIn() || SessionManager::getRol() !== 'GUARDA') {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$guardaModel = new Guarda();
$nombreEmpleado = SessionManager::getNombreEmpleado();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'animales':
            // Obtener animales de una jaula
            $numJaula = $_GET['jaula'] ?? null;
            if (!$numJaula) { http_response_code(400); echo json_encode(['error' => 'Falta jaula']); exit; }
            echo json_encode($guardaModel->getAnimalesJaula($numJaula, $nombreEmpleado));
            break;

        case 'detalle':
            // Obtener detalle de un animal
            $numIdentif = $_GET['id'] ?? null;
            
            if (!$numIdentif) {
                http_response_code(400);
                echo json_encode(['error' => 'Parámetro id requerido']);
                exit;
            }
            
            $detalle = $guardaModel->getDetalleAnimal($numIdentif, $nombreEmpleado);
            
            if (!$detalle) {
                http_response_code(404);
                echo json_encode(['error' => 'Animal no encontrado']);
                exit;
            }
            
            echo json_encode($detalle);
            break;

        case 'buscar':
            // Buscar animal
            $termino = $_GET['q'] ?? '';
            
            if (empty($termino)) {
                http_response_code(400);
                echo json_encode(['error' => 'Parámetro de búsqueda requerido']);
                exit;
            }
            
            $resultados = $guardaModel->buscarAnimal($termino);
            echo json_encode($resultados);
            break;

        case 'estadisticas':
            // Obtener estadísticas del guarda
            $estadisticas = $guardaModel->getEstadisticas($nombreEmpleado);
            echo json_encode($estadisticas);
            break;

        case 'jaulas':
            // Obtener mis jaulas
            $jaulas = $guardaModel->getMisJaulas($nombreEmpleado);
            echo json_encode($jaulas);
            break;

        case 'observacion':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // VALIDACIÓN CSRF PARA API
            // El cliente debe enviar el header 'X-CSRF-Token'
            $headers = getallheaders();
            $token = $headers['X-Csrf-Token'] ?? ($input['csrf_token'] ?? '');
            
            if (!SessionManager::verifyCsrfToken($token)) {
                http_response_code(403);
                echo json_encode(['error' => 'Token de seguridad inválido']);
                exit;
            }

            $numIdentif = $input['numIdentif'] ?? null;
            $observacion = $input['observacion'] ?? '';
            
            if (!$numIdentif || empty($observacion)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                exit;
            }
            // Sanitización básica de entrada antes de guardar (aunque PDO ayuda)
            $observacion = htmlspecialchars(strip_tags($observacion));
            
            $resultado = $guardaModel->registrarObservacion($numIdentif, $nombreEmpleado, $observacion);
            echo json_encode($resultado);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
}
?>