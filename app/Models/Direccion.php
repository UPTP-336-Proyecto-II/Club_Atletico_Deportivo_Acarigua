<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Direccion extends Model
{
    protected string $table = 'direcciones';
    protected string $primaryKey = 'direccion_id';

    public function paises(): array
    {
        return [['id' => 1, 'nombre' => 'Venezuela']]; // Hardcoded default
    }

    public function estados(int $paisId = 1): array
    {
        return $this->query('SELECT estado_id, estado FROM estados ORDER BY estado');
    }

    public function municipios(int $estadoId): array
    {
        return $this->query(
            'SELECT municipio_id, municipio FROM municipios WHERE estado_id = :e ORDER BY municipio',
            [':e' => $estadoId]
        );
    }

    public function parroquias(int $municipioId): array
    {
        return $this->query(
            'SELECT parroquia_id, parroquia FROM parroquias WHERE municipio_id = :m ORDER BY parroquia',
            [':m' => $municipioId]
        );
    }
}
