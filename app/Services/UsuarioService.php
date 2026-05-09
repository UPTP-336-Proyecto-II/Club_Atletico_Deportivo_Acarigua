<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Usuario;
use App\Models\Direccion;
use RuntimeException;
use Throwable;

/**
 * Encapsula la creación/actualización de usuarios con sus entidades
 * relacionadas (dirección y foto) en una única transacción.
 */
final class UsuarioService
{
    public function crear(array $data, array $fotoFile = []): int
    {
        Database::beginTransaction();
        try {
            $direccionId = $this->guardarDireccion($data);
            $fotoPath    = $this->guardarFoto($fotoFile);

            $usuario = new Usuario();
            $usuarioId = $usuario->insert([
                'nombre'       => $data['nombre'],
                'apellido'     => $data['apellido'],
                'cedula'       => $data['cedula'],
                'telefono'     => $data['telefono'],
                'fecha_nac'    => $data['fecha_nac'] ?: null,
                'correo'       => $data['correo'],
                'contrasena'   => password_hash(preg_replace('/[^0-9]/', '', $data['cedula']), PASSWORD_BCRYPT, ['cost' => 12]),
                'rol_id'       => $data['rol_id'],
                'estatus'      => $data['estatus'] ?? 'Activo',
                'direccion_id' => $direccionId,
                'foto'         => $fotoPath,
            ]);

            Database::commit();
            Logger::audit('usuario.crear', ['usuario_id' => $usuarioId]);
            return $usuarioId;
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $usuarioId, array $data, array $fotoFile = []): void
    {
        $usuario = new Usuario();
        $actual = $usuario->find($usuarioId);
        if (!$actual) {
            throw new RuntimeException('Usuario no encontrado.');
        }

        Database::beginTransaction();
        try {
            // Dirección: reutilizar existente o crear nueva
            $direccionId = $actual['direccion_id'] ?? null;
            if ($direccionId) {
                (new Direccion())->update((int) $direccionId, [
                    'parroquias_id'     => $data['parroquia_id'] ?? null,
                    'localidad'         => $data['localidad'] ?? null,
                    'tipo_vivienda'     => $data['tipo_vivienda'] ?? null,
                    'ubicacion_vivienda'=> $data['ubicacion_vivienda'] ?? null,
                ]);
            } else {
                $direccionId = $this->guardarDireccion($data);
            }

            $update = [
                'nombre'       => $data['nombre'],
                'apellido'     => $data['apellido'],
                'cedula'       => $data['cedula'],
                'telefono'     => $data['telefono'],
                'fecha_nac'    => $data['fecha_nac'] ?: null,
                'correo'       => $data['correo'],
                'rol_id'       => $data['rol_id'],
                'estatus'      => $data['estatus'] ?? 'Activo',
                'direccion_id' => $direccionId,
            ];
            $nuevaFoto = $this->guardarFoto($fotoFile);
            if ($nuevaFoto !== null) {
                $update['foto'] = $nuevaFoto;
            }
            $usuario->update($usuarioId, $update);

            Database::commit();
            Logger::audit('usuario.actualizar', ['usuario_id' => $usuarioId]);
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    private function guardarDireccion(array $data): int
    {
        return (new Direccion())->insert([
            'parroquias_id'     => $data['parroquia_id'] ?? 1,
            'localidad'         => $data['localidad'] ?? '',
            'tipo_vivienda'     => $data['tipo_vivienda'] ?? 'casa',
            'ubicacion_vivienda'=> $data['ubicacion_vivienda'] ?? '',
        ]);
    }

    private function guardarFoto(array $file): ?string
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir la foto (código ' . $file['error'] . ').');
        }
        $maxSize = (int) config('app.uploads.max_size');
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('La foto excede el tamaño máximo permitido.');
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) finfo_close($finfo);
        $allowed = config('app.uploads.allowed_mime') ?? [];
        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Tipo de archivo no permitido. Usa JPG, PNG o WebP.');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'bin',
        };
        $basename = bin2hex(random_bytes(8)) . '.' . $ext;
        $dir = BASE_PATH . '/public' . config('app.uploads.personal_dir', '/uploads/usuarios');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $dest = $dir . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('No se pudo guardar la foto.');
        }
        return config('app.uploads.personal_dir', '/uploads/usuarios') . '/' . $basename;
    }
}
