<?php
// vistas/admin/usuarios.php
// Nota: La variable $usuarios llega desde el AdminController
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="AdminController.php?action=dashboard">
                <i class="bi bi-arrow-left-circle"></i> Volver al Dashboard
            </a>
            <span class="navbar-text text-white">Gestión de Personal</span>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Lista de Empleados</h2>
            <a href="AdminController.php?action=nuevo_empleado" class="btn btn-success"><i class="bi bi-person-plus"></i> Nuevo Empleado</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Rol Base</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['nombreEmpleado']); ?></td>
                            <td><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($u['rol_base']); ?></span></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <?php if($u['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>