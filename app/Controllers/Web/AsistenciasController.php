<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Categoria;
use App\Models\Usuario;
use App\Services\AsistenciaService;
use Throwable;

final class AsistenciasController extends Controller
{
    public function index(Request $request): Response
    {
        $hoy = date('Y-m-d');
        $eventos = Database::connection()->query(
            "SELECT a.actividad_id AS evento_id, a.tipo_actividad AS tipo_evento, a.fecha AS fecha_evento,
                    CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
                    (SELECT COUNT(*) FROM asistencias ast WHERE ast.actividad_id = a.actividad_id) AS total,
                    (SELECT COUNT(*) FROM asistencias ast WHERE ast.actividad_id = a.actividad_id AND ast.estatus = 1) AS presentes
             FROM actividades a
             LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
             ORDER BY a.fecha DESC, a.actividad_id DESC
             LIMIT 50"
        )->fetchAll();

        return $this->view('asistencias.index', [
            'title' => 'Asistencia',
            'active' => 'asistencias',
            'breadcrumb' => ['Inicio', 'Asistencia'],
            'eventos' => $eventos,
            'hoy' => $hoy,
        ], 'admin');
    }

    public function crear(Request $request): Response
    {
        $categorias = (new Categoria())->activas();
        $entrenadores = (new Usuario())->entrenadores();
        return $this->view('asistencias.crear', [
            'title' => 'Asistencia',
            'active' => 'asistencias',
            'breadcrumb' => ['Inicio', 'Evaluaciones', 'Asistencia'],
            'categorias' => $categorias,
            'entrenadores' => $entrenadores,
        ], 'admin');
    }

    public function guardar(Request $request): Response
    {
        $data = [
            'tipo_evento' => $request->input('tipo_evento', 'Entrenamiento'),
            'fecha_evento' => (string) $request->input('fecha_evento', date('Y-m-d')),
            'entrenador_id' => (int) $request->input('entrenador_id', 0),
            'categoria_id' => (int) $request->input('categoria_id', 0),
            'ubicacion' => $request->input('ubicacion') ?: 'Cancha UPTP',
            'clima' => $request->input('clima') !== '' ? (int) $request->input('clima') : null,
            'hora_inicio' => $request->input('hora_inicio') ?: null,
            'hora_fin' => $request->input('hora_fin') ?: null,
        ];
        $v = Validator::make($data, [
            'tipo_evento' => 'required',
            'fecha_evento' => 'required|date',
            'entrenador_id' => 'required|integer',
            'categoria_id' => 'required|integer',
        ]);
        if (!$v->validate()) {
            $this->withErrors($v->errors());
            return $this->redirect('/admin/asistencias/crear');
        }

        if (strtotime($data['fecha_evento']) > strtotime(date('Y-m-d'))) {
            flash('error', 'No se pueden registrar asistencias en fechas futuras.');
            return $this->redirect('/admin/asistencias/crear');
        }

        $atletaIds = (array) ($request->body('atletas') ?? []);
        $estatuses = (array) ($request->body('estatus') ?? []);
        $observaciones = (array) ($request->body('observaciones') ?? []);
        $detalles = [];
        foreach ($atletaIds as $aid) {
            $aid = (int) $aid;
            if (!$aid)
                continue;
            $detalles[] = [
                'atleta_id' => $aid,
                'estatus' => ($estatuses[$aid] ?? 'Ausente') === 'Presente' ? 1 : 0,
                'observaciones' => $observaciones[$aid] ?? null,
            ];
        }

        try {
            (new AsistenciaService())->registrar(
                $data['entrenador_id'],
                $data['tipo_evento'],
                $data['fecha_evento'],
                $detalles,
                $data['categoria_id'],
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['ubicacion'],
                $data['clima']
            );
            flash('success', 'Asistencia registrada correctamente.');
            return $this->redirect('/admin/asistencias');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'Error al guardar: ' . $e->getMessage());
            return $this->redirect('/admin/asistencias/crear');
        }
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        $actividad = $db->prepare(
            "SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) AS entrenador
             FROM actividades a
             LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
             WHERE a.actividad_id = ?"
        );
        $actividad->execute([$id]);
        $actividad = $actividad->fetch();

        if (!$actividad) {
            flash('error', 'Registro no encontrado.');
            return $this->redirect('/admin/asistencias');
        }

        $asistencias = $db->prepare(
            "SELECT ast.*, atl.nombre, atl.apellido, atl.cedula
             FROM asistencias ast
             JOIN atletas atl ON ast.atleta_id = atl.atleta_id
             WHERE ast.actividad_id = ?
             ORDER BY atl.apellido, atl.nombre"
        );
        $asistencias->execute([$id]);
        $detalles = $asistencias->fetchAll();

