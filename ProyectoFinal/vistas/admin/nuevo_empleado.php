<?php require_once __DIR__ . '/../../config/session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Empleado - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .section-title { color: #495057; font-weight: 600; margin-bottom: 15px; border-bottom: 2px solid #dee2e6; padding-bottom: 10px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="AdminController.php?action=dashboard">
                <i class="bi bi-arrow-left-circle"></i> Volver al Dashboard
            </a>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Registrar Nuevo Empleado</h4>
            </div>
            <div class="card-body">
                
                <?php $flash = SessionManager::getFlash(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['tipo'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($flash['mensaje']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="AdminController.php?action=guardar_empleado" method="POST" id="formEmpleado">
                    
                    <div class="form-section">
                        <h5 class="section-title"><i class="bi bi-person"></i> Información Personal</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido *</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title"><i class="bi bi-key"></i> Credenciales</h5>
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario *</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="contrasena" class="form-control" required minlength="6">
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title"><i class="bi bi-shield-check"></i> Rol y Asignaciones</h5>
                        <div class="mb-3">
                            <label class="form-label">Rol del Sistema *</label>
                            <select name="rol" id="selectRol" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="GUARDA">Guarda</option>
                                <option value="SUPERVISOR">Supervisor</option>
                                <option value="AMBOS">Ambos (Guarda + Supervisor)</option>
                                <option value="ADMIN">Administrador</option>
                            </select>
                        </div>

                        <div id="seccionJaulas" style="display:none;">
                            <label class="form-label">Jaulas Asignadas</label>
                            <div class="border rounded p-3 bg-white" style="max-height: 300px; overflow-y: auto;">
                                <?php if (empty($jaulas)): ?>
                                    <p class="text-muted">No hay jaulas disponibles</p>
                                <?php else: ?>
                                    <?php foreach ($jaulas as $jaula): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jaulas[]" value="<?php echo $jaula['numJaula']; ?>">
                                            <label class="form-check-label">
                                                Jaula #<?php echo $jaula['numJaula']; ?> - <?php echo htmlspecialchars($jaula['nombre'] ?? 'Sin nombre'); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($jaula['nombre_camino'] ?? 'Sin camino'); ?>)</small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="form-text mt-2">Selecciona las jaulas que este guarda supervisará</div>
                        </div>

                        <div id="seccionCamino" class="mt-3" style="display:none;">
                            <label class="form-label">Camino Asignado</label>
                            <select name="camino" class="form-select bg-white">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($caminos as $camino): ?>
                                    <option value="<?php echo $camino['numCamino']; ?>">
                                        Camino #<?php echo $camino['numCamino']; ?> - <?php echo htmlspecialchars($camino['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">El supervisor tendrá acceso a todas las jaulas de este camino</div>
                        </div>

                        <div id="seccionAdmin" class="mt-3" style="display:none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Los administradores tienen acceso completo al sistema.
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Guardar Empleado
                        </button>
                        <a href="AdminController.php?action=usuarios" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('selectRol').addEventListener('change', function() {
            const rol = this.value;
            
            const secJaulas = document.getElementById('seccionJaulas');
            const secCamino = document.getElementById('seccionCamino');
            const secAdmin = document.getElementById('seccionAdmin');
            
            // Resetear visibilidad
            secJaulas.style.display = 'none';
            secCamino.style.display = 'none';
            secAdmin.style.display = 'none';
            
            if (rol === 'GUARDA') {
                secJaulas.style.display = 'block';
            } else if (rol === 'SUPERVISOR') {
                secCamino.style.display = 'block';
            } else if (rol === 'AMBOS') {
                secJaulas.style.display = 'block';
                secCamino.style.display = 'block';
            } else if (rol === 'ADMIN') {
                secAdmin.style.display = 'block';
            }
        });

        document.getElementById('formEmpleado').addEventListener('submit', function(e) {
            const rol = document.getElementById('selectRol').value;
            
            // Validación Jaulas
            if (rol === 'GUARDA' || rol === 'AMBOS') {
                const jaulasSeleccionadas = document.querySelectorAll('input[name="jaulas[]"]:checked');
                if (jaulasSeleccionadas.length === 0) {
                    e.preventDefault();
                    alert('Debes seleccionar al menos una jaula.');
                    return;
                }
            }
            
            // Validación Camino
            if (rol === 'SUPERVISOR' || rol === 'AMBOS') {
                const camino = document.querySelector('select[name="camino"]').value;
                if (!camino) {
                    e.preventDefault();
                    alert('Debes seleccionar un camino.');
                    return;
                }
            }
        });
    </script>
</body>
</html>