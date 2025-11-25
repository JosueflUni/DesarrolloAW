<?php
// models/Jaula.php

require_once __DIR__ . '/../config/database.php';

class Jaula {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function getJaula($numJaula) {
        try {
            // CORRECCIÓN: Cambiado 'LosCaminos' por 'Caminos'
            $query = "SELECT 
                        j.numJaula,
                        j.nombre AS nombre_jaula,
                        j.tamano,
                        j.numCamino,
                        c.nombre AS nombre_camino,
                        c.largo AS largo_camino,
                        vjc.total_animales,
                        vjc.guardas_asignados
                      FROM Jaulas j
                      LEFT JOIN Caminos c ON j.numCamino = c.numCamino
                      LEFT JOIN VistaJaulasCompletas vjc ON j.numJaula = vjc.numJaula
                      WHERE j.numJaula = :numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getJaula: " . $e->getMessage());
            return null;
        }
    }

    public function getAllJaulas() {
        try {
            $query = "SELECT * FROM VistaJaulasCompletas ORDER BY numJaula";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function crear($nombre, $tamano, $numCamino) {
        try {
            $query = "INSERT INTO Jaulas (nombre, tamano, numCamino) VALUES (:nombre, :tamano, :numCamino)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nombre' => $nombre, 'tamano' => $tamano, 'numCamino' => $numCamino]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) { return false; }
    }

    public function actualizar($numJaula, $nombre, $tamano, $numCamino) {
        try {
            $query = "UPDATE Jaulas SET nombre = :nombre, tamano = :tamano, numCamino = :numCamino WHERE numJaula = :numJaula";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula, 'nombre' => $nombre, 'tamano' => $tamano, 'numCamino' => $numCamino]);
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function eliminar($numJaula) {
        try {
            $query = "SELECT COUNT(*) FROM Animales WHERE numJaula = :numJaula";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            if ($stmt->fetchColumn() > 0) {
                return ['error' => 'No se puede eliminar una jaula con animales'];
            }
            
            // Primero eliminamos asignaciones de guardas para evitar errores de llave foránea
            $delGuardas = "DELETE FROM Guardas WHERE numJaula = :numJaula";
            $this->conn->prepare($delGuardas)->execute(['numJaula' => $numJaula]);

            $query = "DELETE FROM Jaulas WHERE numJaula = :numJaula";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return true;
        } catch (PDOException $e) {
            return ['error' => 'Error BD: ' . $e->getMessage()];
        }
    }

    public function getJaulasByCamino($numCamino) {
        try {
            $query = "SELECT * FROM VistaJaulasCompletas 
                      WHERE numCamino = :numCamino 
                      ORDER BY numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numCamino' => $numCamino]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getJaulasByCamino: " . $e->getMessage());
            return [];
        }
    }

    public function getJaulasDisponibles() {
        try {
            $query = "SELECT j.* 
                      FROM Jaulas j
                      LEFT JOIN Animales a ON j.numJaula = a.numJaula
                      WHERE a.numIdentif IS NULL
                      ORDER BY j.numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getJaulasDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function getCapacidad($numJaula) {
        try {
            $query = "SELECT 
                        j.tamano,
                        COUNT(a.numIdentif) AS animales_actuales,
                        (j.tamano - COUNT(a.numIdentif) * 10) AS espacio_disponible
                      FROM Jaulas j
                      LEFT JOIN Animales a ON j.numJaula = a.numJaula
                      WHERE j.numJaula = :numJaula
                      GROUP BY j.numJaula, j.tamano";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getCapacidad: " . $e->getMessage());
            return null;
        }
    }
}
?>