        return $this->view('asistencias.show', [
            'title' => 'Detalle de Asistencia',
            'active' => 'asistencias',
            'actividad' => $actividad,
            'detalles' => $detalles
        ], 'admin');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        $actividad = $db->prepare("SELECT * FROM actividades WHERE actividad_id = ?");
        $actividad->execute([$id]);
        $actividad = $actividad->fetch();

        if (!$actividad) {
            flash('error', 'Registro no encontrado.');
            return $this->redirect('/admin/asistencias');
        }

        // Restricción de 48 horas para entrenadores
        if (Auth::user()['rol_id'] == ROL_ENTRENADOR) {
            $fechaActividad = strtotime($actividad['fecha']);
            $limite = strtotime('+48 hours', $fechaActividad);
            if (time() > $limite) {
                flash('error', 'El tiempo permitido para editar esta asistencia (48 horas) ha expirado.');
                return $this->redirect('/admin/asistencias');
            }
        }

        $asistencias = $db->prepare(
            "SELECT ast.*, atl.nombre, atl.apellido, atl.cedula
             FROM asistencias ast
             JOIN atletas atl ON ast.atleta_id = atl.atleta_id
             WHERE ast.actividad_id = ?
             ORDER BY atl.apellido, atl.nombre"
        );
        $asistencias->execute([$id]);
        $detalles = $asistencias->fetchAll();

        $entrenadores = (new Usuario())->entrenadores();

        return $this->view('asistencias.edit', [
            'title' => 'Editar Asistencia',
            'active' => 'asistencias',
            'actividad' => $actividad,
            'detalles' => $detalles,
            'entrenadores' => $entrenadores
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $data = [
            'tipo_evento' => $request->input('tipo_evento', 'Entrenamiento'),
            'fecha_evento' => (string) $request->input('fecha_evento', date('Y-m-d')),
            'entrenador_id' => (int) $request->input('entrenador_id', 0),
            'ubicacion' => $request->input('ubicacion') ?: 'Cancha UPTP',
            'clima' => $request->input('clima') !== '' ? (int) $request->input('clima') : null,
            'hora_inicio' => $request->input('hora_inicio') ?: null,
            'hora_fin' => $request->input('hora_fin') ?: null,
        ];

        $v = Validator::make($data, [
            'tipo_evento' => 'required',
            'fecha_evento' => 'required|date',
            'entrenador_id' => 'required|integer',
        ]);

        if (!$v->validate()) {
            $this->withErrors($v->errors());
            return $this->redirect("/admin/asistencias/{$id}/editar");
        }

        if (strtotime($data['fecha_evento']) > strtotime(date('Y-m-d'))) {
            flash('error', 'No se pueden registrar asistencias en fechas futuras.');
            return $this->redirect("/admin/asistencias/{$id}/editar");
        }

        $atletaIds = (array) ($request->body('atletas') ?? []);
        $estatuses = (array) ($request->body('estatus') ?? []);
        $observaciones = (array) ($request->body('observaciones') ?? []);
        $detalles = [];

        foreach ($atletaIds as $aid) {
            $aid = (int) $aid;
            if (!$aid)
                continue;
            $detalles[] = [
                'atleta_id' => $aid,
                'estatus' => $estatuses[$aid] ?? 'Ausente',
                'observaciones' => $observaciones[$aid] ?? null,
            ];
        }

        try {
            (new AsistenciaService())->actualizar(
                $id,
                $data['entrenador_id'],
                $data['tipo_evento'],
                $data['fecha_evento'],
                $detalles,
                $data['hora_inicio'],
                $data['hora_fin'],
                $data['ubicacion'],
                $data['clima']
            );
            flash('success', 'Asistencia actualizada correctamente.');
            return $this->redirect('/admin/asistencias');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'Error al actualizar: ' . $e->getMessage());
            return $this->redirect("/admin/asistencias/{$id}/editar");
        }
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        try {
            $db = Database::connection();
            $db->beginTransaction();

            // Eliminar detalles de asistencia
            $stmt = $db->prepare("DELETE FROM asistencias WHERE actividad_id = ?");
            $stmt->execute([$id]);

            // Eliminar la actividad
            $stmt = $db->prepare("DELETE FROM actividades WHERE actividad_id = ?");
            $stmt->execute([$id]);

            $db->commit();
            flash('success', 'Registro de asistencia eliminado correctamente.');
        } catch (Throwable $e) {
            if ($db->inTransaction())
                $db->rollBack();
            Logger::error($e);
            flash('error', 'No se pudo eliminar el registro.');
        }

        return $this->redirect('/admin/asistencias');
    }
}
