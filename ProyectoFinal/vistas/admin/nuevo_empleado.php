<?php
// vistas/admin/nuevo_empleado.php
require_once __DIR__ . '/../../config/session.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Empleado - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Registrar Nuevo Empleado</h4>
            </div>
            <div class="card-body">
                
                <?php 
                $flash = SessionManager::getFlash();
                if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['tipo'] === 'error' ? 'danger' : 'success'; ?>">
                        <?php echo htmlspecialchars($flash['mensaje']); ?>
                    </div>
                <?php endif; ?>

                <form action="AdminController.php?action=guardar_empleado" method="POST">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol del Sistema</label>
                        <select name="rol" class="form-select">
                            <option value="GUARDA">Guarda</option>
                            <option value="SUPERVISOR">Supervisor</option>
                            <option value="ADMIN">Administrador</option>
                        </select>
                        <div class="form-text">Define los permisos base del usuario.</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Usuario (Login)</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="contrasena" class="form-control" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Guardar Empleado</button>
                        <a href="AdminController.php?action=usuarios" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>