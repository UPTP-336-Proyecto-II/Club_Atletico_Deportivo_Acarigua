<?php

define('BASE_PATH', __DIR__ . '/');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Model.php';
require_once __DIR__ . '/app/Models/Atleta.php';
require_once __DIR__ . '/app/Models/MedidaAntropometrica.php';
require_once __DIR__ . '/app/Models/ResultadoPrueba.php';
require_once __DIR__ . '/app/Models/Asistencia.php';
require_once __DIR__ . '/app/Services/PdfGenerator.php';
require_once __DIR__ . '/app/Services/ReporteService.php';

// Mock DB connection if needed or just use the actual one if configured
try {
    $db = \App\Core\Database::connection();
    echo "DB Connected.\n";
    
    // Find an athlete ID
    $stmt = $db->query("SELECT atleta_id FROM atletas LIMIT 1");
    $atleta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($atleta) {
        $id = (int) $atleta['atleta_id'];
        echo "Testing with atleta_id = $id\n";
        
        $service = new \App\Services\ReporteService();
        $result = $service->fichaAtleta($id);
        
        if ($result) {
            echo "PDF Generated successfully!\n";
            echo "Filename: " . $result['filename'] . "\n";
            echo "Mime: " . $result['mime'] . "\n";
            echo "Size: " . strlen($result['content']) . " bytes\n";
            
            // Save to public dir for manual inspection if needed
            file_put_contents(__DIR__ . '/public/assets/uploads/test_ficha.pdf', $result['content']);
            echo "Saved to public/assets/uploads/test_ficha.pdf\n";
        } else {
            echo "Failed to generate PDF (fichaAtleta returned null).\n";
        }
    } else {
        echo "No athletes found in DB.\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
