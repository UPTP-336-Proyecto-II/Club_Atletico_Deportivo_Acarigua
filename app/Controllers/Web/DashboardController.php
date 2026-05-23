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
                          SELECT DISTINCT atleta_id 
                          FROM asistencias 
                          WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
                      )
                ");
                $_SESSION['last_inactivity_check'] = date('Y-m-d');
            } catch (\Throwable $e) {
                \App\Core\Logger::error($e);
            }
        }

        $atletas   = (int) $db->query('SELECT COUNT(*) FROM atletas')->fetchColumn();
        $activos   = (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus = 1")->fetchColumn();
        $categorias = (int) $db->query("SELECT COUNT(*) FROM categorias WHERE estatus = 'activa'")->fetchColumn();
        $usuarios  = (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

        return $this->view('dashboard.index', [
            'title'      => 'Inicio',
            'active'     => 'inicio',
            'breadcrumb' => ['Inicio'],
            'stats'      => ['atletas' => $atletas, 'activos' => $activos, 'categorias' => $categorias, 'usuarios' => $usuarios],
        ], 'admin');
    }
}
