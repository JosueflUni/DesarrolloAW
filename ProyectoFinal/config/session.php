<?php
// config/session.php

// Detección de HTTPS
$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

// Configuración de seguridad de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $isSecure ? 1 : 0); // Dinámico
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SessionManager {
    
    public static function login($nombreEmpleado, $rol, $userData = []) {
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['nombreEmpleado'] = $nombreEmpleado;
        $_SESSION['rol_activo'] = $rol;
        $_SESSION['nombre_completo'] = $userData['nombre_completo'] ?? '';
        $_SESSION['inicio_sesion'] = time();
        $_SESSION['ultima_actividad'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Generar nuevo token CSRF al login
        self::generateCsrfToken();

        if ($rol === 'GUARDA' && isset($userData['jaulas'])) {
            $_SESSION['jaulas_asignadas'] = $userData['jaulas'];
        } elseif ($rol === 'SUPERVISOR' && isset($userData['camino'])) {
            $_SESSION['camino_asignado'] = $userData['camino'];
        }
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function validateSession() {
        if (!self::isLoggedIn()) return false;
        
        // Timeout (30 min)
        if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > 1800)) {
            self::logout();
            return false;
        }
        
        // Validación básica de Fingerprint (IP + User Agent)
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
             self::logout();
             return false;
        }

        $_SESSION['ultima_actividad'] = time();
        return true;
    }
    
    // --- Lógica CSRF ---
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    // -------------------

    public static function getRol() { return $_SESSION['rol_activo'] ?? null; }
    public static function getNombreEmpleado() { return $_SESSION['nombreEmpleado'] ?? null; }
    
    public static function logout() {
        $nombreEmpleado = $_SESSION['nombreEmpleado'] ?? null;
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
        return $nombreEmpleado;
    }
    
    public static function getSessionInfo() {
        if (!self::isLoggedIn()) return null;
        return [
            'nombreEmpleado' => $_SESSION['nombreEmpleado'],
            'nombre_completo' => $_SESSION['nombre_completo'] ?? '',
            'rol_activo' => $_SESSION['rol_activo'],
            'csrf_token' => $_SESSION['csrf_token'] ?? '' // Exponer token para Vistas
        ];
    }
    
    public static function setFlash($tipo, $mensaje) {
        $_SESSION['flash_message'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }
    
    public static function getFlash() {
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $flash;
        }
        return null;
    }
}

function requireAuth($rolesPermitidos = []) {
    if (!SessionManager::validateSession()) {
        header('Location: /dawb/ProyectoFinal/public/index.php');
        exit;
    }
    if (!empty($rolesPermitidos) && !in_array(SessionManager::getRol(), $rolesPermitidos)) {
        SessionManager::setFlash('error', 'Acceso no autorizado.');
        header('Location: /dawb/ProyectoFinal/public/index.php');
        exit;
    }
}
?>