<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo de Usuario/Empleado
 * Maneja autenticación y determinación de roles
 */
class User {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Autenticar usuario y determinar roles dinámicamente
     * 
     * @param string $nombreEmpleado
     * @param string $contrasena
     * @return array|false Información del usuario o false si falla
     */
    public function authenticate($nombreEmpleado, $contrasena) {
        try {
            // Consultar usando la vista de roles
            $query = "SELECT 
                        nombreEmpleado,
                        nombre,
                        apellido,
                        contrasena,
                        rol_base,
                        rol_calculado,
                        jaula_asignada,
                        camino_asignado
                      FROM VistaRolesEmpleados
                      WHERE nombreEmpleado = :nombreEmpleado 
                      AND activo = TRUE";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                return false;
            }
            
            // Verificar contraseña
            if (!password_verify($contrasena, $usuario['contrasena'])) {
                // Registrar intento fallido
                $this->registrarAcceso($nombreEmpleado, 'ACCESO_DENEGADO', null, 'Contraseña incorrecta');
                return false;
            }
            
            // Preparar información del usuario
            $userInfo = [
                'nombreEmpleado' => $usuario['nombreEmpleado'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido'],
                'rol_base' => $usuario['rol_base'],
                'rol_calculado' => $usuario['rol_calculado']
            ];
            
            // Determinar roles disponibles
            $rolesDisponibles = $this->determinarRolesDisponibles($usuario);
            $userInfo['roles_disponibles'] = $rolesDisponibles;
            
            // Obtener datos adicionales según el rol
            if (in_array('GUARDA', $rolesDisponibles)) {
                $userInfo['jaulas'] = $this->obtenerJaulasGuarda($nombreEmpleado);
            }
            
            if (in_array('SUPERVISOR', $rolesDisponibles)) {
                $userInfo['camino'] = $this->obtenerCaminoSupervisor($nombreEmpleado);
            }
            
            return $userInfo;
            
        } catch (PDOException $e) {
            error_log("Error en authenticate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Determinar qué roles tiene disponibles el usuario
     */
    private function determinarRolesDisponibles($usuario) {
        $roles = [];
        
        switch ($usuario['rol_calculado']) {
            case 'ADMIN':
                $roles = ['ADMIN', 'GUARDA', 'SUPERVISOR'];
                break;
            case 'AMBOS':
                $roles = ['GUARDA', 'SUPERVISOR'];
                break;
            case 'GUARDA':
                $roles = ['GUARDA'];
                break;
            case 'SUPERVISOR':
                $roles = ['SUPERVISOR'];
                break;
        }
        
        return $roles;
    }

    /**
     * Obtener las jaulas asignadas a un guarda
     */
    private function obtenerJaulasGuarda($nombreEmpleado) {
        try {
            $query = "SELECT DISTINCT numJaula 
                      FROM LosGuardas 
                      WHERE nombreEmpleado = :nombreEmpleado";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error en obtenerJaulasGuarda: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener el camino asignado a un supervisor
     */
    private function obtenerCaminoSupervisor($nombreEmpleado) {
        try {
            $query = "SELECT numCamino 
                      FROM LosSupervisores 
                      WHERE nombreEmpleado = :nombreEmpleado 
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en obtenerCaminoSupervisor: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registrar acceso en auditoría
     */
    public function registrarAcceso($nombreEmpleado, $accion, $rol = null, $detalles = '') {
        try {
            $query = "CALL RegistrarAcceso(:nombreEmpleado, :accion, :rol, :ip, :detalles)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'nombreEmpleado' => $nombreEmpleado,
                'accion' => $accion,
                'rol' => $rol,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'detalles' => $detalles
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en registrarAcceso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información completa del usuario
     */
    public function getUsuarioInfo($nombreEmpleado) {
        try {
            $query = "SELECT 
                        nombreEmpleado,
                        nombre,
                        apellido,
                        rol_base,
                        rol_calculado,
                        activo,
                        ultimo_acceso
                      FROM VistaRolesEmpleados
                      WHERE nombreEmpleado = :nombreEmpleado";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getUsuarioInfo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarContrasena($nombreEmpleado, $contrasenaActual, $contrasenaNueva) {
        try {
            // Verificar contraseña actual
            $query = "SELECT contrasena FROM LosEmpleados WHERE nombreEmpleado = :nombreEmpleado";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            $usuario = $stmt->fetch();
            
            if (!$usuario || !password_verify($contrasenaActual, $usuario['contrasena'])) {
                return false;
            }
            
            // Actualizar contraseña
            $hashNueva = password_hash($contrasenaNueva, PASSWORD_DEFAULT);
            $query = "UPDATE LosEmpleados 
                      SET contrasena = :contrasena 
                      WHERE nombreEmpleado = :nombreEmpleado";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'contrasena' => $hashNueva,
                'nombreEmpleado' => $nombreEmpleado
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en cambiarContrasena: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un usuario puede acceder a un recurso específico
     */
    public function verificarPermiso($nombreEmpleado, $tipoRecurso, $idRecurso) {
        try {
            switch ($tipoRecurso) {
                case 'jaula':
                    $query = "SELECT COUNT(*) FROM LosGuardas 
                              WHERE nombreEmpleado = :nombreEmpleado 
                              AND numJaula = :idRecurso";
                    break;
                
                case 'camino':
                    $query = "SELECT COUNT(*) FROM LosSupervisores 
                              WHERE nombreEmpleado = :nombreEmpleado 
                              AND numCamino = :idRecurso";
                    break;
                
                default:
                    return false;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'nombreEmpleado' => $nombreEmpleado,
                'idRecurso' => $idRecurso
            ]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en verificarPermiso: " . $e->getMessage());
            return false;
        }
    }
}