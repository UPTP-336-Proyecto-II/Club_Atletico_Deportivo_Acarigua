<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class AsistenciasApiController extends Controller
{
    public function atletasCategoria(Request $request): Response
    {
        $categoriaId = (int) $request->param('id');
        
        // 1. Sincronizar dinámicamente los estatus de las asignaciones para esta categoría
        (new \App\Models\AsigCategoria())->assignedAthletes($categoriaId);

        // 2. Buscar atletas con asignación vigente (ac.estatus = 1)
        $stmt = Database::connection()->prepare(
            "SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.foto, a.estatus AS atleta_estatus
             FROM asig_categorias ac
             JOIN atletas a ON a.atleta_id = ac.atleta_id
             WHERE ac.categoria_id = :c AND ac.estatus = 1
             ORDER BY a.apellido, a.nombre"
        );
        $stmt->execute([':c' => $categoriaId]);
        return $this->json($stmt->fetchAll());
    }
}
