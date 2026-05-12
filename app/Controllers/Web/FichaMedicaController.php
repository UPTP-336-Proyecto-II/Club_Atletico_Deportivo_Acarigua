<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Atleta;
use App\Models\FichaMedica;

final class FichaMedicaController extends Controller
{
    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) { flash('error', 'No encontrado.'); return $this->redirect('/admin/atletas'); }
        return $this->view('ficha_medica.show', [
            'title' => 'Ficha médica - ' . $atleta['nombre'],
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', $atleta['nombre'], 'Ficha médica'],
            'atleta' => $atleta,
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $model = new FichaMedica();
        $existente = $model->byAtleta($id);
        $payload = [
            'alergias'                 => trim((string) $request->input('alergias', '')),
            'grupo_sanguineo'          => trim((string) $request->input('grupo_sanguineo', '')),
            'antecedentes_familiares'  => trim((string) $request->input('antecedentes_familiares', '')),
            'antecedentes_quirurgicos' => trim((string) $request->input('antecedentes_quirurgicos', '')),
            'condicion_cronica'        => trim((string) $request->input('condicion_cronica', '')),
            'medicacion_actual'        => trim((string) $request->input('medicacion_actual', '')),
        ];
        if ($existente) {
            $model->update((int) $existente['ficha_id'], $payload);
        } else {
            $model->insert(['atleta_id' => $id] + $payload);
        }
        flash('success', 'Ficha médica guardada correctamente.');
        return $this->redirect("/admin/atletas/$id");
    }
}
