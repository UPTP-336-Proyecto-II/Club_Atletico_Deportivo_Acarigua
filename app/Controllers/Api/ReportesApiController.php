<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class ReportesApiController extends Controller
{
    public function resumen(Request $request): Response
    {
        $db = Database::connection();
        return $this->json([
            'atletas'         => (int) $db->query('SELECT COUNT(*) FROM atletas')->fetchColumn(),
            'activos'         => (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus='1'")->fetchColumn(),
            'categorias'      => (int) $db->query("SELECT COUNT(*) FROM categorias WHERE estatus='activa'")->fetchColumn(),
            'usuarios'        => (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
            'eventos_30dias'  => (int) $db->query("SELECT COUNT(*) FROM actividades WHERE fecha >= (CURDATE() - INTERVAL 30 DAY)")->fetchColumn(),
            'por_estatus'     => $db->query("SELECT estatus, COUNT(*) AS total FROM atletas GROUP BY estatus")->fetchAll(),
            'por_categoria'   => $db->query("SELECT c.nombre_categoria, COUNT(a.atleta_id) AS total
                                             FROM categorias c
                                             LEFT JOIN atletas a ON a.categoria_id = c.categoria_id
                                             WHERE c.estatus='activa'
                                             GROUP BY c.categoria_id ORDER BY c.edad_min")->fetchAll(),
        ]);
    }
}
