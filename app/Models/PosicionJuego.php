<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PosicionJuego extends Model
{
    protected string $table = 'posiciones_juegos';
    protected string $primaryKey = 'posicion_id';
}
