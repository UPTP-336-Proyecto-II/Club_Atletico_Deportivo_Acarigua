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

    public function allWithEntrenador(): array
    {
        return $this->query(
            "SELECT c.*,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS entrenador,
                    u.foto AS entrenador_foto,
                    (SELECT COUNT(*) FROM atletas a WHERE a.categoria_id = c.categoria_id) AS total_atletas
             FROM categorias c
             LEFT JOIN usuarios u ON u.usuario_id = c.usuario_id
             ORDER BY c.edad_min"
        );
    }

    public function activas(): array
    {
        return $this->query(
            "SELECT categoria_id, nombre_categoria FROM categorias WHERE estatus = 'activa' ORDER BY edad_min"
        );
    }
}
