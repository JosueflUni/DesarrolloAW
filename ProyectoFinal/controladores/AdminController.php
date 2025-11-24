<?php
// controladores/AdminController.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

requireAuth(['ADMIN']);

class AdminController {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();
    }

    public function dashboard() {
        header('Location: /dawb/ProyectoFinal/vistas/admin/dashboard.php');
    }

    public function listarUsuarios() {
        try {
            $stmt = $this->db->query("SELECT * FROM Empleados ORDER BY apellido, nombre");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $usuarios = [];
        }
        require __DIR__ . '/../vistas/admin/usuarios.php';
    }

    // --- NUEVAS FUNCIONES ---

    public function formularioNuevoEmpleado() {
        require __DIR__ . '/../vistas/admin/nuevo_empleado.php';
    }

    public function guardarEmpleado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recoger datos
            $datos = [
                'usuario' => $_POST['usuario'] ?? '',
                'nombre' => $_POST['nombre'] ?? '',
                'apellido' => $_POST['apellido'] ?? '',
                'email' => $_POST['email'] ?? '',
                'contrasena' => $_POST['contrasena'] ?? '',
                'rol' => $_POST['rol'] ?? 'GUARDA'
            ];

            // Validación básica
            if (empty($datos['usuario']) || empty($datos['contrasena'])) {
                SessionManager::setFlash('error', 'Usuario y contraseña obligatorios');
                header('Location: AdminController.php?action=nuevo_empleado');
                return;
            }

            // Guardar usando el modelo
            $resultado = $this->userModel->crearEmpleado($datos);

            if (isset($resultado['success'])) {
                SessionManager::setFlash('success', 'Empleado registrado correctamente');
                header('Location: AdminController.php?action=usuarios');
            } else {
                SessionManager::setFlash('error', $resultado['error']);
                header('Location: AdminController.php?action=nuevo_empleado');
            }
        }
    }

    public function infraestructura() {
        // Por ahora, mensaje de construcción
        echo "<h1>Gestión de Infraestructura</h1><p>En construcción...</p><a href='AdminController.php?action=dashboard'>Volver</a>";
    }

    public function reportes() {
        // Por ahora, mensaje de construcción
        echo "<h1>Reportes Globales</h1><p>En construcción...</p><a href='AdminController.php?action=dashboard'>Volver</a>";
    }
}

// Router actualizado
$controller = new AdminController();
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'usuarios':
        $controller->listarUsuarios();
        break;
    case 'nuevo_empleado':       // <--- NUEVA RUTA
        $controller->formularioNuevoEmpleado();
        break;
    case 'guardar_empleado':     // <--- NUEVA RUTA
        $controller->guardarEmpleado();
        break;
    case 'infraestructura':
        $controller->infraestructura();
        break;
    case 'reportes':
        $controller->reportes();
        break;
    default:
        $controller->dashboard();
        break;
}
?>