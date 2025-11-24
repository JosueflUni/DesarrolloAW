<?php
// public/index.php

/**
 * Punto de entrada principal del sistema
 * Redirige al login o dashboard según el estado de sesión
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();

// Si el usuario ya tiene sesión activa, redirigir a su dashboard
if (SessionManager::validateSession()) {
    $rol = SessionManager::getRol();
    
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
            SessionManager::logout();
            header('Location: /zoologico/public/index.php');
    }
    exit;
}

// Si no hay sesión, mostrar el login
$authController->login();