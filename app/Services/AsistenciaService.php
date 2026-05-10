<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Actividad;
use Throwable;

final class AsistenciaService
{
    /**
     * Registra un evento (entrenamiento/pruebas/etc.) y bulk insert de asistencias.
     *
     * @param array<int, array{atleta_id:int, estatus:int|string, observaciones?:string}> $detalles
     */
    public function registrarPase(
        int $entrenadorId,
        string $tipoEvento,
        string $fechaEvento,
        array $detalles
    ): int {
        if (empty($detalles)) {
            throw new \RuntimeException('Debes marcar la asistencia de al menos un atleta.');
        }

        // Mapeo de tipos de evento a tinyint (según cada_db_clean.sql)
        $tipoMap = [
            'Partido'         => 0,
            'Entrenamiento'   => 1,
            'Pruebas'         => 1, // Se agrupan como entrenamiento o podrías definir 2
            'Evento especial' => 3
        ];
        $tipoId = $tipoMap[$tipoEvento] ?? 1;

        Database::beginTransaction();
        try {
            $eventoId = (new Actividad())->insert([
                'usuario_id'     => $entrenadorId,
                'tipo_actividad' => $tipoId,
                'fecha'          => $fechaEvento,
            ]);

            $stmt = Database::connection()->prepare(
                'INSERT INTO asistencias (actividad_id, atleta_id, estatus, observaciones)
                 VALUES (:e, :a, :s, :o)'
            );
            foreach ($detalles as $d) {
                // Mapeo de estatus: 1 si es Presente o 1, 0 en otro caso
                $est = ($d['estatus'] === 'Presente' || $d['estatus'] === 1 || $d['estatus'] === '1') ? 1 : 0;
                
                $stmt->execute([
                    ':e' => $eventoId,
                    ':a' => (int) $d['atleta_id'],
                    ':s' => $est,
                    ':o' => $d['observaciones'] ?? null,
                ]);
            }

            Database::commit();
            Logger::audit('asistencia.pase', ['actividad_id' => $eventoId, 'total' => count($detalles)]);
            return $eventoId;
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function actualizarPase(
        int $eventoId,
        int $entrenadorId,
        string $tipoEvento,
        string $fechaEvento,
        array $detalles
    ): void {
        $tipoMap = [
            'Partido'         => 0,
            'Entrenamiento'   => 1,
            'Pruebas'         => 1,
            'Evento especial' => 3
        ];
        $tipoId = $tipoMap[$tipoEvento] ?? 1;

        Database::beginTransaction();
        try {
            // Actualizar actividad
            (new Actividad())->update($eventoId, [
                'usuario_id'     => $entrenadorId,
                'tipo_actividad' => $tipoId,
                'fecha'          => $fechaEvento,
            ]);

            // Eliminar asistencias anteriores
            $db = Database::connection();
            $stmt = $db->prepare("DELETE FROM asistencias WHERE actividad_id = ?");
            $stmt->execute([$eventoId]);

            // Insertar nuevas
            $stmt = $db->prepare(
                'INSERT INTO asistencias (actividad_id, atleta_id, estatus, observaciones)
                 VALUES (:e, :a, :s, :o)'
            );
            foreach ($detalles as $d) {
                $est = ($d['estatus'] === 'Presente' || $d['estatus'] === 1 || $d['estatus'] === '1') ? 1 : 0;
                $stmt->execute([
                    ':e' => $eventoId,
                    ':a' => (int) $d['atleta_id'],
                    ':s' => $est,
                    ':o' => $d['observaciones'] ?? null,
                ]);
            }

            Database::commit();
            Logger::audit('asistencia.update', ['actividad_id' => $eventoId, 'total' => count($detalles)]);
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
