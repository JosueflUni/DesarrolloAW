<?php
// models/Supervisor.php

require_once __DIR__ . '/../config/database.php';

class Supervisor {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function getMisCaminos($nombreEmpleado) {
        try {
            $query = "SELECT 
                        c.numCamino,
                        c.nombre AS nombre_camino,
                        c.largo,
                        COUNT(DISTINCT j.numJaula) AS total_jaulas,
                        (SELECT COUNT(*) 
                         FROM Animales a 
                         INNER JOIN Jaulas j2 ON a.numJaula = j2.numJaula 
                         WHERE j2.numCamino = c.numCamino) AS total_animales,
                        (SELECT COUNT(DISTINCT g.nombreEmpleado) 
                         FROM Guardas g 
                         INNER JOIN Jaulas j3 ON g.numJaula = j3.numJaula 
                         WHERE j3.numCamino = c.numCamino) AS total_guardas
                      FROM Supervisores s
                      INNER JOIN Caminos c ON s.numCamino = c.numCamino
                      LEFT JOIN Jaulas j ON c.numCamino = j.numCamino
                      WHERE s.nombreEmpleado = :nombreEmpleado
                      GROUP BY c.numCamino, c.nombre, c.largo";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombreEmpleado' => $nombreEmpleado]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

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
            return [];
        }
    }

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
            return [];
        }
    }

    /**
     * Obtener estadísticas detalladas del camino
     * CORRECCIÓN: Se usa BINARY para evitar error 1267 de Collation
     */
    public function getEstadisticasCamino($caminoId) {
        try {
            if (!$caminoId) return null;

            $query = "SELECT 
                        (SELECT COUNT(*) FROM Jaulas WHERE numCamino = :cid) AS total_jaulas,
                        (SELECT COUNT(*) FROM Jaulas j 
                         LEFT JOIN Animales a ON j.numJaula = a.numJaula 
                         WHERE j.numCamino = :cid AND a.numIdentif IS NULL) AS jaulas_vacias,
                        (SELECT COUNT(DISTINCT j.numJaula) 
                         FROM Animales a 
                         INNER JOIN Jaulas j ON a.numJaula = j.numJaula 
                         WHERE j.numCamino = :cid) AS jaulas_ocupadas,
                        (SELECT COUNT(DISTINCT g.nombreEmpleado) 
                         FROM Guardas g
                         INNER JOIN Jaulas j ON g.numJaula = j.numJaula 
                         WHERE j.numCamino = :cid) AS total_guardas,
                        (SELECT COUNT(DISTINCT a.numIdentif)
                         FROM Animales a
                         INNER JOIN Jaulas j ON a.numJaula = j.numJaula
                         INNER JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                         WHERE j.numCamino = :cid AND BINARY vaa.nivel_alerta = 'CRITICO') AS animales_criticos";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['cid' => $caminoId]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Estadísticas: " . $e->getMessage());
            return [
                'total_jaulas' => 0, 'jaulas_vacias' => 0, 
                'jaulas_ocupadas' => 0, 'total_guardas' => 0, 
                'animales_criticos' => 0
            ];
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
            return null;
        }
    }

    private function getAnimalesJaula($numJaula) {
        try {
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
            return [];
        }
    }

    /**
     * Obtener resumen de alertas médicas del camino
     * CORRECCIÓN: Uso de BINARY para compatibilidad de collations
     */
    public function getAlertasMedicas($caminoId) {
        try {
            if (!$caminoId) return [];

            $query = "SELECT 
                        vaa.nivel_alerta,
                        COUNT(*) AS total,
                        GROUP_CONCAT(CONCAT(vaa.nombre_animal, ' (', j.nombre, ')') 
                            ORDER BY vaa.nombre_animal 
                            SEPARATOR '; ') AS detalles
                      FROM Jaulas j
                      INNER JOIN VistaAnimalesConAlertas vaa ON j.numJaula = vaa.numJaula
                      WHERE j.numCamino = :cid 
                      AND BINARY vaa.nivel_alerta IN ('CRITICO', 'RECIENTE')
                      GROUP BY vaa.nivel_alerta
                      ORDER BY 
                        CASE vaa.nivel_alerta
                            WHEN 'CRITICO' THEN 1
                            WHEN 'RECIENTE' THEN 2
                        END";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['cid' => $caminoId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error Alertas: " . $e->getMessage());
            return [];
        }
    }

    public function getDistribucionEspecies($nombreEmpleado) {
        try {
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
            $caminos = $this->getMisCaminos($nombreEmpleado);
            $camino = $caminos[0] ?? null;
            $caminoId = $camino['numCamino'] ?? null;

            return [
                'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
                'camino' => $camino,
                'estadisticas' => $this->getEstadisticasCamino($caminoId),
                'personal' => $this->getPersonalCamino($nombreEmpleado),
                'alertas' => $this->getAlertasMedicas($caminoId)
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
?>