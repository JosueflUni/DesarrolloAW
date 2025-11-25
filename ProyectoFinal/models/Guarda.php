<?php
// models/Guarda.php

require_once __DIR__ . '/../config/database.php';

class Guarda {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function getMisJaulas($nombreEmpleado) {
        try {
            // CORRECCIÓN: Tablas actualizadas
            $query = "SELECT 
                        j.numJaula,
                        j.nombre AS nombre_jaula,
                        j.tamano,
                        j.numCamino,
                        c.nombre AS nombre_camino,
                        vjc.total_animales
                      FROM Guardas g
                      INNER JOIN Jaulas j ON g.numJaula = j.numJaula
                      LEFT JOIN Caminos c ON j.numCamino = c.numCamino
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

    public function getAnimalesJaula($numJaula, $nombreEmpleado) {
        try {
            if (!$this->verificarAccesoJaula($nombreEmpleado, $numJaula)) {
                return ['error' => 'Acceso denegado'];
            }

            // CORRECCIÓN: nombre_cientifico y tablas
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
                      INNER JOIN Animales a ON vaa.numIdentif = a.numIdentif
                      LEFT JOIN Paises p ON a.numPais = p.numPais 
                      WHERE vaa.numJaula = :numJaula
                      ORDER BY vaa.nombre_animal";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAnimalesJaula: " . $e->getMessage());
            return [];
        }
    }

    public function getDetalleAnimal($numIdentif, $nombreEmpleado) {
        try {
            $query = "SELECT 
                        a.numIdentif,
                        a.nombre AS nombre_animal,
                        a.sexo,
                        a.fechaNac,
                        a.numJaula,
                        a.nombre_cientifico,
                        p.nombre AS pais_origen,
                        j.nombre AS nombre_jaula,
                        vaa.nivel_alerta,
                        vaa.total_enfermedades
                      FROM Animales a
                      LEFT JOIN Paises p ON a.numPais = p.numPais
                      LEFT JOIN Jaulas j ON a.numJaula = j.numJaula
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE a.numIdentif = :numIdentif";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numIdentif' => $numIdentif]);
            
            $animal = $stmt->fetch();
            
            if ($animal) {
                $animal['historial_medico'] = $this->getHistorialMedico($numIdentif);
            }
            
            return $animal;
        } catch (PDOException $e) {
            error_log("Error en getDetalleAnimal: " . $e->getMessage());
            return null;
        }
    }

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
                      FROM Enfermedades e
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

    public function buscarAnimal($termino) {
        try {
            $terminoLike = "%{$termino}%";
            
            $query = "SELECT 
                        a.numIdentif,
                        a.nombre AS nombre_animal,
                        a.sexo,
                        a.nombre_cientifico,
                        j.nombre AS nombre_jaula,
                        j.numJaula,
                        vaa.nivel_alerta
                      FROM Animales a
                      LEFT JOIN Jaulas j ON a.numJaula = j.numJaula
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE a.nombre LIKE :termino
                         OR a.nombre_cientifico LIKE :termino
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
                      FROM Guardas g
                      LEFT JOIN Animales a ON g.numJaula = a.numJaula
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

    private function verificarAccesoJaula($nombreEmpleado, $numJaula) {
        try {
            $query = "SELECT COUNT(*) FROM Guardas 
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

    public function registrarObservacion($numIdentif, $nombreEmpleado, $observacion, $nuevoEstado) {
        try {
            $this->conn->beginTransaction();

            // 1. Verificar Acceso
            $check = "SELECT COUNT(*) FROM Animales a
                      INNER JOIN Guardas g ON a.numJaula = g.numJaula
                      WHERE a.numIdentif = :id AND g.nombreEmpleado = :emp";
            $stmt = $this->conn->prepare($check);
            $stmt->execute(['id' => $numIdentif, 'emp' => $nombreEmpleado]);
            
            if ($stmt->fetchColumn() == 0) {
                $this->conn->rollBack();
                return ['error' => 'Acceso denegado a este animal'];
            }

            // 2. Actualizar el Estado del Animal en la tabla principal
            $updateSql = "UPDATE Animales SET estado = :estado WHERE numIdentif = :id";
            $this->conn->prepare($updateSql)->execute(['estado' => $nuevoEstado, 'id' => $numIdentif]);

            // 3. Gestión del Historial Médico (Tabla Enfermedades)
            if ($nuevoEstado === 'SANO') {
                // Si se marca como SANO, cerramos cualquier enfermedad activa (Ponemos FechaFin = HOY)
                $closeSql = "UPDATE Enfermedades SET fechaFin = CURDATE() 
                             WHERE numIdentif = :id AND fechaFin IS NULL";
                $this->conn->prepare($closeSql)->execute(['id' => $numIdentif]);
                
                // Opcional: Insertamos un registro de "Alta médica" o "Revisión"
                $logSql = "INSERT INTO Enfermedades (numIdentif, fechaInicio, fechaFin, tipoEnfermedad, tratamiento) 
                           VALUES (:id, CURDATE(), CURDATE(), 'Revisión de Rutina / Alta', :obs)";
                $this->conn->prepare($logSql)->execute(['id' => $numIdentif, 'obs' => $observacion]);

            } else {
                // Si está ENFERMO, CRITICO, etc., abrimos un nuevo registro médico
                $insertSql = "INSERT INTO Enfermedades (numIdentif, fechaInicio, tipoEnfermedad, tratamiento) 
                              VALUES (:id, CURDATE(), :estado, :obs)";
                $this->conn->prepare($insertSql)->execute([
                    'id' => $numIdentif,
                    'estado' => "Diagnóstico: $nuevoEstado", // Guardamos el estado como "Tipo"
                    'obs' => $observacion
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'mensaje' => 'Estado y observación actualizados'];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error en registrarObservacion: " . $e->getMessage());
            return ['error' => 'Error en base de datos'];
        }
    }
}
?>