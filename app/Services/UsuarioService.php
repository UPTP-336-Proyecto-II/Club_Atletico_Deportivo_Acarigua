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
        $actual = $usuario->findCompleto($usuarioId);
        if (!$actual) {
            throw new RuntimeException('Usuario no encontrado.');
        }

        Database::beginTransaction();
        try {
            // Fusionar datos actuales con los nuevos provistos para evitar anular campos
            $nombre = array_key_exists('nombre', $data) ? $data['nombre'] : $actual['nombre'];
            $apellido = array_key_exists('apellido', $data) ? $data['apellido'] : $actual['apellido'];
            $cedula = array_key_exists('cedula', $data) ? $data['cedula'] : $actual['cedula'];
            $telefono = array_key_exists('telefono', $data) ? $data['telefono'] : $actual['telefono'];
            $fechaNac = array_key_exists('fecha_nac', $data) ? $data['fecha_nac'] : $actual['fecha_nac'];
            $correo = array_key_exists('correo', $data) ? $data['correo'] : $actual['correo'];
            $rolId = array_key_exists('rol_id', $data) ? $data['rol_id'] : $actual['rol_id'];
            $estatus = array_key_exists('estatus', $data) ? $data['estatus'] : $actual['estatus'];

            // Dirección
            $direccionId = $actual['direccion_id'] ?? null;
            $parroquiaId = array_key_exists('parroquia_id', $data) ? $data['parroquia_id'] : ($actual['parroquias_id'] ?? null);
            $localidad = array_key_exists('localidad', $data) ? $data['localidad'] : ($actual['localidad'] ?? null);
            $tipoVivienda = array_key_exists('tipo_vivienda', $data) ? $data['tipo_vivienda'] : ($actual['tipo_vivienda'] ?? null);
            $ubicacionVivienda = array_key_exists('ubicacion_vivienda', $data) ? $data['ubicacion_vivienda'] : ($actual['ubicacion_vivienda'] ?? null);

            if ($direccionId) {
                (new Direccion())->update((int) $direccionId, [
                    'parroquias_id'     => $parroquiaId,
                    'localidad'         => $localidad,
                    'tipo_vivienda'     => $tipoVivienda,
                    'ubicacion_vivienda'=> $ubicacionVivienda,
                ]);
            } else if ($parroquiaId || $localidad || $tipoVivienda || $ubicacionVivienda) {
                $direccionId = (new Direccion())->insert([
                    'parroquias_id'     => $parroquiaId ?: 1,
                    'localidad'         => $localidad ?: '',
                    'tipo_vivienda'     => $tipoVivienda ?: 'casa',
                    'ubicacion_vivienda'=> $ubicacionVivienda ?: '',
                ]);
            }

            $update = [
                'nombre'       => $nombre,
                'apellido'     => $apellido,
                'cedula'       => $cedula,
                'telefono'     => $telefono,
                'fecha_nac'    => $fechaNac ?: null,
                'correo'       => $correo,
                'rol_id'       => $rolId,
                'estatus'      => $estatus ?? 'Activo',
                'direccion_id' => $direccionId,
            ];

            // Subir o eliminar foto
            if (isset($data['eliminar_foto']) && $data['eliminar_foto'] == '1') {
                $update['foto'] = null;
                if (!empty($actual['foto'])) {
                    $oldPath = BASE_PATH . '/public' . $actual['foto'];
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
            } else {
                $nuevaFoto = $this->guardarFoto($fotoFile);
                if ($nuevaFoto !== null) {
                    $update['foto'] = $nuevaFoto;
                    if (!empty($actual['foto'])) {
                        $oldPath = BASE_PATH . '/public' . $actual['foto'];
                        if (file_exists($oldPath)) @unlink($oldPath);
                    }
                }
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
