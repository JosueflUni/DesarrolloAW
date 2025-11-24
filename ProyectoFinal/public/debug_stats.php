<?php
// public/debug_stats.php
require_once __DIR__ . '/../config/database.php';

echo "<h1>Diagnóstico de Estadísticas Supervisor</h1>";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Verificar si la Vista existe y es accesible
    echo "<h3>1. Verificando Vista de Alertas...</h3>";
    try {
        $check = $db->query("SELECT COUNT(*) FROM VistaAnimalesConAlertas");
        echo "<p style='color:green'>OK. La vista existe y tiene " . $check->fetchColumn() . " registros.</p>";
    } catch (PDOException $e) {
        die("<h3 style='color:red'>ERROR CRÍTICO EN VISTA: " . $e->getMessage() . "</h3>");
    }

    // 2. Probar la consulta de estadísticas (hardcodeada al camino 1)
    echo "<h3>2. Probando consulta de estadísticas (Camino ID: 1)...</h3>";
    
    $query = "SELECT 
                (SELECT COUNT(*) FROM Jaulas WHERE numCamino = 1) AS total_jaulas,
                (SELECT COUNT(DISTINCT a.numIdentif)
                 FROM Animales a
                 INNER JOIN Jaulas j ON a.numJaula = j.numJaula
                 INNER JOIN VistaAnimalesConAlertas vaa ON a.numIdentif = vaa.numIdentif
                 WHERE j.numCamino = 1 AND vaa.nivel_alerta = 'CRITICO') AS animales_criticos";

    $stmt = $db->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p style='color:green'>¡Consulta Exitosa!</p>";
    echo "<pre>" . print_r($result, true) . "</pre>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>ERROR ENCONTRADO:</h3>";
    echo "<div style='background:#f8d7da; padding:15px; border:1px solid red;'>";
    echo "<strong>Código:</strong> " . $e->getCode() . "<br>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage();
    echo "</div>";
    
    if (strpos($e->getMessage(), 'collation') !== false) {
        echo "<p><strong>Solución sugerida:</strong> El conflicto de idiomas persiste. Ejecuta el SQL de reparación de Collation nuevamente.</p>";
    }
    if (strpos($e->getMessage(), 'view') !== false) {
        echo "<p><strong>Solución sugerida:</strong> La vista está rota. Ejecuta el SQL de 'CREATE VIEW' nuevamente.</p>";
    }
}
?>