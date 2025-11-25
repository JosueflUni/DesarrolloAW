<?php require_once __DIR__ . '/../../config/session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Animal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 700px;">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Editar Animal: <?php echo htmlspecialchars($animal['nombre']); ?></h4>
            </div>
            <div class="card-body">
                <form action="AdminController.php?action=actualizar_animal" method="POST">
                    <div class="mb-3">
                        <label class="form-label">ID Identificación (No editable)</label>
                        <input type="text" class="form-control bg-light" name="numIdentif" value="<?php echo htmlspecialchars($animal['numIdentif']); ?>" readonly>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($animal['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre Científico</label>
                            <input type="text" name="nombre_cientifico" class="form-control" value="<?php echo htmlspecialchars($animal['nombre_cientifico']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sexo</label>
                            <select name="sexo" class="form-select" required>
                                <option value="M" <?php echo $animal['sexo'] === 'M' ? 'selected' : ''; ?>>Macho</option>
                                <option value="H" <?php echo $animal['sexo'] === 'H' ? 'selected' : ''; ?>>Hembra</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" name="fechaNac" class="form-control" value="<?php echo $animal['fechaNac']; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jaula Asignada</label>
                            <select name="numJaula" class="form-select" required>
                                <?php foreach ($jaulas as $j): ?>
                                    <option value="<?php echo $j['numJaula']; ?>" <?php echo $animal['numJaula'] == $j['numJaula'] ? 'selected' : ''; ?>>
                                        Jaula #<?php echo $j['numJaula']; ?> (<?php echo htmlspecialchars($j['nombre']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">País de Origen</label>
                            <select name="numPais" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($paises as $p): ?>
                                    <option value="<?php echo $p['numPais']; ?>" <?php echo $animal['numPais'] == $p['numPais'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                        <a href="AdminController.php?action=animales" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>