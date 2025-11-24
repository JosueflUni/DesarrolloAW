<?php
// models/Supervisor.php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo para operaciones de Supervisores
 * ACTUALIZADO: Compatible con estructura de BD normalizada
 */
class Supervisor {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Obtener información del camino asignado
     */
    public function getMiCamino($nombreEmpleado) {
        try {
            // CORRECCIÓN: Tablas Supervisores, Caminos, Jaulas, Animales, Guardas
            $query = "SELECT 
                        c.numCamino,
                        c.nombre AS nombre_camino,
                        c.largo,
                        COUNT(DISTINCT j.numJaula) AS total_jaulas,
                        COUNT(DISTINCT a.numIdentif) AS total_animales,
                        COUNT(DISTINCT g.nombreEmpleado) AS total_guardas
                      FROM Supervisores s
                      INNER JOIN Caminos c ON s.numCamino = c.numCamino
                      LEFT JOIN Jaulas j ON c.numCamino = j.numCamino
                      LEFT JOIN Animales a ON j.numJaula = a.numJaula
                      LEFT JOIN Guardas g ON j.numJaula = g.numJaula
                      WHERE s.nombreEmpleado = :nombreEmpleado
                      GROUP BY c.numCamino, c.nombre, c.largo";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getMiCamino: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todas las jaulas del camino con estado de ocupación
     */
    public function getJaulasCamino($nombreEmpleado) {
        try {
            $query = "SELECT 
                        j.numJaula,
                        j.nombre AS nombre_jaula,
                        j.tamano,
                        vjc.total_animales,
                        vjc.guardas_asignados,
                        CASE 
                            WHEN vjc.total_animales = 0 THEN 'DISPONIBLE'
                            WHEN vjc.total_animales < 5 THEN 'OCUPADA'
                            ELSE 'LLENA'
                        END AS estado_ocupacion,
                        CASE 
                            WHEN vjc.guardas_asignados IS NULL THEN 'SIN_GUARDA'
                            ELSE 'CON_GUARDA'
                        END AS estado_personal
                      FROM Supervisores s
                      INNER JOIN Jaulas j ON s.numCamino = j.numCamino
                      LEFT JOIN VistaJaulasCompletas vjc ON j.numJaula = vjc.numJaula
                      WHERE s.nombreEmpleado = :nombreEmpleado
                      ORDER BY j.numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getJaulasCamino: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener personal (guardas) del camino
     */
    public function getPersonalCamino($nombreEmpleado) {
        try {
            $query = "SELECT DISTINCT
                        e.nombreEmpleado,
                        e.nombre,
                        e.apellido,
                        CONCAT(e.nombre, ' ', e.apellido) AS nombre_completo,
                        GROUP_CONCAT(DISTINCT j.numJaula ORDER BY j.numJaula SEPARATOR ', ') AS jaulas_asignadas,
                        COUNT(DISTINCT j.numJaula) AS total_jaulas,
                        COUNT(DISTINCT a.numIdentif) AS total_animales_cargo
                      FROM Supervisores s
                      INNER JOIN Jaulas j ON s.numCamino = j.numCamino
                      INNER JOIN Guardas g ON j.numJaula = g.numJaula
                      INNER JOIN Empleados e ON g.nombreEmpleado = e.nombreEmpleado
                      LEFT JOIN Animales a ON j.numJaula = a.numJaula
                      WHERE s.nombreEmpleado = :nombreEmpleado
                      GROUP BY e.nombreEmpleado, e.nombre, e.apellido
                      ORDER BY e.apellido, e.nombre";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getPersonalCamino: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas detalladas del camino
     */
    public function getEstadisticasCamino($nombreEmpleado) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT j.numJaula) AS total_jaulas,
                        COUNT(DISTINCT CASE WHEN a.numIdentif IS NULL THEN j.numJaula END) AS jaulas_vacias,
                        COUNT(DISTINCT CASE WHEN a.numIdentif IS NOT NULL THEN j.numJaula END) AS jaulas_ocupadas,
                        COUNT(DISTINCT a.numIdentif) AS total_animales,
                        COUNT(DISTINCT g.nombreEmpleado) AS total_guardas,
                        COUNT(DISTINCT CASE 
                            WHEN vaa.nivel_alerta = 'CRITICO' THEN a.numIdentif 
                        END) AS animales_criticos,
                        SUM(j.tamano) AS capacidad_total
                      FROM Supervisores s
                      INNER JOIN Jaulas j ON s.numCamino = j.numCamino
                      LEFT JOIN Animales a ON j.numJaula = a.numJaula
                      LEFT JOIN Guardas g ON j.numJaula = g.numJaula
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE s.nombreEmpleado = :nombreEmpleado";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getEstadisticasCamino: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener detalle de una jaula específica
     */
    public function getDetalleJaula($numJaula, $nombreEmpleado) {
        try {
            if (!$this->verificarAccesoJaula($nombreEmpleado, $numJaula)) {
                return ['error' => 'Acceso denegado'];
            }

            $query = "SELECT 
                        j.numJaula,
                        j.nombre AS nombre_jaula,
                        j.tamano,
                        j.numCamino,
                        c.nombre AS nombre_camino,
                        vjc.total_animales,
                        vjc.guardas_asignados
                      FROM Jaulas j
                      LEFT JOIN Caminos c ON j.numCamino = c.numCamino
                      LEFT JOIN VistaJaulasCompletas vjc ON j.numJaula = vjc.numJaula
                      WHERE j.numJaula = :numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            $jaula = $stmt->fetch();
            
            if ($jaula) {
                $jaula['animales'] = $this->getAnimalesJaula($numJaula);
            }
            
            return $jaula;
        } catch (PDOException $e) {
            error_log("Error en getDetalleJaula: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener animales de una jaula (Método privado auxiliar)
     */
    private function getAnimalesJaula($numJaula) {
        try {
            // CORRECCIÓN: nombre_cientifico
            $query = "SELECT 
                        a.numIdentif,
                        a.nombre AS nombre_animal,
                        a.sexo,
                        a.nombre_cientifico, 
                        vaa.nivel_alerta
                      FROM Animales a
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE a.numJaula = :numJaula
                      ORDER BY a.nombre";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAnimalesJaula: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener resumen de alertas médicas del camino
     */
    public function getAlertasMedicas($nombreEmpleado) {
        try {
            $query = "SELECT 
                        vaa.nivel_alerta,
                        COUNT(*) AS total,
                        GROUP_CONCAT(CONCAT(vaa.nombre_animal, ' (', j.nombre, ')') 
                            ORDER BY vaa.nombre_animal 
                            SEPARATOR '; ') AS detalles
                      FROM Supervisores s
                      INNER JOIN Jaulas j ON s.numCamino = j.numCamino
                      INNER JOIN VistaAnimalesConAlertas vaa ON j.numJaula = vaa.numJaula
                      WHERE s.nombreEmpleado = :nombreEmpleado
                      AND vaa.nivel_alerta IN ('CRITICO', 'RECIENTE')
                      GROUP BY vaa.nivel_alerta
                      ORDER BY 
                        CASE vaa.nivel_alerta
                            WHEN 'CRITICO' THEN 1
                            WHEN 'RECIENTE' THEN 2
                        END";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAlertasMedicas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener distribución de especies en el camino
     */
    public function getDistribucionEspecies($nombreEmpleado) {
        try {
            // CORRECCIÓN: nombre_cientifico
            $query = "SELECT 
                        a.nombre_cientifico AS especie,
                        COUNT(*) AS cantidad,
                        GROUP_CONCAT(DISTINCT j.nombre ORDER BY j.nombre SEPARATOR ', ') AS jaulas
                      FROM Supervisores s
                      INNER JOIN Jaulas j ON s.numCamino = j.numCamino
                      INNER JOIN Animales a ON j.numJaula = a.numJaula
                      WHERE s.nombreEmpleado = :nombreEmpleado
                      GROUP BY a.nombre_cientifico
                      ORDER BY cantidad DESC, especie
                      LIMIT 10";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getDistribucionEspecies: " . $e->getMessage());
            return [];
        }
    }

    private function verificarAccesoJaula($nombreEmpleado, $numJaula) {
        try {
            $query = "SELECT COUNT(*) 
                      FROM Supervisores s
                      INNER JOIN Jaulas j ON s.numCamino = j.numCamino
                      WHERE s.nombreEmpleado = :nombreEmpleado 
                      AND j.numJaula = :numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'nombreEmpleado' => $nombreEmpleado,
                'numJaula' => $numJaula
            ]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function generarReporte($nombreEmpleado, $fechaInicio, $fechaFin) {
        try {
            return [
                'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
                'camino' => $this->getMiCamino($nombreEmpleado),
                'estadisticas' => $this->getEstadisticasCamino($nombreEmpleado),
                'personal' => $this->getPersonalCamino($nombreEmpleado),
                'alertas' => $this->getAlertasMedicas($nombreEmpleado)
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
?>