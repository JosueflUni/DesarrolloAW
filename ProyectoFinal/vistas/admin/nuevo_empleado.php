<?php
// vistas/admin/nuevo_empleado.php - FORMULARIO COMPLETO CON ASIGNACIONES
require_once __DIR__ . '/../../config/session.php';
?>
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
                    
                    <!-- Información Personal -->
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
                            <div class="form-text">Opcional, para notificaciones del sistema</div>
                        </div>
                    </div>

                    <!-- Credenciales -->
                    <div class="form-section">
                        <h5 class="section-title"><i class="bi bi-key"></i> Credenciales de Acceso</h5>
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario *</label>
                            <input type="text" name="usuario" class="form-control" required>
                            <div class="form-text">Será usado para iniciar sesión</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="contrasena" class="form-control" required minlength="6">
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                    </div>

                    <!-- Rol y Asignaciones -->
                    <div class="form-section">
                        <h5 class="section-title"><i class="bi bi-shield-check"></i> Rol y Asignaciones</h5>
                        <div class="mb-3">
                            <label class="form-label">Rol del Sistema *</label>
                            <select name="rol" id="selectRol" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="GUARDA">Guarda</option>
                                <option value="SUPERVISOR">Supervisor</option>
                                <option value="ADMIN">Administrador</option>
                            </select>
                        </div>

                        <!-- Asignación de Jaulas (solo para Guarda) -->
                        <div id="seccionJaulas" style="display:none;">
                            <label class="form-label">Jaulas Asignadas</label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                <?php if (empty($jaulas)): ?>
                                    <p class="text-muted">No hay jaulas disponibles</p>
                                <?php else: ?>
                                    <?php 
                                    $caminoActual = null;
                                    foreach ($jaulas as $jaula): 
                                        if ($caminoActual !== $jaula['numCamino']) {
                                            if ($caminoActual !== null) echo '</div>';
                                            $caminoActual = $jaula['numCamino'];
                                            echo '<div class="mb-3">';
                                            echo '<h6 class="text-primary mb-2"><i class="bi bi-signpost-2"></i> ' . htmlspecialchars($jaula['nombre_camino'] ?? "Camino {$jaula['numCamino']}") . '</h6>';
                                        }
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jaulas[]" value="<?php echo $jaula['numJaula']; ?>" id="jaula<?php echo $jaula['numJaula']; ?>">
                                            <label class="form-check-label" for="jaula<?php echo $jaula['numJaula']; ?>">
                                                Jaula #<?php echo $jaula['numJaula']; ?> - <?php echo htmlspecialchars($jaula['nombre'] ?? 'Sin nombre'); ?>
                                            </label>
                                        </div>
                                    <?php 
                                    endforeach; 
                                    if ($caminoActual !== null) echo '</div>';
                                    ?>
                                <?php endif; ?>
                            </div>
                            <div class="form-text mt-2">Selecciona las jaulas que este guarda supervisará</div>
                        </div>

                        <!-- Asignación de Camino (solo para Supervisor) -->
                        <div id="seccionCamino" style="display:none;">
                            <label class="form-label">Camino Asignado</label>
                            <select name="camino" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($caminos as $camino): ?>
                                    <option value="<?php echo $camino['numCamino']; ?>">
                                        Camino #<?php echo $camino['numCamino']; ?> - <?php echo htmlspecialchars($camino['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">El supervisor tendrá acceso a todas las jaulas de este camino</div>
                        </div>

                        <!-- Info para Admin -->
                        <div id="seccionAdmin" style="display:none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Los administradores tienen acceso completo al sistema y no requieren asignaciones específicas.
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
            
            // Ocultar todas las secciones
            document.getElementById('seccionJaulas').style.display = 'none';
            document.getElementById('seccionCamino').style.display = 'none';
            document.getElementById('seccionAdmin').style.display = 'none';
            
            // Mostrar la sección correspondiente
            if (rol === 'GUARDA') {
                document.getElementById('seccionJaulas').style.display = 'block';
            } else if (rol === 'SUPERVISOR') {
                document.getElementById('seccionCamino').style.display = 'block';
            } else if (rol === 'ADMIN') {
                document.getElementById('seccionAdmin').style.display = 'block';
            }
        });

        // Validación antes de enviar
        document.getElementById('formEmpleado').addEventListener('submit', function(e) {
            const rol = document.getElementById('selectRol').value;
            
            if (rol === 'GUARDA') {
                const jaulasSeleccionadas = document.querySelectorAll('input[name="jaulas[]"]:checked');
                if (jaulasSeleccionadas.length === 0) {
                    e.preventDefault();
                    alert('Por favor selecciona al menos una jaula para el guarda');
                }
            } else if (rol === 'SUPERVISOR') {
                const camino = document.querySelector('select[name="camino"]').value;
                if (!camino) {
                    e.preventDefault();
                    alert('Por favor selecciona un camino para el supervisor');
                }
            }
        });
    </script>
</body>
</html>