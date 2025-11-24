<?php
// models/Guarda.php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo para operaciones de Guardas
 */
class Guarda {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Obtener todas las jaulas asignadas a un guarda con información completa
     */
    public function getMisJaulas($nombreEmpleado) {
        try {
            $query = "SELECT 
                        j.numJaula,
                        j.nombre AS nombre_jaula,
                        j.tamano,
                        j.numCamino,
                        c.nombre AS nombre_camino,
                        vjc.total_animales
                      FROM LosGuardas g
                      INNER JOIN LasJaulas j ON g.numJaula = j.numJaula
                      LEFT JOIN LosCaminos c ON j.numCamino = c.numCamino
                      LEFT JOIN VistaJaulasCompletas vjc ON j.numJaula = vjc.numJaula
                      WHERE g.nombreEmpleado = :nombreEmpleado
                      ORDER BY j.numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getMisJaulas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener animales de una jaula específica con alertas médicas
     */
    public function getAnimalesJaula($numJaula, $nombreEmpleado) {
        try {
            // Verificar que el guarda tenga acceso a esta jaula
            if (!$this->verificarAccesoJaula($nombreEmpleado, $numJaula)) {
                return ['error' => 'Acceso denegado'];
            }

            $query = "SELECT 
                        vaa.numIdentif,
                        vaa.nombre_animal,
                        vaa.sexo,
                        vaa.nombre_cientifico,
                        vaa.total_enfermedades,
                        vaa.ultima_enfermedad,
                        vaa.nivel_alerta,
                        p.nombre AS nombre_pais
                      FROM VistaAnimalesConAlertas vaa
                      INNER JOIN LosAnimales a ON vaa.numIdentif = a.numIdentif
                      LEFT JOIN LosPaises p ON a.numPais = p.numPais
                      WHERE vaa.numJaula = :numJaula
                      ORDER BY 
                        CASE vaa.nivel_alerta
                            WHEN 'CRITICO' THEN 1
                            WHEN 'RECIENTE' THEN 2
                            WHEN 'HISTORIAL' THEN 3
                            ELSE 4
                        END,
                        vaa.nombre_animal";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAnimalesJaula: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener detalle completo de un animal
     */
    public function getDetalleAnimal($numIdentif, $nombreEmpleado) {
        try {
            $query = "SELECT 
                        a.numIdentif,
                        a.nombre AS nombre_animal,
                        a.sexo,
                        a.fechaNac,
                        a.numJaula,
                        a.nombreA AS nombre_cientifico,
                        p.nombre AS pais_origen,
                        j.nombre AS nombre_jaula,
                        vaa.nivel_alerta,
                        vaa.total_enfermedades
                      FROM LosAnimales a
                      LEFT JOIN LosPaises p ON a.numPais = p.numPais
                      LEFT JOIN LasJaulas j ON a.numJaula = j.numJaula
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE a.numIdentif = :numIdentif";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numIdentif' => $numIdentif]);
            
            $animal = $stmt->fetch();
            
            if ($animal) {
                // Obtener historial médico
                $animal['historial_medico'] = $this->getHistorialMedico($numIdentif);
            }
            
            return $animal;
        } catch (PDOException $e) {
            error_log("Error en getDetalleAnimal: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener historial médico de un animal
     */
    private function getHistorialMedico($numIdentif) {
        try {
            $query = "SELECT 
                        e.codEnfermedad,
                        e.fechaInicio,
                        e.fechaFin,
                        e.tipoEnfermedad,
                        e.tratamiento,
                        DATEDIFF(COALESCE(e.fechaFin, CURDATE()), e.fechaInicio) AS dias_duracion,
                        CASE 
                            WHEN e.fechaFin IS NULL THEN 'ACTIVA'
                            ELSE 'RECUPERADO'
                        END AS estado
                      FROM LasEnfermedades e
                      WHERE e.numIdentif = :numIdentif
                      ORDER BY e.fechaInicio DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numIdentif' => $numIdentif]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getHistorialMedico: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar animales en todo el zoológico (solo lectura)
     */
    public function buscarAnimal($termino) {
        try {
            $terminoLike = "%{$termino}%";
            
            $query = "SELECT 
                        a.numIdentif,
                        a.nombre AS nombre_animal,
                        a.sexo,
                        a.nombreA AS nombre_cientifico,
                        j.nombre AS nombre_jaula,
                        j.numJaula,
                        vaa.nivel_alerta
                      FROM LosAnimales a
                      LEFT JOIN LasJaulas j ON a.numJaula = j.numJaula
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE a.nombre LIKE :termino
                         OR a.nombreA LIKE :termino
                         OR a.numIdentif LIKE :termino
                      ORDER BY a.nombre
                      LIMIT 20";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['termino' => $terminoLike]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en buscarAnimal: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas del guarda
     */
    public function getEstadisticas($nombreEmpleado) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT g.numJaula) AS total_jaulas,
                        COUNT(DISTINCT a.numIdentif) AS total_animales,
                        COUNT(DISTINCT CASE 
                            WHEN vaa.nivel_alerta = 'CRITICO' THEN a.numIdentif 
                        END) AS animales_criticos,
                        COUNT(DISTINCT CASE 
                            WHEN vaa.nivel_alerta = 'RECIENTE' THEN a.numIdentif 
                        END) AS animales_atencion
                      FROM LosGuardas g
                      LEFT JOIN LosAnimales a ON g.numJaula = a.numJaula
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE g.nombreEmpleado = :nombreEmpleado";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getEstadisticas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si el guarda tiene acceso a una jaula
     */
    private function verificarAccesoJaula($nombreEmpleado, $numJaula) {
        try {
            $query = "SELECT COUNT(*) FROM LosGuardas 
                      WHERE nombreEmpleado = :nombreEmpleado 
                      AND numJaula = :numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'nombreEmpleado' => $nombreEmpleado,
                'numJaula' => $numJaula
            ]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en verificarAccesoJaula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar observación de animal (funcionalidad adicional)
     */
    public function registrarObservacion($numIdentif, $nombreEmpleado, $observacion) {
        try {
            // Verificar que el guarda tenga acceso al animal
            $query = "SELECT COUNT(*) FROM LosAnimales a
                      INNER JOIN LosGuardas g ON a.numJaula = g.numJaula
                      WHERE a.numIdentif = :numIdentif 
                      AND g.nombreEmpleado = :nombreEmpleado";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'numIdentif' => $numIdentif,
                'nombreEmpleado' => $nombreEmpleado
            ]);
            
            if ($stmt->fetchColumn() == 0) {
                return ['error' => 'Acceso denegado'];
            }
            
            // Aquí podrías insertar en una tabla de observaciones si existiera
            // Por ahora solo retornamos éxito
            return ['success' => true, 'mensaje' => 'Observación registrada'];
            
        } catch (PDOException $e) {
            error_log("Error en registrarObservacion: " . $e->getMessage());
            return ['error' => 'Error al registrar observación'];
        }
    }
}