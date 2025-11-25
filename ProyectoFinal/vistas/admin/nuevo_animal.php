<?php
// ============================================
// vistas/admin/nuevo_animal.php
// ============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Animal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="AdminController.php?action=animales">
                <i class="bi bi-arrow-left-circle"></i> Volver a Animales
            </a>
        </div>
    </nav>

    <div class="container" style="max-width: 700px;">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Registrar Nuevo Animal</h4>
            </div>
            <div class="card-body">
                <form action="AdminController.php?action=guardar_animal" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Identificación *</label>
                            <input type="text" name="numIdentif" class="form-control" required placeholder="Ej: A-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre Científico *</label>
                        <input type="text" name="nombre_cientifico" class="form-control" required placeholder="Ej: Panthera leo">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sexo *</label>
                            <select name="sexo" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="M">Macho</option>
                                <option value="H">Hembra</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" name="fechaNac" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jaula *</label>
                            <select name="numJaula" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($jaulas as $j): ?>
                                    <option value="<?php echo $j['numJaula']; ?>">
                                        Jaula #<?php echo $j['numJaula']; ?> - <?php echo htmlspecialchars($j['nombre'] ?? 'Sin nombre'); ?>
                                        (<?php echo htmlspecialchars($j['nombre_camino'] ?? 'Sin camino'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">País de Origen</label>
                            <select name="numPais" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($paises as $p): ?>
                                    <option value="<?php echo $p['numPais']; ?>">
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Registrar Animal
                        </button>
                        <a href="AdminController.php?action=animales" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>