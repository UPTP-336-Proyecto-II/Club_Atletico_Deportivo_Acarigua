<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::connection();

        // Chequeo diario pasivo de inactividad de atletas (desactivación tras 2 meses sin asistencias)
        if (session_status() === PHP_SESSION_ACTIVE && ($_SESSION['last_inactivity_check'] ?? '') !== date('Y-m-d')) {
            try {
                $db->query("
                    UPDATE atletas 
                    SET estatus = 3 
                    WHERE estatus = 1 
                      AND creado_en <= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
                      AND atleta_id NOT IN (
                          SELECT DISTINCT a.atleta_id 
                          FROM asistencias a
                          INNER JOIN actividades act ON act.actividad_id = a.actividad_id
                          WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
                      )
                ");
                $_SESSION['last_inactivity_check'] = date('Y-m-d');
            } catch (\Throwable $e) {
                \App\Core\Logger::error($e);
            }
        }

        $atletas   = (int) $db->query('SELECT COUNT(*) FROM atletas')->fetchColumn();
        $activos   = (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus = 1")->fetchColumn();
        $categorias = (int) $db->query("SELECT COUNT(*) FROM categorias WHERE estatus = 1")->fetchColumn();
        $usuarios  = (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

        $dataCategorias = $db->query("
            SELECT c.nombre_categoria, COUNT(ac.atleta_id) AS total
            FROM categorias c
            LEFT JOIN asig_categorias ac ON ac.categoria_id = c.categoria_id
            GROUP BY c.categoria_id, c.nombre_categoria
            ORDER BY c.nombre_categoria
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $dataAsistencia = $db->query("
            SELECT c.nombre_categoria, 
                   DATE_FORMAT(act.fecha, '%Y-%m') AS mes,
                   COUNT(CASE WHEN a.estatus = 1 THEN 1 END) AS presentes,
                   COUNT(a.asistencia_id) AS total_registros
            FROM asistencias a
            INNER JOIN actividades act ON act.actividad_id = a.actividad_id
            INNER JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id
            INNER JOIN categorias c ON c.categoria_id = ac.categoria_id
            WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY c.categoria_id, c.nombre_categoria, mes
            ORDER BY mes ASC, c.nombre_categoria ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $dataDemografia = $db->query("
            SELECT a.sexo,
                   CASE 
                       WHEN TIMESTAMPDIFF(YEAR, a.fecha_nac, CURDATE()) < 10 THEN 'Sub-10'
                       WHEN TIMESTAMPDIFF(YEAR, a.fecha_nac, CURDATE()) BETWEEN 10 AND 12 THEN 'Sub-13'
                       WHEN TIMESTAMPDIFF(YEAR, a.fecha_nac, CURDATE()) BETWEEN 13 AND 15 THEN 'Sub-16'
                       ELSE 'Sub-20/Mayores'
                   END AS rango_edad,
                   COUNT(*) AS total
            FROM atletas a
            WHERE a.estatus = 1
            GROUP BY a.sexo, rango_edad
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $dataActividades = $db->query("
            SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                   tipo_actividad,
                   COUNT(*) AS total
            FROM actividades
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY mes, tipo_actividad
            ORDER BY mes ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $dataEntrenadores = $db->query("
            SELECT CONCAT_WS(' ', u.nombre, u.apellido) AS entrenador,
                   COUNT(ac.atleta_id) AS total_atletas
            FROM usuarios u
            INNER JOIN categorias c ON c.usuario_id = u.usuario_id
            INNER JOIN asig_categorias ac ON ac.categoria_id = c.categoria_id
            WHERE u.rol_id = 3
            GROUP BY u.usuario_id, entrenador
            ORDER BY total_atletas DESC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('dashboard.index', [
            'title'      => 'Inicio',
            'active'     => 'inicio',
            'breadcrumb' => ['Inicio'],
            'stats'      => ['atletas' => $atletas, 'activos' => $activos, 'categorias' => $categorias, 'usuarios' => $usuarios],
            'dataCategorias' => $dataCategorias,
            'dataAsistencia' => $dataAsistencia,
            'dataDemografia' => $dataDemografia,
            'dataActividades' => $dataActividades,
            'dataEntrenadores' => $dataEntrenadores,
        ], 'admin');
    }
}
