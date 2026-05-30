<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AsigCategoria extends Model
{
    protected string $table = 'asig_categorias';
    protected string $primaryKey = 'asignacion_id';

    /**
     * Obtiene todos los atletas asignados a una categoría específica,
     * trayendo sus datos personales, su posición de juego y sincronizando su estatus.
     */
    public function assignedAthletes(int $categoriaId): array
    {
        // Obtener límites de edad de la categoría
        $stmtCat = $this->db()->prepare("SELECT edad_min, edad_max FROM categorias WHERE categoria_id = :catId");
        $stmtCat->execute([':catId' => $categoriaId]);
        $cat = $stmtCat->fetch(\PDO::FETCH_ASSOC);

        $atletas = $this->query(
            "SELECT ac.*, a.nombre, a.apellido, a.cedula, a.foto, a.fecha_nac,
                    p1.nombre_posicion AS posicion_principal,
                    p2.nombre_posicion AS posicion_secundaria
             FROM asig_categorias ac
             JOIN atletas a ON a.atleta_id = ac.atleta_id
             LEFT JOIN posiciones_juegos p1 ON p1.posicion_id = ac.posicion_principal_id
             LEFT JOIN posiciones_juegos p2 ON p2.posicion_id = ac.posicion_secundaria_id
             WHERE ac.categoria_id = :catId
             ORDER BY a.apellido, a.nombre",
            [':catId' => $categoriaId]
        );

        if ($cat) {
            $edadMin = (int) $cat['edad_min'];
            $edadMax = (int) $cat['edad_max'];
            $hoy = new \DateTime();

            foreach ($atletas as &$a) {
                $nac = new \DateTime($a['fecha_nac'] ?? 'today');
                $edad = $hoy->diff($nac)->y;

                // Si la edad está dentro de los límites = 1 (vigente), sino = 2 (vencido)
                $nuevoEstatus = ($edad >= $edadMin && $edad <= $edadMax) ? 1 : 2;

                if ((int) ($a['estatus'] ?? 1) !== $nuevoEstatus) {
                    $this->updateStatus((int) $a['asignacion_id'], $nuevoEstatus);
                    $a['estatus'] = $nuevoEstatus;
                }
            }
        }

        return $atletas;
    }

    /**
     * Actualiza el campo estatus de una asignación específica en la base de datos.
     */
    public function updateStatus(int $asignacionId, int $estatus): bool
    {
        $stmt = $this->db()->prepare("UPDATE asig_categorias SET estatus = :estatus WHERE asignacion_id = :id");
        return $stmt->execute([':estatus' => $estatus, ':id' => $asignacionId]);
    }

    /**
     * Verifica si un dorsal ya está ocupado en una categoría determinada.
     * Permite excluir una asignación en caso de que estemos editándola.
     */
    public function checkDorsalExists(int $categoriaId, int $dorsal, ?int $excludeAsignacionId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM asig_categorias WHERE categoria_id = :catId AND nun_dorsal = :dorsal";
        $params = [':catId' => $categoriaId, ':dorsal' => $dorsal];
        if ($excludeAsignacionId !== null) {
            $sql .= " AND asignacion_id != :exId";
            $params[':exId'] = $excludeAsignacionId;
        }
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene las asignaciones de categorías de un atleta específico.
     */
    public function athleteAssignments(int $atletaId): array
    {
        $assignments = $this->query(
            "SELECT ac.*, c.nombre_categoria, c.edad_min, c.edad_max, a.fecha_nac,
                    p1.nombre_posicion AS posicion_principal,
                    p2.nombre_posicion AS posicion_secundaria
             FROM asig_categorias ac
             JOIN categorias c ON c.categoria_id = ac.categoria_id
             JOIN atletas a ON a.atleta_id = ac.atleta_id
             LEFT JOIN posiciones_juegos p1 ON p1.posicion_id = ac.posicion_principal_id
             LEFT JOIN posiciones_juegos p2 ON p2.posicion_id = ac.posicion_secundaria_id
             WHERE ac.atleta_id = :atletaId
             ORDER BY c.nombre_categoria",
            [':atletaId' => $atletaId]
        );

        $hoy = new \DateTime();
        foreach ($assignments as &$asig) {
            $nac = new \DateTime($asig['fecha_nac'] ?? 'today');
            $edad = $hoy->diff($nac)->y;
            $edadMin = (int) $asig['edad_min'];
            $edadMax = (int) $asig['edad_max'];

            $nuevoEstatus = ($edad >= $edadMin && $edad <= $edadMax) ? 1 : 2;

            if ((int) ($asig['estatus'] ?? 1) !== $nuevoEstatus) {
                $this->updateStatus((int) $asig['asignacion_id'], $nuevoEstatus);
                $asig['estatus'] = $nuevoEstatus;
            }
        }

        return $assignments;
    }
}
