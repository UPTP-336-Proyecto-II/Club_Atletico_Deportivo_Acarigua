<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use Throwable;

final class Configuracion
{
    /** @var array<string, string|null>|null Cache en memoria para las configuraciones */
    private static ?array $settings = null;

    /**
     * Carga todas las configuraciones en memoria.
     */
    private static function load(): void
    {
        if (self::$settings !== null) {
            return;
        }

        try {
            $db = Database::connection();
            $stmt = $db->query('SELECT clave, valor FROM configuraciones');
            $rows = $stmt->fetchAll();

            self::$settings = [];
            foreach ($rows as $row) {
                self::$settings[$row['clave']] = $row['valor'];
            }
        } catch (Throwable) {
            self::$settings = []; // Si hay error o no existe tabla, evita fallos continuos
        }
    }

    /**
     * Obtiene el valor de una configuración por su clave.
     * @param string $clave
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        self::load();
        return self::$settings[$clave] ?? $default;
    }

    /**
     * Actualiza múltiples configuraciones a la vez.
     * @param array<string, string> $data Arreglo asociativo de clave => valor
     * @return bool
     */
    public static function updateMany(array $data): bool
    {
        try {
            $db = Database::connection();
            $db->beginTransaction();

            $stmt = $db->prepare('UPDATE configuraciones SET valor = :valor WHERE clave = :clave');

            foreach ($data as $clave => $valor) {
                $stmt->execute([
                    ':valor' => $valor,
                    ':clave' => $clave
                ]);
            }

            $db->commit();
            self::$settings = null; // Invalida el caché para forzar recarga
            return true;
        } catch (Throwable) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
}
