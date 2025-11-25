<?php
// controladores/AdminController.php - VERSIÓN COMPLETA Y FUNCIONAL

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

requireAuth(['ADMIN']);

class AdminController {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();
    }

    public function dashboard() {
        header('Location: /dawb/ProyectoFinal/vistas/admin/dashboard.php');
    }

    // ==================== GESTIÓN DE EMPLEADOS ====================
    
    public function listarUsuarios() {
        try {
            $stmt = $this->db->query("
                SELECT e.*,
                    (SELECT GROUP_CONCAT(DISTINCT numJaula SEPARATOR ', ') 
                     FROM Guardas WHERE nombreEmpleado = e.nombreEmpleado) AS jaulas_asignadas,
                    (SELECT GROUP_CONCAT(DISTINCT numCamino SEPARATOR ', ') 
                     FROM Supervisores WHERE nombreEmpleado = e.nombreEmpleado) AS caminos_asignados
                FROM Empleados e 
                ORDER BY e.apellido, e.nombre
            ");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listando usuarios: " . $e->getMessage());
            $usuarios = [];
        }
        require __DIR__ . '/../vistas/admin/usuarios.php';
    }

    public function formularioNuevoEmpleado() {
        // Obtener datos para los selectores
        $caminos = $this->obtenerCaminos();
        $jaulas = $this->obtenerJaulas();
        require __DIR__ . '/../vistas/admin/nuevo_empleado.php';
    }

    public function guardarEmpleado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: AdminController.php?action=nuevo_empleado');
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Validar datos
            $datos = [
                'usuario' => trim($_POST['usuario'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'contrasena' => $_POST['contrasena'] ?? '',
                'rol' => $_POST['rol'] ?? 'GUARDA'
            ];

            if (empty($datos['usuario']) || empty($datos['contrasena'])) {
                throw new Exception('Usuario y contraseña son obligatorios');
            }

            // 2. Crear empleado
            $resultado = $this->userModel->crearEmpleado($datos);
            
            if (isset($resultado['error'])) {
                throw new Exception($resultado['error']);
            }

            // 3. Asignar según rol
            if ($datos['rol'] === 'GUARDA' && !empty($_POST['jaulas'])) {
                $jaulas = $_POST['jaulas']; // Array de IDs de jaulas
                foreach ($jaulas as $jaulaId) {
                    $stmt = $this->db->prepare("INSERT INTO Guardas (nombreEmpleado, numJaula) VALUES (:emp, :jaula)");
                    $stmt->execute(['emp' => $datos['usuario'], 'jaula' => $jaulaId]);
                }
            } elseif ($datos['rol'] === 'SUPERVISOR' && !empty($_POST['camino'])) {
                $stmt = $this->db->prepare("INSERT INTO Supervisores (nombreEmpleado, numCamino) VALUES (:emp, :camino)");
                $stmt->execute(['emp' => $datos['usuario'], 'camino' => $_POST['camino']]);
            }

            $this->db->commit();
            SessionManager::setFlash('success', 'Empleado registrado exitosamente');
            header('Location: AdminController.php?action=usuarios');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error guardando empleado: " . $e->getMessage());
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: AdminController.php?action=nuevo_empleado');
        }
    }

    public function formularioEditarEmpleado() {
        $nombreEmpleado = $_GET['id'] ?? null;
        if (!$nombreEmpleado) {
            SessionManager::setFlash('error', 'ID de empleado requerido');
            header('Location: AdminController.php?action=usuarios');
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM Empleados WHERE nombreEmpleado = :id");
            $stmt->execute(['id' => $nombreEmpleado]);
            $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$empleado) {
                throw new Exception('Empleado no encontrado');
            }

            // Obtener asignaciones actuales
            $stmt = $this->db->prepare("SELECT numJaula FROM Guardas WHERE nombreEmpleado = :id");
            $stmt->execute(['id' => $nombreEmpleado]);
            $jaulasAsignadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $this->db->prepare("SELECT numCamino FROM Supervisores WHERE nombreEmpleado = :id");
            $stmt->execute(['id' => $nombreEmpleado]);
            $caminosAsignados = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $caminos = $this->obtenerCaminos();
            $jaulas = $this->obtenerJaulas();

            require __DIR__ . '/../vistas/admin/editar_empleado.php';
            
        } catch (Exception $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: AdminController.php?action=usuarios');
        }
    }

    public function actualizarEmpleado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: AdminController.php?action=usuarios');
            return;
        }

        try {
            $this->db->beginTransaction();

            $nombreEmpleado = $_POST['nombreEmpleado'] ?? null;
            if (!$nombreEmpleado) {
                throw new Exception('ID de empleado requerido');
            }

            // 1. Actualizar datos básicos
            $sql = "UPDATE Empleados SET 
                    nombre = :nombre,
                    apellido = :apellido,
                    email = :email,
                    rol_base = :rol,
                    activo = :activo
                    WHERE nombreEmpleado = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'email' => trim($_POST['email']),
                'rol' => $_POST['rol'],
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'id' => $nombreEmpleado
            ]);

            // 2. Actualizar contraseña si se proporcionó
            if (!empty($_POST['nueva_contrasena'])) {
                $hash = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE Empleados SET contrasena = :pass WHERE nombreEmpleado = :id");
                $stmt->execute(['pass' => $hash, 'id' => $nombreEmpleado]);
            }

            // 3. Actualizar asignaciones
            // Eliminar asignaciones anteriores
            $this->db->prepare("DELETE FROM Guardas WHERE nombreEmpleado = :id")->execute(['id' => $nombreEmpleado]);
            $this->db->prepare("DELETE FROM Supervisores WHERE nombreEmpleado = :id")->execute(['id' => $nombreEmpleado]);

            // Agregar nuevas asignaciones
            if ($_POST['rol'] === 'GUARDA' && !empty($_POST['jaulas'])) {
                foreach ($_POST['jaulas'] as $jaulaId) {
                    $stmt = $this->db->prepare("INSERT INTO Guardas (nombreEmpleado, numJaula) VALUES (:emp, :jaula)");
                    $stmt->execute(['emp' => $nombreEmpleado, 'jaula' => $jaulaId]);
                }
            } elseif ($_POST['rol'] === 'SUPERVISOR' && !empty($_POST['camino'])) {
                $stmt = $this->db->prepare("INSERT INTO Supervisores (nombreEmpleado, numCamino) VALUES (:emp, :camino)");
                $stmt->execute(['emp' => $nombreEmpleado, 'camino' => $_POST['camino']]);
            }

            $this->db->commit();
            SessionManager::setFlash('success', 'Empleado actualizado exitosamente');
            header('Location: AdminController.php?action=usuarios');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error actualizando empleado: " . $e->getMessage());
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: AdminController.php?action=editar_empleado&id=' . $nombreEmpleado);
        }
    }

    public function eliminarEmpleado() {
        $nombreEmpleado = $_GET['id'] ?? null;
        if (!$nombreEmpleado) {
            SessionManager::setFlash('error', 'ID de empleado requerido');
            header('Location: AdminController.php?action=usuarios');
            return;
        }

        try {
            $this->db->beginTransaction();

            // Eliminar asignaciones
            $this->db->prepare("DELETE FROM Guardas WHERE nombreEmpleado = :id")->execute(['id' => $nombreEmpleado]);
            $this->db->prepare("DELETE FROM Supervisores WHERE nombreEmpleado = :id")->execute(['id' => $nombreEmpleado]);
            
            // Eliminar empleado
            $stmt = $this->db->prepare("DELETE FROM Empleados WHERE nombreEmpleado = :id");
            $stmt->execute(['id' => $nombreEmpleado]);

            $this->db->commit();
            SessionManager::setFlash('success', 'Empleado eliminado exitosamente');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            SessionManager::setFlash('error', 'Error al eliminar empleado: ' . $e->getMessage());
        }

        header('Location: AdminController.php?action=usuarios');
    }

    // ==================== GESTIÓN DE ANIMALES ====================
    
    public function listarAnimales() {
        try {
            $stmt = $this->db->query("
                SELECT a.*, 
                    j.nombre AS nombre_jaula,
                    p.nombre AS nombre_pais,
                    vaa.nivel_alerta
                FROM Animales a
                LEFT JOIN Jaulas j ON a.numJaula = j.numJaula
                LEFT JOIN Paises p ON a.numPais = p.numPais
                LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                ORDER BY a.nombre
            ");
            $animales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $animales = [];
        }
        require __DIR__ . '/../vistas/admin/animales.php';
    }

    public function formularioNuevoAnimal() {
        $jaulas = $this->obtenerJaulas();
        $paises = $this->obtenerPaises();
        require __DIR__ . '/../vistas/admin/nuevo_animal.php';
    }

    public function guardarAnimal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: AdminController.php?action=nuevo_animal');
            return;
        }

        try {
            $sql = "INSERT INTO Animales (numIdentif, nombre, sexo, fechaNac, numJaula, nombre_cientifico, numPais) 
                    VALUES (:id, :nombre, :sexo, :fecha, :jaula, :cientifico, :pais)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => trim($_POST['numIdentif']),
                'nombre' => trim($_POST['nombre']),
                'sexo' => $_POST['sexo'],
                'fecha' => $_POST['fechaNac'] ?: null,
                'jaula' => $_POST['numJaula'],
                'cientifico' => trim($_POST['nombre_cientifico']),
                'pais' => $_POST['numPais'] ?: null
            ]);

            SessionManager::setFlash('success', 'Animal registrado exitosamente');
            header('Location: AdminController.php?action=animales');
            
        } catch (PDOException $e) {
            SessionManager::setFlash('error', 'Error al registrar animal: ' . $e->getMessage());
            header('Location: AdminController.php?action=nuevo_animal');
        }
    }

    // ==================== GESTIÓN DE CAMINOS ====================
    
    public function listarCaminos() {
        try {
            $stmt = $this->db->query("
                SELECT c.*,
                    COUNT(DISTINCT j.numJaula) AS total_jaulas,
                    (SELECT COUNT(*) FROM Animales a 
                     INNER JOIN Jaulas j2 ON a.numJaula = j2.numJaula 
                     WHERE j2.numCamino = c.numCamino) AS total_animales,
                    (SELECT CONCAT(e.nombre, ' ', e.apellido)
                     FROM Supervisores s
                     INNER JOIN Empleados e ON s.nombreEmpleado = e.nombreEmpleado
                     WHERE s.numCamino = c.numCamino LIMIT 1) AS supervisor
                FROM Caminos c
                LEFT JOIN Jaulas j ON c.numCamino = j.numCamino
                GROUP BY c.numCamino
                ORDER BY c.numCamino
            ");
            $caminos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $caminos = [];
        }
        require __DIR__ . '/../vistas/admin/caminos.php';
    }

    public function formularioNuevoCamino() {
        require __DIR__ . '/../vistas/admin/nuevo_camino.php';
    }

    public function guardarCamino() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: AdminController.php?action=nuevo_camino');
            return;
        }

        try {
            $sql = "INSERT INTO Caminos (nombre, largo) VALUES (:nombre, :largo)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'nombre' => trim($_POST['nombre']),
                'largo' => floatval($_POST['largo'])
            ]);

            SessionManager::setFlash('success', 'Camino registrado exitosamente');
            header('Location: AdminController.php?action=caminos');
            
        } catch (PDOException $e) {
            SessionManager::setFlash('error', 'Error al registrar camino: ' . $e->getMessage());
            header('Location: AdminController.php?action=nuevo_camino');
        }
    }

    // ==================== GESTIÓN DE JAULAS ====================
    
    public function listarJaulas() {
        try {
            $stmt = $this->db->query("
                SELECT j.*,
                    c.nombre AS nombre_camino,
                    COUNT(DISTINCT a.numIdentif) AS total_animales,
                    GROUP_CONCAT(DISTINCT CONCAT(e.nombre, ' ', e.apellido) SEPARATOR ', ') AS guardas
                FROM Jaulas j
                LEFT JOIN Caminos c ON j.numCamino = c.numCamino
                LEFT JOIN Animales a ON j.numJaula = a.numJaula
                LEFT JOIN Guardas g ON j.numJaula = g.numJaula
                LEFT JOIN Empleados e ON g.nombreEmpleado = e.nombreEmpleado
                GROUP BY j.numJaula
                ORDER BY j.numCamino, j.numJaula
            ");
            $jaulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $jaulas = [];
        }
        require __DIR__ . '/../vistas/admin/jaulas.php';
    }

    public function formularioNuevaJaula() {
        $caminos = $this->obtenerCaminos();
        require __DIR__ . '/../vistas/admin/nueva_jaula.php';
    }

    public function guardarJaula() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: AdminController.php?action=nueva_jaula');
            return;
        }

        try {
            $sql = "INSERT INTO Jaulas (nombre, tamano, numCamino) VALUES (:nombre, :tamano, :camino)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'nombre' => trim($_POST['nombre']),
                'tamano' => floatval($_POST['tamano']),
                'camino' => $_POST['numCamino']
            ]);

            SessionManager::setFlash('success', 'Jaula registrada exitosamente');
            header('Location: AdminController.php?action=jaulas');
            
        } catch (PDOException $e) {
            SessionManager::setFlash('error', 'Error al registrar jaula: ' . $e->getMessage());
            header('Location: AdminController.php?action=nueva_jaula');
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    
    private function obtenerCaminos() {
        try {
            $stmt = $this->db->query("SELECT numCamino, nombre FROM Caminos ORDER BY numCamino");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function obtenerJaulas() {
        try {
            $stmt = $this->db->query("
                SELECT j.numJaula, j.nombre, j.numCamino, c.nombre AS nombre_camino
                FROM Jaulas j
                LEFT JOIN Caminos c ON j.numCamino = c.numCamino
                ORDER BY j.numCamino, j.numJaula
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function obtenerPaises() {
        try {
            $stmt = $this->db->query("SELECT numPais, nombre FROM Paises ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Router
$controller = new AdminController();
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    // Dashboard
    case 'dashboard':
        $controller->dashboard();
        break;
    
    // Empleados
    case 'usuarios':
        $controller->listarUsuarios();
        break;
    case 'nuevo_empleado':
        $controller->formularioNuevoEmpleado();
        break;
    case 'guardar_empleado':
        $controller->guardarEmpleado();
        break;
    case 'editar_empleado':
        $controller->formularioEditarEmpleado();
        break;
    case 'actualizar_empleado':
        $controller->actualizarEmpleado();
        break;
    case 'eliminar_empleado':
        $controller->eliminarEmpleado();
        break;
    
    // Animales
    case 'animales':
        $controller->listarAnimales();
        break;
    case 'nuevo_animal':
        $controller->formularioNuevoAnimal();
        break;
    case 'guardar_animal':
        $controller->guardarAnimal();
        break;
    
    // Caminos
    case 'caminos':
        $controller->listarCaminos();
        break;
    case 'nuevo_camino':
        $controller->formularioNuevoCamino();
        break;
    case 'guardar_camino':
        $controller->guardarCamino();
        break;
    
    // Jaulas
    case 'jaulas':
        $controller->listarJaulas();
        break;
    case 'nueva_jaula':
        $controller->formularioNuevaJaula();
        break;
    case 'guardar_jaula':
        $controller->guardarJaula();
        break;
    
    default:
        $controller->dashboard();
}
?>