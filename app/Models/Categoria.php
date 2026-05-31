<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `categorias` de cada_db.
 *
 * La FK usuario_id apunta a usuarios.usuario_id (entrenador asignado).
 */
final class Categoria extends Model
{
    protected string $table = 'categorias';
    protected string $primaryKey = 'categoria_id';

    public function allWithEntrenador(array $filters = []): array
    {
        $sql = "SELECT c.*,
                       CONCAT_WS(' ', u.nombre, u.apellido) AS entrenador,
                       u.foto AS entrenador_foto,
                       (SELECT COUNT(*) FROM asig_categorias ac WHERE ac.categoria_id = c.categoria_id) AS total_atletas
                FROM categorias c
                LEFT JOIN usuarios u ON u.usuario_id = c.usuario_id";

        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "c.nombre_categoria LIKE :q";
            $params[':q'] = "%" . $filters['q'] . "%";
        }

        if (!empty($filters['sexo'])) {
            $where[] = "c.sexo_categoria = :sexo";
            $params[':sexo'] = $filters['sexo'];
        }

        if (!empty($filters['entrenador_id'])) {
            $where[] = "c.usuario_id = :entrenador_id";
            $params[':entrenador_id'] = (int) $filters['entrenador_id'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY c.edad_min";

        return $this->query($sql, $params);
    }

    public function activas(): array
    {
        return $this->query(
            "SELECT categoria_id, nombre_categoria FROM categorias WHERE estatus = 1 ORDER BY edad_min"
        );
    }
}
