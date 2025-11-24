<?php
// models/Animal.php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo para operaciones con Animales
 */
class Animal {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Obtener información completa de un animal
     */
    public function getAnimal($numIdentif) {
        try {
            $query = "SELECT 
                        a.numIdentif,
                        a.nombre AS nombre_animal,
                        a.sexo,
                        a.fechaNac,
                        a.numJaula,
                        a.nombreA AS nombre_cientifico,
                        a.numPais,
                        p.nombre AS pais_origen,
                        j.nombre AS nombre_jaula,
                        vaa.nivel_alerta,
                        vaa.total_enfermedades,
                        vaa.ultima_enfermedad
                      FROM LosAnimales a
                      LEFT JOIN LosPaises p ON a.numPais = p.numPais
                      LEFT JOIN LasJaulas j ON a.numJaula = j.numJaula
                      LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                      WHERE a.numIdentif = :numIdentif";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numIdentif' => $numIdentif]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getAnimal: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todos los animales
     */
    public function getAllAnimales() {
        try {
            $query = "SELECT * FROM VistaAnimalesConAlertas ORDER BY nombre_animal";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAllAnimales: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener animales de una jaula
     */
    public function getAnimalesByJaula($numJaula) {
        try {
            $query = "SELECT * FROM VistaAnimalesConAlertas 
                      WHERE numJaula = :numJaula 
                      ORDER BY nombre_animal";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numJaula' => $numJaula]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAnimalesByJaula: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar animales
     */
    public function buscar($termino) {
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
                      LIMIT 50";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['termino' => $terminoLike]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en buscar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear un nuevo animal
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO LosAnimales 
                      (numIdentif, nombre, sexo, fechaNac, numJaula, nombreA, numPais) 
                      VALUES 
                      (:numIdentif, :nombre, :sexo, :fechaNac, :numJaula, :nombreA, :numPais)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'numIdentif' => $datos['numIdentif'],
                'nombre' => $datos['nombre'],
                'sexo' => $datos['sexo'],
                'fechaNac' => $datos['fechaNac'],
                'numJaula' => $datos['numJaula'],
                'nombreA' => $datos['nombreA'],
                'numPais' => $datos['numPais']
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en crear animal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar información de un animal
     */
    public function actualizar($numIdentif, $datos) {
        try {
            $query = "UPDATE LosAnimales 
                      SET nombre = :nombre,
                          sexo = :sexo,
                          fechaNac = :fechaNac,
                          numJaula = :numJaula,
                          nombreA = :nombreA,
                          numPais = :numPais
                      WHERE numIdentif = :numIdentif";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'numIdentif' => $numIdentif,
                'nombre' => $datos['nombre'],
                'sexo' => $datos['sexo'],
                'fechaNac' => $datos['fechaNac'],
                'numJaula' => $datos['numJaula'],
                'nombreA' => $datos['nombreA'],
                'numPais' => $datos['numPais']
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en actualizar animal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mover animal a otra jaula
     */
    public function moverJaula($numIdentif, $nuevaJaula) {
        try {
            $query = "UPDATE LosAnimales 
                      SET numJaula = :numJaula 
                      WHERE numIdentif = :numIdentif";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'numIdentif' => $numIdentif,
                'numJaula' => $nuevaJaula
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en moverJaula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un animal
     */
    public function eliminar($numIdentif) {
        try {
            // Primero eliminar enfermedades asociadas
            $query = "DELETE FROM LasEnfermedades WHERE numIdentif = :numIdentif";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numIdentif' => $numIdentif]);
            
            // Luego eliminar el animal
            $query = "DELETE FROM LosAnimales WHERE numIdentif = :numIdentif";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['numIdentif' => $numIdentif]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en eliminar animal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial médico de un animal
     */
    public function getHistorialMedico($numIdentif) {
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
     * Obtener animales con alertas críticas
     */
    public function getAnimalesCriticos() {
        try {
            $query = "SELECT * FROM VistaAnimalesConAlertas 
                      WHERE nivel_alerta = 'CRITICO' 
                      ORDER BY ultima_enfermedad DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAnimalesCriticos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas generales de animales
     */
    public function getEstadisticasGenerales() {
        try {
            $query = "SELECT 
                        COUNT(*) AS total_animales,
                        COUNT(CASE WHEN sexo = 'M' THEN 1 END) AS machos,
                        COUNT(CASE WHEN sexo = 'H' THEN 1 END) AS hembras,
                        COUNT(DISTINCT nombreA) AS total_especies,
                        COUNT(DISTINCT numJaula) AS jaulas_ocupadas
                      FROM LosAnimales";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getEstadisticasGenerales: " . $e->getMessage());
            return null;
        }
    }
}