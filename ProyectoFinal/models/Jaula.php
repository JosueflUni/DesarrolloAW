<?php
// models/Jaula.php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo para operaciones con Jaulas
 */
class Jaula {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Obtener información completa de una jaula
     */
    public function getJaula($numJaula) {
        try {
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
                      LEFT JOIN LosCaminos c ON j.numCamino = c.numCamino
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

    /**
     * Obtener todas las jaulas
     */
    public function getAllJaulas() {
        try {
            $query = "SELECT * FROM VistaJaulasCompletas ORDER BY numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAllJaulas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener jaulas de un camino
     */
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

    /**
     * Obtener jaulas vacías
     */
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

    /**
     * Crear una nueva jaula
     */
    public function crear($nombre, $tamano, $numCamino) {
        try {
            $query = "INSERT INTO Jaulas (nombre, tamano, numCamino) 
                      VALUES (:nombre, :tamano, :numCamino)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'nombre' => $nombre,
                'tamano' => $tamano,
                'numCamino' => $numCamino
            ]);
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear jaula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar información de una jaula
     */
    public function actualizar($numJaula, $nombre, $tamano, $numCamino) {
        try {
            $query = "UPDATE Jaulas 
                      SET nombre = :nombre, 
                          tamano = :tamano, 
                          numCamino = :numCamino 
                      WHERE numJaula = :numJaula";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'numJaula' => $numJaula,
                'nombre' => $nombre,
                'tamano' => $tamano,
                'numCamino' => $numCamino
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en actualizar jaula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una jaula (solo si está vacía)
     */
    public function eliminar($numJaula) {
        try {
            // Verificar que la jaula esté vacía
            $query = "SELECT COUNT(*) FROM Animales WHERE numJaula = :numJaula";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            if ($stmt->fetchColumn() > 0) {
                return ['error' => 'No se puede eliminar una jaula con animales'];
            }
            
            // Eliminar jaula
            $query = "DELETE FROM Jaulas WHERE numJaula = :numJaula";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en eliminar jaula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener capacidad de una jaula
     */
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