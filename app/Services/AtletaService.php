<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Atleta;
use App\Models\Representante;
use App\Models\Direccion;
use App\Models\FichaMedica;
use RuntimeException;
use Throwable;

/**
 * Encapsula la creación/actualización de un atleta con sus entidades
 * relacionadas (representante, dirección, ficha médica) en una única transacción.
 */
final class AtletaService
{
    public function crear(array $data, array $fotoFile = []): int
    {
        Database::beginTransaction();
        try {
            $direccionId = $this->guardarDireccion($data);
            $representanteId = $this->guardarRepresentante($data, $direccionId);
            $fotoPath    = $this->guardarFoto($fotoFile);

            $atleta = new Atleta();
            $atletaId = $atleta->insert([
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'fecha_nac'         => $data['fecha_nacimiento'],
                'sexo'              => $data['sexo'] ?? 'M', // Por default para evitar error
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?: null,
                'posicion_juego_id' => !empty($data['posicion_de_juego']) ? $data['posicion_de_juego'] : null,
                'pierna_dominante'  => !empty($data['pierna_dominante']) ? $data['pierna_dominante'] : null,
                'categoria_id'      => !empty($data['categoria_id']) ? $data['categoria_id'] : null,
                'representante_id'  => $representanteId,
                'direccion_id'      => $direccionId,
                'foto'              => $fotoPath,
                'estatus'           => $data['estatus'] ?? 1, // 1: Activo
            ]);

            $this->guardarFichaMedica($atletaId, $data);

            Database::commit();
            Logger::audit('atleta.crear', ['atleta_id' => $atletaId]);
            return $atletaId;
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $atletaId, array $data, array $fotoFile = []): void
    {
        $atleta = new Atleta();
        $actual = $atleta->find($atletaId);
        if (!$actual) {
            throw new RuntimeException('Atleta no encontrado.');
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

            $representanteId = $this->guardarRepresentante($data, $direccionId, (int) ($actual['representante_id'] ?? 0));

            $update = [
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'fecha_nac'         => $data['fecha_nacimiento'],
                'sexo'              => $data['sexo'] ?? $actual['sexo'],
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?: null,
                'posicion_juego_id' => !empty($data['posicion_de_juego']) ? $data['posicion_de_juego'] : null,
                'pierna_dominante'  => !empty($data['pierna_dominante']) ? $data['pierna_dominante'] : null,
                'categoria_id'      => !empty($data['categoria_id']) ? $data['categoria_id'] : null,
                'representante_id'  => $representanteId,
                'direccion_id'      => $direccionId,
                'estatus'           => $data['estatus'] ?? $actual['estatus'],
            ];
            
            if (!empty($data['eliminar_foto'])) {
                $update['foto'] = null;
            } else {
                $nuevaFoto = $this->guardarFoto($fotoFile);
                if ($nuevaFoto !== null) {
                    $update['foto'] = $nuevaFoto;
                }
            }
            $atleta->update($atletaId, $update);

            $this->guardarFichaMedica($atletaId, $data);

            Database::commit();
            Logger::audit('atleta.actualizar', ['atleta_id' => $atletaId]);
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    private function guardarDireccion(array $data): int
    {
        return (new Direccion())->insert([
            'parroquias_id'     => $data['parroquia_id'] ?? 1, // Fix temporal si es requerido
            'localidad'         => $data['localidad'] ?? '',
            'tipo_vivienda'     => $data['tipo_vivienda'] ?? 'casa',
            'ubicacion_vivienda'=> $data['ubicacion_vivienda'] ?? '',
        ]);
    }

    private function guardarRepresentante(array $data, int $direccionId, int $representanteIdExistente = 0): int
    {
        $representanteModel = new Representante(); // Apunta a representante
        
        // 1. Si tenemos un ID existente de representante, actualizamos ese registro
        if ($representanteIdExistente > 0) {
            $existente = $representanteModel->find($representanteIdExistente);
            if ($existente) {
                $representanteModel->update($representanteIdExistente, [
                    'nombre'        => $data['tutor_nombres'] ?? $existente['nombre'],
                    'apellido'      => $data['tutor_apellidos'] ?? $existente['apellido'],
                    'cedula'        => $data['tutor_cedula'] ?? $existente['cedula'],
                    'telefono'      => $data['tutor_telefono'] ?? $existente['telefono'],
                    'tipo_relacion' => $data['tutor_relacion'] ?? $existente['tipo_relacion'],
                    'direccion_id'  => $direccionId,
                ]);
                return $representanteIdExistente;
            }
        }

        // 2. Si no hay ID existente, pero se proporciona cédula, intentamos buscarlo
        $existente = !empty($data['tutor_cedula']) ? $representanteModel->findByCedula($data['tutor_cedula']) : null;
        if ($existente) {
            $representanteModel->update((int) $existente['representante_id'], [
                'nombre'        => $data['tutor_nombres'] ?? $existente['nombre'],
                'apellido'      => $data['tutor_apellidos'] ?? $existente['apellido'],
                'telefono'      => $data['tutor_telefono'] ?? $existente['telefono'],
                'tipo_relacion' => $data['tutor_relacion'] ?? $existente['tipo_relacion'],
                'direccion_id'  => $direccionId,
            ]);
            return (int) $existente['representante_id'];
        }

        // 3. Si no existe de ninguna forma, insertamos uno nuevo
        return $representanteModel->insert([
            'nombre'        => $data['tutor_nombres'] ?? 'Sin Nombre',
            'apellido'      => $data['tutor_apellidos'] ?? '',
            'cedula'        => $data['tutor_cedula'] ?? 'S/N',
            'telefono'      => $data['tutor_telefono'] ?? '',
            'tipo_relacion' => $data['tutor_relacion'] ?? 'representante',
            'direccion_id'  => $direccionId,
        ]);
    }

    private function guardarFichaMedica(int $atletaId, array $data): void
    {
        $tieneData = !empty($data['alergias']) || !empty($data['grupo_sanguineo'])
            || !empty($data['condicion_cronica']) || !empty($data['antecedentes_quirurgicos']);
        if (!$tieneData) return;

        $model = new FichaMedica();
        $actual = $model->byAtleta($atletaId);
        $payload = [
            'grupo_sanguineo'          => $data['grupo_sanguineo'] ?? 'O+',
            'alergias'                 => $data['alergias'] ?? null,
            'antecedentes_familiares'  => $data['antecedentes_familiares'] ?? null,
            'antecedentes_quirurgicos' => $data['antecedentes_quirurgicos'] ?? null,
            'condicion_cronica'        => $data['condicion_cronica'] ?? null,
            'medicacion_actual'        => $data['medicacion_actual'] ?? null,
        ];
        if ($actual) {
            $model->update((int) $actual['ficha_id'], $payload);
        } else {
            $model->insert(['atleta_id' => $atletaId] + $payload);
        }
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
        $dir = BASE_PATH . '/public' . config('app.uploads.atletas_dir');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $dest = $dir . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('No se pudo guardar la foto.');
        }

        // Optimizar y redimensionar la imagen si excede el tamaño ideal
        $this->optimizarImagen($dest, $mime);

        return config('app.uploads.atletas_dir') . '/' . $basename;
    }

    /**
     * Redimensiona y optimiza una imagen subida para ahorrar espacio, ancho de banda
     * y mejorar los tiempos de renderizado en el navegador del cliente.
     */
    private function optimizarImagen(string $filePath, string $mime): void
    {
        try {
            $info = getimagesize($filePath);
            if (!$info) return;

            list($width, $height) = $info;
            $maxDim = 600; // Resolución ideal óptima para fotos de perfil 180x180

            // Si es más pequeña que el límite, no hace falta redimensionarla
            if ($width <= $maxDim && $height <= $maxDim) {
                return;
            }

            // Calcular proporciones manteniendo relación de aspecto
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxDim;
                $newHeight = (int) round($maxDim / $ratio);
            } else {
                $newHeight = $maxDim;
                $newWidth = (int) round($maxDim * $ratio);
            }

            // Crear el recurso de imagen de origen según el tipo MIME
            $srcImage = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($filePath),
                'image/png'  => @imagecreatefrompng($filePath),
                'image/webp' => @imagecreatefromwebp($filePath),
                default      => null
            };

            if (!$srcImage) return;

            // Crear el lienzo de destino
            $dstImage = imagecreatetruecolor($newWidth, $newHeight);
            if (!$dstImage) {
                imagedestroy($srcImage);
                return;
            }

            // Manejo y preservación de transparencias para PNG y WebP
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Redimensionar con interpolación bilineal de alta calidad
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Guardar sobreescribiendo el archivo original con compresión óptima
            match ($mime) {
                'image/jpeg' => imagejpeg($dstImage, $filePath, 85),
                'image/png'  => imagepng($dstImage, $filePath, 6),
                'image/webp' => imagewebp($dstImage, $filePath, 80),
                default      => null
            };

            // Liberar memoria
            imagedestroy($srcImage);
            imagedestroy($dstImage);
        } catch (Throwable $e) {
            // Registramos el error de GD pero no bloqueamos el flujo de guardado principal
            Logger::error($e);
        }
    }
}
