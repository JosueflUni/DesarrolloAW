<?php
// controladores/AdminController.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Jaula.php';
require_once __DIR__ . '/../models/Animal.php';

requireAuth(['ADMIN']);

class AdminController {
    private $db;
    private $userModel;
    private $jaulaModel;
    private $animalModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();
        $this->jaulaModel = new Jaula();
        $this->animalModel = new Animal();
    }

    public function dashboard() {
        require __DIR__ . '/../vistas/admin/dashboard.php';
    }

    // ==================== GESTIÓN DE EMPLEADOS ====================
    
    public function listarUsuarios() {
        try {
            $stmt = $this->db->query("
                SELECT e.*,
                    (SELECT GROUP_CONCAT(DISTINCT numJaula SEPARATOR ', ') FROM Guardas WHERE nombreEmpleado = e.nombreEmpleado) AS jaulas_asignadas,
                    (SELECT GROUP_CONCAT(DISTINCT numCamino SEPARATOR ', ') FROM Supervisores WHERE nombreEmpleado = e.nombreEmpleado) AS caminos_asignados
                FROM Empleados e ORDER BY e.apellido, e.nombre
            ");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $usuarios = []; }
        require __DIR__ . '/../vistas/admin/usuarios.php';
    }

    public function formularioNuevoEmpleado() {
        $caminos = $this->obtenerCaminos();
        $jaulas = $this->obtenerJaulas();
        require __DIR__ . '/../vistas/admin/nuevo_empleado.php';
    }

    public function guardarEmpleado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: AdminController.php?action=nuevo_empleado');
        
        try {
            $this->db->beginTransaction();
            $datos = [
                'usuario' => trim($_POST['usuario']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'email' => trim($_POST['email']),
                'contrasena' => $_POST['contrasena'],
                'rol' => $_POST['rol']
            ];

            $res = $this->userModel->crearEmpleado($datos);
            if (isset($res['error'])) throw new Exception($res['error']);

            $this->asignarRoles($datos['usuario'], $datos['rol'], $_POST);

            $this->db->commit();
            SessionManager::setFlash('success', 'Empleado registrado exitosamente');
            header('Location: AdminController.php?action=usuarios');
        } catch (Exception $e) {
            $this->db->rollBack();
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: AdminController.php?action=nuevo_empleado');
        }
    }

    public function formularioEditarEmpleado() {
        $id = $_GET['id'] ?? null;
        if (!$id) header('Location: AdminController.php?action=usuarios');

        $stmt = $this->db->prepare("SELECT * FROM Empleados WHERE nombreEmpleado = :id");
        $stmt->execute(['id' => $id]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT numJaula FROM Guardas WHERE nombreEmpleado = :id");
        $stmt->execute(['id' => $id]);
        $jaulasAsignadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $this->db->prepare("SELECT numCamino FROM Supervisores WHERE nombreEmpleado = :id");
        $stmt->execute(['id' => $id]);
        $caminoAsignado = $stmt->fetchColumn();

        // Lógica para detectar rol calculado si es AMBOS
        if (!empty($jaulasAsignadas) && !empty($caminoAsignado)) {
            $empleado['rol_base'] = 'AMBOS'; 
        }

        $caminos = $this->obtenerCaminos();
        $jaulas = $this->obtenerJaulas();

        require __DIR__ . '/../vistas/admin/editar_empleado.php';
    }

    public function actualizarEmpleado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: AdminController.php?action=usuarios');

        try {
            $this->db->beginTransaction();
            $id = $_POST['nombreEmpleado'];

            // Actualizar datos base
            // Nota: Si el rol es AMBOS, guardamos 'GUARDA' como base o cualquiera, 
            // ya que el rol real se calcula por las tablas donde está presente.
            $rolBaseBD = ($_POST['rol'] === 'AMBOS') ? 'GUARDA' : $_POST['rol'];

            $sql = "UPDATE Empleados SET nombre=:n, apellido=:a, email=:e, rol_base=:r, activo=:ac WHERE nombreEmpleado=:id";
            $this->db->prepare($sql)->execute([
                'n' => $_POST['nombre'], 'a' => $_POST['apellido'], 'e' => $_POST['email'],
                'r' => $rolBaseBD, 'ac' => isset($_POST['activo']) ? 1 : 0, 'id' => $id
            ]);

            if (!empty($_POST['nueva_contrasena'])) {
                $hash = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);
                $this->db->prepare("UPDATE Empleados SET contrasena=:p WHERE nombreEmpleado=:id")->execute(['p'=>$hash, 'id'=>$id]);
            }

            $this->db->prepare("DELETE FROM Guardas WHERE nombreEmpleado=:id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM Supervisores WHERE nombreEmpleado=:id")->execute(['id'=>$id]);
            
            $this->asignarRoles($id, $_POST['rol'], $_POST);

            $this->db->commit();
            SessionManager::setFlash('success', 'Empleado actualizado');
            header('Location: AdminController.php?action=usuarios');
        } catch (Exception $e) {
            $this->db->rollBack();
            SessionManager::setFlash('error', $e->getMessage());
            header("Location: AdminController.php?action=editar_empleado&id=$id");
        }
    }

    public function eliminarEmpleado() {
        $id = $_GET['id'] ?? null;
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM Guardas WHERE nombreEmpleado=:id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM Supervisores WHERE nombreEmpleado=:id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM Empleados WHERE nombreEmpleado=:id")->execute(['id'=>$id]);
            $this->db->commit();
            SessionManager::setFlash('success', 'Empleado eliminado');
        } catch (Exception $e) {
            $this->db->rollBack();
            SessionManager::setFlash('error', 'Error al eliminar: ' . $e->getMessage());
        }
        header('Location: AdminController.php?action=usuarios');
    }

    // ==================== GESTIÓN DE JAULAS, CAMINOS Y ANIMALES (Igual que antes) ====================
    // ... (Mantén los métodos listarJaulas, guardarJaula, actualizarJaula, eliminarJaula, etc. tal como estaban) ...
    
    public function listarJaulas() {
        $jaulas = $this->jaulaModel->getAllJaulas();
        require __DIR__ . '/../vistas/admin/jaulas.php';
    }
    public function formularioNuevaJaula() {
        $caminos = $this->obtenerCaminos();
        require __DIR__ . '/../vistas/admin/nueva_jaula.php';
    }
    public function guardarJaula() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if($this->jaulaModel->crear($_POST['nombre'], $_POST['tamano'], $_POST['numCamino'])) {
                SessionManager::setFlash('success', 'Jaula creada');
                header('Location: AdminController.php?action=jaulas');
            } else {
                SessionManager::setFlash('error', 'Error al crear jaula');
                header('Location: AdminController.php?action=nueva_jaula');
            }
        }
    }
    public function formularioEditarJaula() {
        $id = $_GET['id'] ?? null;
        $jaula = $this->jaulaModel->getJaula($id);
        $caminos = $this->obtenerCaminos();
        if(!$jaula) { header('Location: AdminController.php?action=jaulas'); exit; }
        require __DIR__ . '/../vistas/admin/editar_jaula.php';
    }
    public function actualizarJaula() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if($this->jaulaModel->actualizar($_POST['numJaula'], $_POST['nombre'], $_POST['tamano'], $_POST['numCamino'])) {
                SessionManager::setFlash('success', 'Jaula actualizada');
                header('Location: AdminController.php?action=jaulas');
            } else {
                header("Location: AdminController.php?action=editar_jaula&id=".$_POST['numJaula']);
            }
        }
    }
    public function eliminarJaula() {
        $id = $_GET['id'] ?? null;
        $res = $this->jaulaModel->eliminar($id);
        if ($res === true) SessionManager::setFlash('success', 'Jaula eliminada');
        else SessionManager::setFlash('error', is_array($res) ? $res['error'] : 'Error');
        header('Location: AdminController.php?action=jaulas');
    }

    public function listarCaminos() {
        $caminos = $this->obtenerCaminosCompletos();
        require __DIR__ . '/../vistas/admin/caminos.php';
    }
    public function formularioNuevoCamino() { require __DIR__ . '/../vistas/admin/nuevo_camino.php'; }
    public function guardarCamino() {
        try {
            $sql = "INSERT INTO Caminos (nombre, largo) VALUES (:n, :l)";
            $this->db->prepare($sql)->execute(['n'=>$_POST['nombre'], 'l'=>$_POST['largo']]);
            SessionManager::setFlash('success', 'Camino creado');
            header('Location: AdminController.php?action=caminos');
        } catch (Exception $e) { header('Location: AdminController.php?action=nuevo_camino'); }
    }
    public function formularioEditarCamino() {
        $id = $_GET['id'] ?? null;
        $stmt = $this->db->prepare("SELECT * FROM Caminos WHERE numCamino = :id");
        $stmt->execute(['id'=>$id]);
        $camino = $stmt->fetch(PDO::FETCH_ASSOC);
        require __DIR__ . '/../vistas/admin/editar_camino.php';
    }
    public function actualizarCamino() {
        try {
            $sql = "UPDATE Caminos SET nombre = :n, largo = :l WHERE numCamino = :id";
            $this->db->prepare($sql)->execute(['n'=>$_POST['nombre'], 'l'=>$_POST['largo'], 'id'=>$_POST['numCamino']]);
            SessionManager::setFlash('success', 'Camino actualizado');
            header('Location: AdminController.php?action=caminos');
        } catch (Exception $e) { header('Location: AdminController.php?action=caminos'); }
    }
    public function eliminarCamino() {
        $id = $_GET['id'] ?? null;
        try {
            $check = $this->db->prepare("SELECT COUNT(*) FROM Jaulas WHERE numCamino = :id");
            $check->execute(['id'=>$id]);
            if ($check->fetchColumn() > 0) throw new Exception("Tiene jaulas asignadas");
            $this->db->prepare("DELETE FROM Supervisores WHERE numCamino = :id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM Caminos WHERE numCamino = :id")->execute(['id'=>$id]);
            SessionManager::setFlash('success', 'Camino eliminado');
        } catch (Exception $e) { SessionManager::setFlash('error', $e->getMessage()); }
        header('Location: AdminController.php?action=caminos');
    }

    public function listarAnimales() {
        $stmt = $this->db->query("SELECT a.*, j.nombre as nombre_jaula, p.nombre as nombre_pais, vaa.nivel_alerta FROM Animales a LEFT JOIN Jaulas j ON a.numJaula=j.numJaula LEFT JOIN Paises p ON a.numPais=p.numPais LEFT JOIN VistaAnimalesConAlertas vaa ON a.numIdentif=vaa.numIdentif ORDER BY a.nombre");
        $animales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../vistas/admin/animales.php';
    }
    public function formularioNuevoAnimal() {
        $jaulas = $this->obtenerJaulas();
        $paises = $this->obtenerPaises();
        require __DIR__ . '/../vistas/admin/nuevo_animal.php';
    }
    public function guardarAnimal() {
        try {
            $res = $this->animalModel->crear($_POST);
            if ($res === true) {
                SessionManager::setFlash('success', 'Animal registrado');
                header('Location: AdminController.php?action=animales');
            } else { throw new Exception(is_array($res)?$res['error']:'Error desconocido'); }
        } catch (Exception $e) {
            SessionManager::setFlash('error', $e->getMessage());
            header('Location: AdminController.php?action=nuevo_animal');
        }
    }
    public function formularioEditarAnimal() {
        $id = $_GET['id'] ?? null;
        $sql = "SELECT * FROM Animales WHERE numIdentif = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$animal) { header('Location: AdminController.php?action=animales'); exit; }
        $jaulas = $this->obtenerJaulas();
        $paises = $this->obtenerPaises();
        require __DIR__ . '/../vistas/admin/editar_animal.php';
    }
    public function actualizarAnimal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') header('Location: AdminController.php?action=animales');
        try {
            $this->animalModel->actualizar($_POST['numIdentif'], $_POST);
            SessionManager::setFlash('success', 'Animal actualizado');
            header('Location: AdminController.php?action=animales');
        } catch (Exception $e) { header("Location: AdminController.php?action=editar_animal&id=" . $_POST['numIdentif']); }
    }
    public function eliminarAnimal() {
        $id = $_GET['id'] ?? null;
        $this->animalModel->eliminar($id);
        SessionManager::setFlash('success', 'Animal eliminado');
        header('Location: AdminController.php?action=animales');
    }

    // ==================== AUXILIARES ACTUALIZADOS ====================

    private function asignarRoles($usuarioId, $rol, $postData) {
        // Si es Guarda O es Ambos, asignamos jaulas
        if ($rol === 'GUARDA' || $rol === 'AMBOS') {
            if (!empty($postData['jaulas'])) {
                $stmt = $this->db->prepare("INSERT INTO Guardas (nombreEmpleado, numJaula) VALUES (:u, :j)");
                foreach ($postData['jaulas'] as $jaulaId) {
                    $stmt->execute(['u' => $usuarioId, 'j' => $jaulaId]);
                }
            }
        }

        // Si es Supervisor O es Ambos, asignamos camino
        if ($rol === 'SUPERVISOR' || $rol === 'AMBOS') {
            if (!empty($postData['camino'])) {
                $this->db->prepare("INSERT INTO Supervisores (nombreEmpleado, numCamino) VALUES (:u, :c)")
                     ->execute(['u' => $usuarioId, 'c' => $postData['camino']]);
            }
        }
    }

    private function obtenerCaminos() {
        return $this->db->query("SELECT numCamino, nombre FROM Caminos ORDER BY numCamino")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerCaminosCompletos() {
        return $this->db->query("
            SELECT c.*, COUNT(DISTINCT j.numJaula) as total_jaulas, 
            (SELECT COUNT(*) FROM Animales a JOIN Jaulas j2 ON a.numJaula=j2.numJaula WHERE j2.numCamino=c.numCamino) as total_animales,
            (SELECT CONCAT(e.nombre,' ',e.apellido) FROM Supervisores s JOIN Empleados e ON s.nombreEmpleado=e.nombreEmpleado WHERE s.numCamino=c.numCamino LIMIT 1) as supervisor
            FROM Caminos c LEFT JOIN Jaulas j ON c.numCamino=j.numCamino GROUP BY c.numCamino
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerJaulas() {
        return $this->db->query("SELECT j.numJaula, j.nombre, j.numCamino, c.nombre as nombre_camino FROM Jaulas j LEFT JOIN Caminos c ON j.numCamino=c.numCamino ORDER BY j.numCamino")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerPaises() {
        return $this->db->query("SELECT * FROM Paises ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Router
$controller = new AdminController();
$action = $_GET['action'] ?? 'dashboard';

$methodName = $action;
if (strpos($action, 'guardar_') === 0 || strpos($action, 'actualizar_') === 0) {
    $parts = explode('_', $action);
    $methodName = $parts[0] . ucfirst($parts[1]);
} elseif (strpos($action, 'editar_') === 0 || strpos($action, 'nuevo_') === 0 || strpos($action, 'nueva_') === 0) {
    $parts = explode('_', $action);
    $methodName = 'formulario' . ucfirst($parts[0]) . ucfirst($parts[1]);
} elseif (strpos($action, 'eliminar_') === 0) {
    $parts = explode('_', $action);
    $methodName = $parts[0] . ucfirst($parts[1]);
}

if (method_exists($controller, $methodName)) {
    $controller->$methodName();
} else {
    switch($action) {
        case 'usuarios': $controller->listarUsuarios(); break;
        case 'animales': $controller->listarAnimales(); break;
        case 'jaulas': $controller->listarJaulas(); break;
        case 'caminos': $controller->listarCaminos(); break;
        default: $controller->dashboard();
    }
}
?>