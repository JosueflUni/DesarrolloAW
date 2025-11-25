<?php require_once __DIR__ . '/../../config/session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 800px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white"><h4>Editar Empleado: <?php echo htmlspecialchars($empleado['nombreEmpleado']); ?></h4></div>
            <div class="card-body">
                <?php $flash = SessionManager::getFlash(); if ($flash): ?><div class="alert alert-danger"><?php echo $flash['mensaje']; ?></div><?php endif; ?>
                
                <form action="AdminController.php?action=actualizar_empleado" method="POST" id="formEmpleado">
                    <input type="hidden" name="nombreEmpleado" value="<?php echo htmlspecialchars($empleado['nombreEmpleado']); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($empleado['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($empleado['apellido']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($empleado['email']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña (Opcional)</label>
                        <input type="password" name="nueva_contrasena" class="form-control" minlength="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" id="selectRol" class="form-select" required>
                            <option value="GUARDA" <?php echo $empleado['rol_base'] === 'GUARDA' ? 'selected' : ''; ?>>Guarda</option>
                            <option value="SUPERVISOR" <?php echo $empleado['rol_base'] === 'SUPERVISOR' ? 'selected' : ''; ?>>Supervisor</option>
                            <option value="AMBOS" <?php echo $empleado['rol_base'] === 'AMBOS' ? 'selected' : ''; ?>>Ambos (Guarda + Supervisor)</option>
                            <option value="ADMIN" <?php echo $empleado['rol_base'] === 'ADMIN' ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>

                    <div id="seccionJaulas" class="mb-3" style="display: <?php echo ($empleado['rol_base'] === 'GUARDA' || $empleado['rol_base'] === 'AMBOS') ? 'block' : 'none'; ?>;">
                        <label class="form-label">Jaulas Asignadas</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($jaulas as $j): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jaulas[]" value="<?php echo $j['numJaula']; ?>" 
                                        <?php echo in_array($j['numJaula'], $jaulasAsignadas) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Jaula #<?php echo $j['numJaula']; ?> (<?php echo htmlspecialchars($j['nombre_camino']); ?>)</label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="seccionCamino" class="mb-3" style="display: <?php echo ($empleado['rol_base'] === 'SUPERVISOR' || $empleado['rol_base'] === 'AMBOS') ? 'block' : 'none'; ?>;">
                        <label class="form-label">Camino Asignado</label>
                        <select name="camino" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($caminos as $c): ?>
                                <option value="<?php echo $c['numCamino']; ?>" 
                                    <?php echo ($caminoAsignado == $c['numCamino']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" <?php echo $empleado['activo'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Usuario Activo</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="AdminController.php?action=usuarios" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('selectRol').addEventListener('change', function() {
            const rol = this.value;
            const sJaulas = document.getElementById('seccionJaulas');
            const sCamino = document.getElementById('seccionCamino');
            
            sJaulas.style.display = 'none';
            sCamino.style.display = 'none';
            
            if (rol === 'GUARDA') sJaulas.style.display = 'block';
            else if (rol === 'SUPERVISOR') sCamino.style.display = 'block';
            else if (rol === 'AMBOS') {
                sJaulas.style.display = 'block';
                sCamino.style.display = 'block';
            }
        });

        document.getElementById('formEmpleado').addEventListener('submit', function(e) {
            const rol = document.getElementById('selectRol').value;
            
            if (rol === 'GUARDA' || rol === 'AMBOS') {
                if (document.querySelectorAll('input[name="jaulas[]"]:checked').length === 0) {
                    e.preventDefault(); alert('Selecciona al menos una jaula.');
                }
            }
            if (rol === 'SUPERVISOR' || rol === 'AMBOS') {
                if (!document.querySelector('select[name="camino"]').value) {
                    e.preventDefault(); alert('Selecciona un camino.');
                }
            }
        });
    </script>
</body>
</html>