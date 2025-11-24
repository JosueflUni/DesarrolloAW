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
            $query = "SELECT * FROM VistaRolesEmpleados WHERE nombreEmpleado = :nombreEmpleado AND activo = TRUE";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            $usuario = $stmt->fetch();
            
            if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
                $this->registrarAcceso($nombreEmpleado, 'ACCESO_DENEGADO', null, 'Credenciales inválidas');
                return false;
            }
            
            return $this->procesarDatosUsuario($usuario);
            
        } catch (PDOException $e) {
            error_log("Error Auth: " . $e->getMessage());
            return false;
        }
    }

    // NUEVO MÉTODO: Para recuperar datos sin verificar password (uso interno/cambio de rol)
    public function findByUsername($nombreEmpleado) {
        try {
            $query = "SELECT * FROM VistaRolesEmpleados WHERE nombreEmpleado = :nombreEmpleado AND activo = TRUE";
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

    // Refactorización para no repetir código
    private function procesarDatosUsuario($usuario) {
        $userInfo = [
            'nombreEmpleado' => $usuario['nombreEmpleado'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido'],
            'rol_base' => $usuario['rol_base'],
            'rol_calculado' => $usuario['rol_calculado']
        ];
        
        $rolesDisponibles = $this->determinarRolesDisponibles($usuario);
        $userInfo['roles_disponibles'] = $rolesDisponibles;
        
        if (in_array('GUARDA', $rolesDisponibles)) {
            $userInfo['jaulas'] = $this->obtenerJaulasGuarda($usuario['nombreEmpleado']);
        }
        
        if (in_array('SUPERVISOR', $rolesDisponibles)) {
            $userInfo['camino'] = $this->obtenerCaminoSupervisor($usuario['nombreEmpleado']);
        }
        
        return $userInfo;
    }

    private function determinarRolesDisponibles($usuario) {
        switch ($usuario['rol_calculado']) {
            case 'ADMIN': return ['ADMIN', 'GUARDA', 'SUPERVISOR'];
            case 'AMBOS': return ['GUARDA', 'SUPERVISOR'];
            default: return [$usuario['rol_calculado']];
        }
    }

    private function obtenerJaulasGuarda($nombreEmpleado) {
        $stmt = $this->conn->prepare("SELECT DISTINCT numJaula FROM LosGuardas WHERE nombreEmpleado = :u");
        $stmt->execute(['u' => $nombreEmpleado]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function obtenerCaminoSupervisor($nombreEmpleado) {
        $stmt = $this->conn->prepare("SELECT numCamino FROM LosSupervisores WHERE nombreEmpleado = :u LIMIT 1");
        $stmt->execute(['u' => $nombreEmpleado]);
        return $stmt->fetchColumn();
    }

    public function registrarAcceso($nombreEmpleado, $accion, $rol = null, $detalles = '') {
        try {
            // Nota: Asumiendo que el SP existe. Si falla, no debería romper el flujo del usuario.
            $query = "CALL RegistrarAcceso(:u, :a, :r, :ip, :d)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'u' => $nombreEmpleado, 'a' => $accion, 'r' => $rol,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 'd' => $detalles
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error Auditoría: " . $e->getMessage());
            return false; // Fail silently for user
        }
    }
}
?>