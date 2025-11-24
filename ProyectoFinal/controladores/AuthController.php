<?php
// controladores/AuthController.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->mostrarLogin();
        }

        // Validación CSRF
        if (!isset($_POST['csrf_token']) || !SessionManager::verifyCsrfToken($_POST['csrf_token'])) {
            SessionManager::setFlash('error', 'Error de validación de seguridad (CSRF). Recargue la página.');
            return $this->mostrarLogin();
        }

        $nombreEmpleado = trim($_POST['nombreEmpleado'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($nombreEmpleado) || empty($contrasena)) {
            SessionManager::setFlash('error', 'Credenciales requeridas');
            return $this->mostrarLogin();
        }

        $usuario = $this->userModel->authenticate($nombreEmpleado, $contrasena);

        if (!$usuario) {
            SessionManager::setFlash('error', 'Usuario o contraseña incorrectos');
            return $this->mostrarLogin();
        }

        $rolesDisponibles = $usuario['roles_disponibles'];

        if (empty($rolesDisponibles)) {
            SessionManager::setFlash('error', 'Sin roles asignados.');
            return $this->mostrarLogin();
        }

        if (count($rolesDisponibles) === 1) {
            $rol = $rolesDisponibles[0];
            $this->iniciarSesionConRol($usuario, $rol);
            $this->userModel->registrarAcceso($nombreEmpleado, 'LOGIN', $rol, 'Login automático');
            return $this->redirigirSegunRol($rol);
        }

        $_SESSION['temp_usuario'] = $usuario;
        return $this->mostrarSelectorRoles($rolesDisponibles);
    }

    public function seleccionarRol() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dawb/ProyectoFinal/public/index.php');
            exit;
        }

        if (!isset($_SESSION['temp_usuario'])) {
            header('Location: /dawb/ProyectoFinal/public/index.php');
            exit;
        }

        $usuario = $_SESSION['temp_usuario'];
        $rolSeleccionado = $_POST['rol'] ?? '';

        if (!in_array($rolSeleccionado, $usuario['roles_disponibles'])) {
            SessionManager::setFlash('error', 'Rol inválido');
            return $this->mostrarSelectorRoles($usuario['roles_disponibles']);
        }

        $this->iniciarSesionConRol($usuario, $rolSeleccionado);
        $this->userModel->registrarAcceso($usuario['nombreEmpleado'], 'LOGIN', $rolSeleccionado, 'Rol seleccionado');
        unset($_SESSION['temp_usuario']);

        return $this->redirigirSegunRol($rolSeleccionado);
    }

    // ✅ MÉTODO CORREGIDO
    public function cambiarRol() {
        if (!SessionManager::isLoggedIn()) {
            header('Location: /dawb/ProyectoFinal/public/index.php');
            exit;
        }

        $nombreEmpleado = SessionManager::getNombreEmpleado();
        $usuario = $this->userModel->findByUsername($nombreEmpleado);

        if (!$usuario) {
            SessionManager::logout();
            header('Location: /dawb/ProyectoFinal/public/index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoRol = $_POST['rol'] ?? '';
            if (in_array($nuevoRol, $usuario['roles_disponibles'])) {
                // FIX: Reconstruir datos para actualizar sesión
                $datosAdicionales = [
                    'nombre_completo' => $usuario['nombre_completo'],
                    'jaulas' => [],
                    'camino' => null
                ];
                
                if ($nuevoRol === 'GUARDA') {
                    $datosAdicionales['jaulas'] = $usuario['jaulas'] ?? [];
                }
                if ($nuevoRol === 'SUPERVISOR') {
                    $datosAdicionales['camino'] = $usuario['camino'] ?? null;
                }
                
                // Reutilizar login() para actualizar sesión completa
                SessionManager::login($nombreEmpleado, $nuevoRol, $datosAdicionales);
                
                // Registrar en auditoría
                $this->userModel->registrarAcceso($nombreEmpleado, 'CAMBIO_ROL', $nuevoRol, "Cambio de rol a $nuevoRol");
                
                SessionManager::setFlash('success', 'Rol cambiado exitosamente a ' . $nuevoRol);
                $this->redirigirSegunRol($nuevoRol);
            } else {
                SessionManager::setFlash('error', 'Rol no válido');
            }
        }
        
        // Si no es POST o falla, redirigir al dashboard actual
        $this->redirigirSegunRol(SessionManager::getRol());
    }

    public function logout() {
        $nombreEmpleado = SessionManager::getNombreEmpleado();
        $rol = SessionManager::getRol();
        if ($nombreEmpleado) {
            $this->userModel->registrarAcceso($nombreEmpleado, 'LOGOUT', $rol);
        }
        SessionManager::logout();
        header('Location: /dawb/ProyectoFinal/public/index.php');
        exit;
    }

    private function iniciarSesionConRol($usuario, $rol) {
        $datosAdicionales = [
            'nombre_completo' => $usuario['nombre_completo'],
            'jaulas' => $usuario['jaulas'] ?? [],
            'camino' => $usuario['camino'] ?? null
        ];
        SessionManager::login($usuario['nombreEmpleado'], $rol, $datosAdicionales);
    }

    private function redirigirSegunRol($rol) {
        switch ($rol) {
            case 'GUARDA': header('Location: /dawb/ProyectoFinal/vistas/guarda/dashboard.php'); break;
            case 'SUPERVISOR': header('Location: /dawb/ProyectoFinal/vistas/supervisor/dashboard.php'); break;
            case 'ADMIN': header('Location: /dawb/ProyectoFinal/vistas/admin/dashboard.php'); break;
            default: header('Location: /dawb/ProyectoFinal/public/index.php');
        }
        exit;
    }

    private function mostrarLogin() { require __DIR__ . '/../vistas/auth/login.php'; }
    private function mostrarSelectorRoles($roles) { require __DIR__ . '/../vistas/auth/select_rol.php'; }
    
    public function verificarSesion() {
        if (SessionManager::isLoggedIn()) {
            $this->redirigirSegunRol(SessionManager::getRol());
        }
    }
}

if (isset($_GET['action'])) {
    $c = new AuthController();
    switch ($_GET['action']) {
        case 'login': $c->login(); break;
        case 'logout': $c->logout(); break;
        case 'seleccionar_rol': $c->seleccionarRol(); break;
        case 'cambiar_rol': $c->cambiarRol(); break;
        default: $c->verificarSesion(); $c->login();
    }
} else {
    (new AuthController())->verificarSesion();
}
?>