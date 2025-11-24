<?php
// controladores/GuardaController.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Guarda.php';

/**
 * Controlador para operaciones del Guarda
 */
class GuardaController {
    private $guardaModel;
    private $nombreEmpleado;

    public function __construct() {
        // Verificar autenticación
        if (!SessionManager::isLoggedIn() || SessionManager::getRol() !== 'GUARDA') {
            header('Location: /dawb/ProyectoFinal/public/index.php');
            exit;
        }

        $this->guardaModel = new Guarda();
        $this->nombreEmpleado = SessionManager::getNombreEmpleado();
    }

    /**
     * Mostrar dashboard principal
     */
    public function dashboard() {
        $data = [
            'nombreEmpleado' => $this->nombreEmpleado,
            'nombreCompleto' => SessionManager::getSessionInfo()['nombre_completo'],
            'misJaulas' => $this->guardaModel->getMisJaulas($this->nombreEmpleado),
            'estadisticas' => $this->guardaModel->getEstadisticas($this->nombreEmpleado)
        ];

        require __DIR__ . '/../vistas/guarda/dashboard.php';
    }

    /**
     * Obtener animales de una jaula (AJAX)
     */
    public function getAnimalesJaula() {
        header('Content-Type: application/json');
        
        $numJaula = $_GET['jaula'] ?? null;
        
        if (!$numJaula) {
            http_response_code(400);
            echo json_encode(['error' => 'Número de jaula requerido']);
            return;
        }

        $animales = $this->guardaModel->getAnimalesJaula($numJaula, $this->nombreEmpleado);
        echo json_encode($animales);
    }

    /**
     * Obtener detalle de un animal (AJAX)
     */
    public function getDetalleAnimal() {
        header('Content-Type: application/json');
        
        $numIdentif = $_GET['id'] ?? null;
        
        if (!$numIdentif) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de animal requerido']);
            return;
        }

        $detalle = $this->guardaModel->getDetalleAnimal($numIdentif, $this->nombreEmpleado);
        
        if (!$detalle) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado']);
            return;
        }

        echo json_encode($detalle);
    }

    /**
     * Buscar animal (AJAX)
     */
    public function buscarAnimal() {
        header('Content-Type: application/json');
        
        $termino = $_GET['q'] ?? '';
        
        if (empty($termino)) {
            echo json_encode([]);
            return;
        }

        $resultados = $this->guardaModel->buscarAnimal($termino);
        echo json_encode($resultados);
    }

    /**
     * Registrar observación de un animal
     */
    public function registrarObservacion() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $numIdentif = $input['numIdentif'] ?? null;
        $observacion = $input['observacion'] ?? '';

        if (!$numIdentif || empty($observacion)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }

        $resultado = $this->guardaModel->registrarObservacion(
            $numIdentif, 
            $this->nombreEmpleado, 
            $observacion
        );

        echo json_encode($resultado);
    }
}

// Manejo de rutas
if (basename($_SERVER['PHP_SELF']) === 'GuardaController.php') {
    $controller = new GuardaController();
    $action = $_GET['action'] ?? 'dashboard';

    switch ($action) {
        case 'dashboard':
            $controller->dashboard();
            break;
        case 'animales':
            $controller->getAnimalesJaula();
            break;
        case 'detalle':
            $controller->getDetalleAnimal();
            break;
        case 'buscar':
            $controller->buscarAnimal();
            break;
        case 'observacion':
            $controller->registrarObservacion();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Acción no encontrada']);
    }
}