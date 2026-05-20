<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Logger;
use App\Models\Atleta;
use App\Models\Categoria;
use App\Models\PosicionJuego;
use App\Models\Direccion;
use App\Models\MedidaAntropometrica;
use App\Models\ResultadoPrueba;
use App\Models\Asistencia;
use App\Services\AtletaService;
use Throwable;

final class AtletasController extends Controller
{
    // CAMBIAR ESTE VALOR SI LA COMUNIDAD DECIDE OTRA EDAD MÍNIMA OFICIAL
    public const EDAD_MINIMA_ATLETA = 6;

    public function index(Request $request): Response
    {
        $filters = [
            'categoria_id' => $request->query('categoria_id'),
            'estatus' => $request->query('estatus'),
            'q' => $request->query('q'),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $atletaModel = new Atleta();
        $data = $atletaModel->paginate(array_filter($filters, fn($v) => $v !== null && $v !== ''), $page, 15);
        $categorias = (new Categoria())->allWithEntrenador();

        // Calcular conteos reales para las tarjetas
        $countsRaw = $atletaModel->countByEstatus();
        $stats = ['activo' => 0, 'lesionado' => 0, 'suspendido' => 0, 'inactivo' => 0];
        foreach ($countsRaw as $c) {
            if ((int) $c['estatus'] === 1)
                $stats['activo'] = (int) $c['total'];
            if ((int) $c['estatus'] === 2)
                $stats['lesionado'] = (int) $c['total'];
            if ((int) $c['estatus'] === 0)
                $stats['suspendido'] = (int) $c['total'];
            if ((int) $c['estatus'] === 3)
                $stats['inactivo'] = (int) $c['total'];
        }

        return $this->view('atletas.index', [
            'title' => 'Atletas',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas'],
            'pag' => $data,
            'categorias' => $categorias,
            'filters' => $filters,
            'stats' => $stats,
        ], 'admin');
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }
        $pdo = \App\Core\Database::connection();
        $tipos_discapacidades = $pdo->query("SELECT * FROM tipos_discapacidades ORDER BY nombre_tipo ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $medidasModel = new MedidaAntropometrica();
        $medidas_historial = $medidasModel->historial($id);

        $pruebasModel = new ResultadoPrueba();
        $pruebas_historial = $pruebasModel->historial($id);

        $asistenciaModel = new Asistencia();
        $asistencias_historial = $asistenciaModel->historialAtleta($id);

        return $this->view('atletas.show', [
            'title' => $atleta['nombre'] . ' ' . $atleta['apellido'],
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', $atleta['nombre'] . ' ' . $atleta['apellido']],
            'atleta' => $atleta,
            'tipos_discapacidades' => $tipos_discapacidades,
            'medidas_historial' => $medidas_historial,
            'pruebas_historial' => $pruebas_historial,
            'asistencias_historial' => $asistencias_historial,
            'categorias' => (new Categoria())->activas(),
            'posiciones' => (new PosicionJuego())->all('nombre_posicion'),
            'paises'     => (new Direccion())->paises(),
            'entrenadores' => (new \App\Models\Usuario())->entrenadores(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('atletas.form', [
            'title' => 'Nuevo atleta',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Nuevo'],
            'atleta' => null,
            'categorias' => (new Categoria())->activas(),
            'posiciones' => (new PosicionJuego())->all('nombre_posicion'),
            'paises' => (new Direccion())->paises(),
            'action' => url('/admin/atletas'),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->rawInput($request);
        $errors = $this->validar($data)->errors();
        if ($errors) {
            $this->withOld($data)->withErrors($errors);
            return $this->redirect('/admin/atletas/crear');
        }

        $fechaNac = strtotime($data['fecha_nacimiento']);
        $hoy = time();
        if ($fechaNac > $hoy) {
            $this->withOld($data);
            flash('error', 'La fecha de nacimiento no puede ser en el futuro.');
            return $this->redirect('/admin/atletas/crear');
        }
        $edad = date('Y', $hoy) - date('Y', $fechaNac);
        if ($edad < self::EDAD_MINIMA_ATLETA) {
            $this->withOld($data);
            flash('error', 'El atleta debe tener al menos ' . self::EDAD_MINIMA_ATLETA . ' años de edad.');
            return $this->redirect('/admin/atletas/crear');
        }

        try {
            $service = new AtletaService();
            $id = $service->crear($data, $_FILES['foto'] ?? []);
            flash('success', 'Atleta registrado correctamente.');
            return $this->redirect("/admin/atletas/$id");
        } catch (Throwable $e) {
            Logger::error($e);
            $this->withOld($data);
            flash('error', 'No se pudo crear el atleta: ' . $e->getMessage());
            return $this->redirect('/admin/atletas/crear');
        }
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }
        return $this->view('atletas.form', [
            'title' => 'Editar atleta',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Editar'],
            'atleta' => $atleta,
            'categorias' => (new Categoria())->activas(),
            'posiciones' => (new PosicionJuego())->all('nombre_posicion'),
            'paises' => (new Direccion())->paises(),
            'action' => url("/admin/atletas/{$atleta['atleta_id']}"),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atletaModel = new Atleta();
        $actual = $atletaModel->findCompleto($id);
        
        if (!$actual) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Atleta no encontrado.'], 404);
            }
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }

        // Combinar datos existentes con los nuevos para permitir actualizaciones parciales (Modales)
        $data = $this->mergeData($actual, $request);
        
        $v = $this->validar($data, $id);
        if (!$v->validate()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $v->errors()
                ], 422);
            }
            $this->withOld($data)->withErrors($v->errors());
            return $this->redirect("/admin/atletas/$id/editar");
        }

        $fechaNac = strtotime($data['fecha_nacimiento']);
        $hoy = time();
        if ($fechaNac > $hoy) {
            $msg = 'La fecha de nacimiento no puede ser en el futuro.';
            if ($request->isAjax() || $request->isJson()) return Response::json(['success' => false, 'message' => $msg], 400);
            $this->withOld($data);
            flash('error', $msg);
            return $this->redirect("/admin/atletas/$id/editar");
        }
        
        $edad = (int) date('Y', $hoy) - (int) date('Y', $fechaNac);
        if ($edad < self::EDAD_MINIMA_ATLETA) {
            $msg = 'El atleta debe tener al menos ' . self::EDAD_MINIMA_ATLETA . ' años de edad.';
            if ($request->isAjax() || $request->isJson()) return Response::json(['success' => false, 'message' => $msg], 400);
            $this->withOld($data);
            flash('error', $msg);
            return $this->redirect("/admin/atletas/$id/editar");
        }

        try {
            (new AtletaService())->actualizar($id, $data, $_FILES['foto'] ?? []);
            
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => true, 'message' => 'Atleta actualizado correctamente.']);
            }
            
            flash('success', 'Atleta actualizado.');
            return $this->redirect("/admin/atletas/$id");
        } catch (Throwable $e) {
            Logger::error($e);
            $msg = 'No se pudo actualizar: ' . $e->getMessage();
            if ($request->isAjax() || $request->isJson()) return Response::json(['success' => false, 'message' => $msg], 500);
            $this->withOld($data);
            flash('error', $msg);
            return $this->redirect("/admin/atletas/$id/editar");
        }
    }

    /**
     * Mezcla los datos actuales del atleta con los recibidos en el request.
     * Esto permite que los modales solo envíen los campos que están editando.
     */
    private function mergeData(array $actual, Request $request): array
    {
        $input = [
            'nombre'            => $request->input('nombre', $actual['nombre']),
            'apellido'          => $request->input('apellido', $actual['apellido']),
            'cedula'            => $request->input('cedula') !== null ? ($request->input('cedula') ?: null) : $actual['cedula'],
            'sexo'              => $request->input('sexo', $actual['sexo']),
            'telefono'          => $request->input('telefono') !== null ? ($request->input('telefono') ?: null) : $actual['telefono'],
            'fecha_nacimiento'  => $request->input('fecha_nacimiento', $actual['fecha_nac']),
            'posicion_de_juego' => $request->input('posicion_de_juego') !== null ? ($request->input('posicion_de_juego') ?: null) : $actual['posicion_juego_id'],
            'pierna_dominante'  => $request->input('pierna_dominante') !== null ? ($request->input('pierna_dominante') ?: null) : $actual['pierna_dominante'],
            'categoria_id'      => $request->input('categoria_id') !== null ? ($request->input('categoria_id') ?: null) : $actual['categoria_id'],
            'estatus'           => $request->input('estatus') !== null ? (int) $request->input('estatus') : $actual['estatus'],
            
            'estado_id'         => $request->input('estado_id', $actual['estado_id'] ?? null),
            'municipio_id'      => $request->input('municipio_id', $actual['municipio_id'] ?? null),
            'parroquia_id'      => $request->input('parroquia_id', $actual['parroquias_id']),
            'localidad'         => $request->input('localidad', $actual['localidad']),
            'tipo_vivienda'     => $request->input('tipo_vivienda', $actual['tipo_vivienda']),
            'ubicacion_vivienda'=> $request->input('ubicacion_vivienda', $actual['ubicacion_vivienda']),
            
            'tutor_nombres'     => $request->input('tutor_nombres', $actual['tutor_nombres']),
            'tutor_apellidos'   => $request->input('tutor_apellidos', $actual['tutor_apellidos']),
            'tutor_cedula'      => $request->input('tutor_cedula', $actual['tutor_cedula']),
            'tutor_telefono'    => $request->input('tutor_telefono', $actual['tutor_telefono']),
            'tutor_relacion'    => $request->input('tutor_relacion', $actual['tutor_relacion']),
            
            'alergias'                 => $request->input('alergias', $actual['alergias']),
            'grupo_sanguineo'          => $request->input('grupo_sanguineo', $actual['grupo_sanguineo']),
            'antecedentes_familiares'  => $request->input('antecedentes_familiares', $actual['antecedentes_familiares']),
            'antecedentes_quirurgicos' => $request->input('antecedentes_quirurgicos', $actual['antecedentes_quirurgicos']),
            'condicion_cronica'        => $request->input('condicion_cronica', $actual['condicion_cronica']),
            'medicacion_actual'        => $request->input('medicacion_actual', $actual['medicacion_actual']),
            'eliminar_foto'            => $request->input('eliminar_foto') === '1',
        ];

        return $input;
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        try {
            // Eliminar ficha médica asociada primero (si existe) para evitar error de llave foránea
            (new \App\Models\FichaMedica())->query('DELETE FROM fichas_medicas WHERE atleta_id = :id', [':id' => $id]);

            (new Atleta())->delete($id);
            Logger::audit('atleta.eliminar', ['atleta_id' => $id]);
            flash('success', 'Atleta eliminado correctamente.');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'No se pudo eliminar el atleta porque tiene registros importantes asociados (ej. asistencias). Sugerencia: cambie su estatus a Inactivo.');
        }
        return $this->redirect('/admin/atletas');
    }

    private function rawInput(Request $request): array
    {
        return [
            'nombre' => trim((string) $request->input('nombre', '')),
            'apellido' => trim((string) $request->input('apellido', '')),
            'cedula' => trim((string) $request->input('cedula', '')),
            'sexo' => trim((string) $request->input('sexo', 'M')), // Nuevo campo requerido en BD
            'telefono' => trim((string) $request->input('telefono', '')),
            'fecha_nacimiento' => trim((string) $request->input('fecha_nacimiento', '')),
            'posicion_de_juego' => $request->input('posicion_de_juego') ?: null,
            'pierna_dominante' => $request->input('pierna_dominante') ?: null,
            'categoria_id' => $request->input('categoria_id') ?: null,
            'estatus' => $request->input('estatus') !== null ? (int) $request->input('estatus') : 1,

            // Dirección (Adaptado a tabla direcciones)
            'estado_id' => $request->input('estado_id') ?: null,
            'municipio_id' => $request->input('municipio_id') ?: null,
            'parroquia_id' => $request->input('parroquia_id') ?: null,
            'localidad' => trim((string) $request->input('localidad', '')),
            'tipo_vivienda' => trim((string) $request->input('tipo_vivienda', '')),
            'ubicacion_vivienda' => trim((string) $request->input('ubicacion_vivienda', '')),

            // Representante (Adaptado a tabla representante)
            'tutor_nombres' => trim((string) $request->input('tutor_nombres', '')),
            'tutor_apellidos' => trim((string) $request->input('tutor_apellidos', '')),
            'tutor_cedula' => trim((string) $request->input('tutor_cedula', '')),
            'tutor_telefono' => trim((string) $request->input('tutor_telefono', '')),
            'tutor_relacion' => trim((string) $request->input('tutor_relacion', 'representante')),

            // Ficha médica (Adaptado a tabla ficha_medica)
            'alergias' => trim((string) $request->input('alergias', '')),
            'grupo_sanguineo' => trim((string) $request->input('grupo_sanguineo', '')),
            'antecedentes_familiares' => trim((string) $request->input('antecedentes_familiares', '')),
            'antecedentes_quirurgicos' => trim((string) $request->input('antecedentes_quirurgicos', '')),
            'condicion_cronica' => trim((string) $request->input('condicion_cronica', '')),
            'medicacion_actual' => trim((string) $request->input('medicacion_actual', '')),
            'eliminar_foto' => $request->input('eliminar_foto') === '1',
        ];
    }

    private function validar(array $data, ?int $ignoreId = null): Validator
    {
        // Regex: cédula venezolana V-X.XXX.XXX o E-XX.XXX.XXX (hasta 8 dígitos) o P-AÑO-ACTA-FOLIO (partida de nacimiento)
        $cedRegex = '/^([VE]-\d{1,3}(\.\d{3})*|P-\d{4}-[A-Z0-9]{1,5}-[A-Z0-9]{1,5})$/i';
        // Regex: teléfono 11 dígitos con prefijo venezolano (prefijo 4 dígitos + 7 dígitos = 11 total)
        $telRegex = '/^0(412|414|416|422|424|426)\d{7}$/';

        $rules = [
            'nombre' => 'required|min:2|max:100',
            'apellido' => 'required|min:2|max:100',
            'fecha_nacimiento' => 'required|date',
            'estatus' => 'required|in:0,1,2,3',
            'pierna_dominante' => 'in:derecha,izquierda,ambidiestro',
        ];

        // Calcular edad en años en el backend para validar de manera dinámica
        $age = 0;
        if (!empty($data['fecha_nacimiento'])) {
            $birthDate = strtotime($data['fecha_nacimiento']);
            if ($birthDate !== false) {
                $age = (int) date('Y') - (int) date('Y', $birthDate);
                if (date('md') < date('md', $birthDate)) {
                    $age--;
                }
            }
        }

        // 1. Cédula/Folio obligatoria si es mayor de 9 años
        if ($age > 9) {
            $rules['cedula'] = ['required', "regex:$cedRegex"];
        } elseif (!empty($data['cedula'])) {
            $rules['cedula'] = ["regex:$cedRegex"];
        }

        // 2. Teléfono personal obligatorio si es mayor de edad
        if ($age >= 18) {
            $rules['telefono'] = ['required', "regex:$telRegex"];
        } elseif (!empty($data['telefono'])) {
            $rules['telefono'] = ["regex:$telRegex"];
        }

        // 3. Datos del representante obligatorios si es menor de edad OR si se edita desde el modal de representante
        $tieneRepresentanteEnPost = ($ignoreId !== null) && isset($_POST['tutor_nombres']);
        if ($age < 18 || $tieneRepresentanteEnPost) {
            $esEdicionBasico = ($ignoreId !== null) && isset($_POST['nombre']) && !isset($_POST['tutor_nombres']);
            $tutorVacio = empty($data['tutor_nombres']) 
                || $data['tutor_nombres'] === 'Sin Nombre' 
                || empty($data['tutor_cedula']) 
                || $data['tutor_cedula'] === 'S/N' 
                || empty($data['tutor_telefono']);

            if ($esEdicionBasico && $tutorVacio && $age < 18) {
                $rules['tutor_representante'] = 'required';
            } else {
                $rules['tutor_nombres'] = 'required|min:2|max:100';
                $rules['tutor_apellidos'] = 'required|min:2|max:100';
                $rules['tutor_cedula'] = ['required', "regex:$cedRegex"];
                $rules['tutor_telefono'] = ['required', "regex:$telRegex"];
                $rules['tutor_relacion'] = 'required';
            }
        } else {
            // Si es mayor de edad y no se envía el modal de representante, es opcional pero se valida formato si existe
            if (!empty($data['tutor_cedula'])) {
                $rules['tutor_cedula'] = ["regex:$cedRegex"];
            }
            if (!empty($data['tutor_telefono'])) {
                $rules['tutor_telefono'] = ["regex:$telRegex"];
            }
        }

        // 4. Validar dirección detallada si estamos en registro o si se envían datos de dirección en el request
        $esRegistro = ($ignoreId === null);
        $tieneDireccionEnRequest = isset($_POST['parroquia_id']) || isset($_POST['localidad']);
        if ($esRegistro || $tieneDireccionEnRequest) {
            $rules['parroquia_id'] = 'required|integer';
            $rules['localidad'] = 'required|min:2|max:200';
            $rules['tipo_vivienda'] = 'required|in:casa,apto,edificio';
            $rules['ubicacion_vivienda'] = 'required|min:2|max:500';
        }

        $messages = [
            'cedula' => 'La cédula debe tener el formato V-12.345.678, E-12.345.678 o P-AÑO-ACTA-FOLIO (partida de nacimiento). Es obligatoria para mayores de 9 años.',
            'telefono' => 'El teléfono debe comenzar con 0412, 0414, 0416, 0422 o 0424 y tener 11 dígitos. Es obligatorio para mayores de edad.',
            'tutor_representante' => 'Para registrar al atleta como menor de edad, primero debe asignar y guardar los datos de su representante en la sección correspondiente de su perfil.',
            'tutor_nombres' => 'El nombre del representante es obligatorio.',
            'tutor_apellidos' => 'El apellido del representante es obligatorio.',
            'tutor_cedula' => 'La cédula del representante debe tener el formato V-12.345.678 o E-12.345.678 y es obligatoria.',
            'tutor_telefono' => 'El teléfono del representante es obligatorio y debe tener 11 dígitos.',
            'tutor_relacion' => 'El tipo de relación con el representante es obligatorio.',
            'parroquia_id' => 'La parroquia es obligatoria.',
            'localidad' => 'La localidad o sector es obligatorio y debe tener al menos 2 caracteres.',
            'tipo_vivienda' => 'El tipo de vivienda es obligatorio.',
            'ubicacion_vivienda' => 'La ubicación específica (dirección exacta) es obligatoria y debe tener al menos 2 caracteres.',
        ];

        $v = Validator::make($data, $rules, $messages);
        $v->validate();
        return $v;
    }
}
