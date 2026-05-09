<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PreguntaSeguridad extends Model
{
    protected string $table = 'preguntas_seguridad';
    protected string $primaryKey = 'pregunta_id';
}
