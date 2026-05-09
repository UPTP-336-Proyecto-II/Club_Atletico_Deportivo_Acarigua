<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class FichaMedica extends Model
{
    protected string $table = 'fichas_medicas';
    protected string $primaryKey = 'ficha_id';

    public function byAtleta(int $atletaId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM fichas_medicas WHERE atleta_id = :a LIMIT 1',
            [':a' => $atletaId]
        );
    }
}
