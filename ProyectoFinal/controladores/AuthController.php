<?php
// controllers/AuthController.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Controlador de Autenticación
 * Maneja login, logout y selección de roles
 */
class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Procesar login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->mostrarLogin();
        }

        // Validar datos de entrada
        $nombreEmpleado = trim($_POST['nombreEmpleado'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($nombreEmpleado) || empty($contrasena)) {
            SessionManager::setFlash('error', 'Por favor ingresa usuario y contraseña');
            return $this->mostrarLogin();
        }

        // Intentar autenticar
        $usuario = $this->userModel->authenticate($nombreEmpleado, $contrasena);

        if (!$usuario) {
            SessionManager::setFlash('error', 'Usuario o contraseña incorrectos');
            return $this->mostrarLogin();
        }

        // Determinar qué hacer según los roles disponibles
        $rolesDisponibles = $usuario['roles_disponibles'];

        if (empty($rolesDisponibles)) {
            SessionManager::setFlash('error', 'Tu cuenta no tiene roles asignados. Contacta al administrador.');
            return $this->mostrarLogin();
        }

        // Si solo tiene un rol, iniciar sesión directamente
        if (count($rolesDisponibles) === 1) {
            $rol = $rolesDisponibles[0];
            $this->iniciarSesionConRol($usuario, $rol);
            $this->userModel->registrarAcceso($nombreEmpleado, 'LOGIN', $rol, 'Login automático');
            return $this->redirigirSegunRol($rol);
        }

        // Si tiene múltiples roles, mostrar selector
        $_SESSION['temp_usuario'] = $usuario;
        return $this->mostrarSelectorRoles($rolesDisponibles);
    }

    /**
     * Seleccionar rol (para usuarios con múltiples roles)
     */
    public function seleccionarRol() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /zoologico/public/index.php');
            exit;
        }

        if (!isset($_SESSION['temp_usuario'])) {
            SessionManager::setFlash('error', 'Sesión expirada. Por favor inicia sesión nuevamente.');
            header('Location: /zoologico/public/index.php');
            exit;
        }

        $usuario = $_SESSION['temp_usuario'];
        $rolSeleccionado = $_POST['rol'] ?? '';

        // Validar que el rol seleccionado esté disponible
        if (!in_array($rolSeleccionado, $usuario['roles_disponibles'])) {
            SessionManager::setFlash('error', 'Rol no válido');
            return $this->mostrarSelectorRoles($usuario['roles_disponibles']);
        }

        // Iniciar sesión con el rol seleccionado
        $this->iniciarSesionConRol($usuario, $rolSeleccionado);
        $this->userModel->registrarAcceso($usuario['nombreEmpleado'], 'LOGIN', $rolSeleccionado, 'Login con selección de rol');
        
        // Limpiar datos temporales
        unset($_SESSION['temp_usuario']);

        return $this->redirigirSegunRol($rolSeleccionado);
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        $nombreEmpleado = SessionManager::getNombreEmpleado();
        $rol = SessionManager::getRol();

        if ($nombreEmpleado) {
            $this->userModel->registrarAcceso($nombreEmpleado, 'LOGOUT', $rol, 'Logout normal');
        }

        SessionManager::logout();
        SessionManager::setFlash('success', 'Has cerrado sesión correctamente');
        
        header('Location: /zoologico/public/index.php');
        exit;
    }

    /**
     * Cambiar de rol (para usuarios con múltiples roles)
     */
    public function cambiarRol() {
        if (!SessionManager::isLoggedIn()) {
            header('Location: /zoologico/public/index.php');
            exit;
        }

        $nombreEmpleado = SessionManager::getNombreEmpleado();
        $usuario = $this->userModel->authenticate($nombreEmpleado, ''); // Obtener info sin validar contraseña
        
        // En producción, necesitarías un método separado para obtener info del usuario
        // Por ahora mostramos el selector de roles
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoRol = $_POST['rol'] ?? '';
            
            // Aquí implementarías la lógica para cambiar el rol
            // Necesitarías verificar que el usuario tenga acceso a ese rol
            
            SessionManager::setFlash('success', 'Rol cambiado correctamente');
            return $this->redirigirSegunRol($nuevoRol);
        }

        // Mostrar formulario de cambio de rol
        require __DIR__ . '/../views/auth/cambiar_rol.php';
    }

    /**
     * Iniciar sesión con un rol específico
     */
    private function iniciarSesionConRol($usuario, $rol) {
        $datosAdicionales = [
            'nombre_completo' => $usuario['nombre_completo']
        ];

        switch ($rol) {
            case 'GUARDA':
                $datosAdicionales['jaulas'] = $usuario['jaulas'] ?? [];
                break;
            case 'SUPERVISOR':
                $datosAdicionales['camino'] = $usuario['camino'] ?? null;
                break;
        }

        SessionManager::login($usuario['nombreEmpleado'], $rol, $datosAdicionales);
    }

    /**
     * Redirigir según el rol
     */
    private function redirigirSegunRol($rol) {
        switch ($rol) {
            case 'GUARDA':
                header('Location: /zoologico/views/guarda/dashboard.php');
                break;
            case 'SUPERVISOR':
                header('Location: /zoologico/views/supervisor/dashboard.php');
                break;
            case 'ADMIN':
                header('Location: /zoologico/views/admin/dashboard.php');
                break;
            default:
                SessionManager::setFlash('error', 'Rol no reconocido');
                header('Location: /zoologico/public/index.php');
        }
        exit;
    }

    /**
     * Mostrar pantalla de login
     */
    private function mostrarLogin() {
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Mostrar selector de roles
     */
    private function mostrarSelectorRoles($roles) {
        require __DIR__ . '/../views/auth/selector_roles.php';
    }

    /**
     * Verificar si hay sesión activa y redirigir
     */
    public function verificarSesion() {
        if (SessionManager::isLoggedIn()) {
            $rol = SessionManager::getRol();
            $this->redirigirSegunRol($rol);
        }
    }
}

// Manejo de rutas del controlador
if (isset($_GET['action'])) {
    $controller = new AuthController();
    
    switch ($_GET['action']) {
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'seleccionar_rol':
            $controller->seleccionarRol();
            break;
        case 'cambiar_rol':
            $controller->cambiarRol();
            break;
        default:
            $controller->verificarSesion();
            $controller->login();
    }
} else {
    $controller = new AuthController();
    $controller->verificarSesion();
}