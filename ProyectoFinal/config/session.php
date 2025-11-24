<?php
// config/session.php

/**
 * Configuración de sesiones seguras
 */

// Configuración de seguridad de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 hora

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Clase para manejar sesiones de usuario
 */
class SessionManager {
    
    /**
     * Iniciar sesión de usuario
     */
    public static function login($nombreEmpleado, $rol, $userData = []) {
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);
        
        $_SESSION['logged_in'] = true;
        $_SESSION['nombreEmpleado'] = $nombreEmpleado;
        $_SESSION['rol_activo'] = $rol;
        $_SESSION['nombre_completo'] = $userData['nombre_completo'] ?? '';
        $_SESSION['inicio_sesion'] = time();
        $_SESSION['ultima_actividad'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Datos adicionales según el rol
        if ($rol === 'GUARDA' && isset($userData['jaulas'])) {
            $_SESSION['jaulas_asignadas'] = $userData['jaulas'];
        } elseif ($rol === 'SUPERVISOR' && isset($userData['camino'])) {
            $_SESSION['camino_asignado'] = $userData['camino'];
        }
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Verificar si la sesión sigue siendo válida
     */
    public static function validateSession() {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        // Verificar timeout de inactividad (30 minutos)
        if (isset($_SESSION['ultima_actividad']) && 
            (time() - $_SESSION['ultima_actividad'] > 1800)) {
            self::logout();
            return false;
        }
        
        // Verificar que la IP no haya cambiado (opcional, puede causar problemas con proxies)
        if (isset($_SESSION['ip_address']) && 
            $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            // self::logout(); // Descomentar si quieres validación estricta de IP
            // return false;
        }
        
        // Actualizar última actividad
        $_SESSION['ultima_actividad'] = time();
        
        return true;
    }
    
    /**
     * Obtener el rol activo del usuario
     */
    public static function getRol() {
        return $_SESSION['rol_activo'] ?? null;
    }
    
    /**
     * Obtener el nombre del empleado
     */
    public static function getNombreEmpleado() {
        return $_SESSION['nombreEmpleado'] ?? null;
    }
    
    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function hasRole($rol) {
        return self::getRol() === $rol;
    }
    
    /**
     * Cerrar sesión
     */
    public static function logout() {
        // Guardar datos necesarios antes de destruir
        $nombreEmpleado = $_SESSION['nombreEmpleado'] ?? null;
        
        // Limpiar variables de sesión
        $_SESSION = array();
        
        // Destruir cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir sesión
        session_destroy();
        
        return $nombreEmpleado;
    }
    
    /**
     * Cambiar rol activo (para usuarios con múltiples roles)
     */
    public static function cambiarRol($nuevoRol, $datosAdicionales = []) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $_SESSION['rol_activo'] = $nuevoRol;
        
        // Limpiar datos del rol anterior
        unset($_SESSION['jaulas_asignadas']);
        unset($_SESSION['camino_asignado']);
        
        // Agregar datos del nuevo rol
        if ($nuevoRol === 'GUARDA' && isset($datosAdicionales['jaulas'])) {
            $_SESSION['jaulas_asignadas'] = $datosAdicionales['jaulas'];
        } elseif ($nuevoRol === 'SUPERVISOR' && isset($datosAdicionales['camino'])) {
            $_SESSION['camino_asignado'] = $datosAdicionales['camino'];
        }
        
        return true;
    }
    
    /**
     * Obtener información de la sesión
     */
    public static function getSessionInfo() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'nombreEmpleado' => $_SESSION['nombreEmpleado'],
            'nombre_completo' => $_SESSION['nombre_completo'] ?? '',
            'rol_activo' => $_SESSION['rol_activo'],
            'tiempo_sesion' => time() - ($_SESSION['inicio_sesion'] ?? time()),
            'ultima_actividad' => $_SESSION['ultima_actividad'] ?? time()
        ];
    }
    
    /**
     * Establecer un mensaje flash (mensaje que dura una sola petición)
     */
    public static function setFlash($tipo, $mensaje) {
        $_SESSION['flash_message'] = [
            'tipo' => $tipo, // success, error, warning, info
            'mensaje' => $mensaje
        ];
    }
    
    /**
     * Obtener y eliminar mensaje flash
     */
    public static function getFlash() {
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $flash;
        }
        return null;
    }
}

/**
 * Middleware de autenticación
 * Usar al inicio de páginas protegidas
 */
function requireAuth($rolesPermitidos = []) {
    if (!SessionManager::validateSession()) {
        header('Location: /zoologico/public/index.php');
        exit;
    }
    
    if (!empty($rolesPermitidos) && !in_array(SessionManager::getRol(), $rolesPermitidos)) {
        SessionManager::setFlash('error', 'No tienes permisos para acceder a esta página');
        header('Location: /zoologico/public/index.php');
        exit;
    }
}