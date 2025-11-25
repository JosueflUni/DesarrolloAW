<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function authenticate($nombreEmpleado, $contrasena) {
        try {
            $query = "SELECT * FROM VistaRolesEmpleados WHERE nombreEmpleado = :nombreEmpleado";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            $usuario = $stmt->fetch();
            
            if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
                return false;
            }
            
            return $this->procesarDatosUsuario($usuario);
        } catch (PDOException $e) {
            error_log("Error Auth: " . $e->getMessage());
            return false;
        }
    }

    public function findByUsername($nombreEmpleado) {
        try {
            $query = "SELECT * FROM VistaRolesEmpleados WHERE nombreEmpleado = :nombreEmpleado";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            $usuario = $stmt->fetch();

            if (!$usuario) return false;

            return $this->procesarDatosUsuario($usuario);

        } catch (PDOException $e) {
            error_log("Error findByUsername: " . $e->getMessage());
            return false;
        }
    }

    private function procesarDatosUsuario($usuario) {
        $rolBase = $usuario['rol_base'] ?? $usuario['rol_calculado'] ?? 'INVITADO';

        $userInfo = [
            'nombreEmpleado' => $usuario['nombreEmpleado'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido'],
            'rol_base' => $rolBase, 
            'rol_calculado' => $usuario['rol_calculado'] ?? $rolBase
        ];
        
        $rolesDisponibles = $this->determinarRolesDisponibles($userInfo);
        $userInfo['roles_disponibles'] = $rolesDisponibles;
        
        if (in_array('GUARDA', $rolesDisponibles)) {
            $userInfo['jaulas'] = $this->obtenerJaulasGuarda($usuario['nombreEmpleado']);
        }
        
        if (in_array('SUPERVISOR', $rolesDisponibles)) {
            $userInfo['camino'] = $this->obtenerCaminoSupervisor($usuario['nombreEmpleado']);
        }
        
        return $userInfo;
    }

    // ==========================================
    // CORRECCIÓN LÓGICA DE ROLES AQUÍ
    // ==========================================
    private function determinarRolesDisponibles($usuario) {
        $rol = $usuario['rol_calculado'];
        switch ($rol) {
            // CAMBIO: El Admin ahora solo devuelve 'ADMIN'. 
            // Esto evita que vea pantallas vacías de otros roles.
            case 'ADMIN': return ['ADMIN']; 
            
            // Si tiene ambos roles operativos, sí permitimos elegir
            case 'AMBOS': return ['GUARDA', 'SUPERVISOR'];
            
            default: return [$rol];
        }
    }

    private function obtenerJaulasGuarda($nombreEmpleado) {
        try {
            $stmt = $this->conn->prepare("SELECT DISTINCT numJaula FROM Guardas WHERE nombreEmpleado = :u");
            $stmt->execute(['u' => $nombreEmpleado]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) { return []; }
    }

    private function obtenerCaminoSupervisor($nombreEmpleado) {
        try {
            $stmt = $this->conn->prepare("SELECT numCamino FROM Supervisores WHERE nombreEmpleado = :u LIMIT 1");
            $stmt->execute(['u' => $nombreEmpleado]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) { return null; }
    }

    public function registrarAcceso($nombreEmpleado, $accion, $rol = null, $detalles = '') {
        try {
            $query = "INSERT INTO HistorialAccesos (nombreEmpleado, accion, rol, ip_address, detalles) VALUES (:u, :a, :r, :ip, :d)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'u' => $nombreEmpleado, 'a' => $accion, 'r' => $rol,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 'd' => $detalles
            ]);
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function crearEmpleado($datos) {
        try {
            $check = $this->conn->prepare("SELECT nombreEmpleado FROM Empleados WHERE nombreEmpleado = :u");
            $check->execute(['u' => $datos['usuario']]);
            if ($check->rowCount() > 0) return ['error' => 'El nombre de usuario ya existe'];

            $hash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO Empleados (nombreEmpleado, nombre, apellido, contrasena, email, rol_base, activo) 
                    VALUES (:usuario, :nombre, :apellido, :pass, :email, :rol, 1)";
            
            $this->conn->prepare($sql)->execute([
                'usuario' => $datos['usuario'], 'nombre' => $datos['nombre'], 'apellido' => $datos['apellido'],
                'pass' => $hash, 'email' => $datos['email'], 'rol' => $datos['rol']
            ]);

            return ['success' => true];
        } catch (PDOException $e) { return ['error' => 'Error en BD: ' . $e->getMessage()]; }
    }
}
?>