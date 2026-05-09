<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Atleta;
use App\Models\ResultadoPrueba;

final class ResultadosPruebasController extends Controller
{
    public function index(Request $request): Response
    {
        $pag = (new Atleta())->paginate(['estatus' => 1], (int) $request->query('page', 1), 20);
        return $this->view('resultados_pruebas.index', [
            'title' => 'Pruebas físicas',
            'active' => 'pruebas',
            'breadcrumb' => ['Inicio', 'Reportes', 'Pruebas físicas'],
            'pag' => $pag,
        ], 'admin');
    }

    public function atleta(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) { flash('error', 'No encontrado.'); return $this->redirect('/admin/pruebas'); }
        return $this->view('resultados_pruebas.atleta', [
            'title' => 'Pruebas - ' . $atleta['nombre'],
            'active' => 'pruebas',
            'breadcrumb' => ['Inicio', 'Pruebas', $atleta['nombre']],
            'atleta' => $atleta,
            'historial' => (new ResultadoPrueba())->historial($id),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $id = (int) $request->param('id');
        $eventoId = (int) $request->input('evento_id', 0);

        // Si no hay evento, crear uno de tipo "Pruebas" con fecha hoy y entrenador del usuario actual (fallback)
        if (!$eventoId) {
            $db = Database::connection();
            $entrenadorId = (int) $request->input('entrenador_id', 0);
            if (!$entrenadorId) {
                $entrenadorId = (int) $db->query("SELECT usuario_id FROM usuarios WHERE rol_id = " . ROL_ENTRENADOR . " LIMIT 1")->fetchColumn();
            }
            if ($entrenadorId) {
                $stmt = $db->prepare("INSERT INTO actividades (usuario_id, tipo_actividad, fecha)
                                      VALUES (:e, 1, :f)"); // 1: Entrenamiento/Pruebas
                $stmt->execute([':e' => $entrenadorId, ':f' => date('Y-m-d')]);
                $eventoId = (int) $db->lastInsertId();
            }
        }
        if (!$eventoId) {
            flash('error', 'Se requiere un evento y un entrenador.');
            return $this->redirect("/admin/pruebas/atleta/$id");
        }

        $data = [
            'actividad_id'      => $eventoId,
            'atleta_id'         => $id,
            'test_de_fuerza'    => $this->num($request->input('test_de_fuerza')),
            'test_resistencia'  => $this->num($request->input('test_resistencia')),
            'test_velocidad'    => $this->num($request->input('test_velocidad')),
            'test_coordinacion' => $this->num($request->input('test_coordinacion')),
            'test_de_reaccion'  => $this->num($request->input('test_de_reaccion')),
        ];
        try {
            (new ResultadoPrueba())->insert($data);
            flash('success', 'Prueba registrada.');
        } catch (\Throwable $e) {
            flash('error', 'No se pudo registrar: ' . $e->getMessage());
        }
        return $this->redirect("/admin/pruebas/atleta/$id");
    }

    private function num(mixed $v): ?float
    {
        if ($v === '' || $v === null) return null;
        return is_numeric($v) ? (float) $v : null;
    }
}
