<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Direccion;
use App\Services\UsuarioService;
use App\Core\Logger;
use Throwable;

final class UsuariosController extends Controller
{
    public function index(Request $request): Response
    {
        $loggedUser = Auth::user();
        $items = (new Usuario())->allWithRol();

        // Si es administrador ordinario, ocultar los superusuarios de la lista
        if ($loggedUser['rol_id'] == ROL_ADMIN) {
            $items = array_filter($items, function ($item) {
                return (int) $item['rol_id'] !== ROL_SUPERUSER;
            });
            // Reindexar array
            $items = array_values($items);
        }

        return $this->view('usuarios.index', [
            'title' => 'Gestión de Usuarios',
            'active' => 'usuarios',
            'breadcrumb' => ['Inicio', 'Usuarios'],
            'items' => $items,
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('usuarios.form', [
            'title' => 'Nuevo Usuario',
            'active' => 'usuarios',
            'breadcrumb' => ['Inicio', 'Usuarios', 'Nuevo'],
            'item' => null,
            'roles' => (new Rol())->allowedRolesFor((int) Auth::user()['rol_id']),
            'paises' => (new Direccion())->paises(),
            'action' => url('/admin/usuarios'),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->input($request);
        $loggedUser = Auth::user();

        // Validar que un administrador no intente registrar a alguien como Superusuario
        if ($loggedUser['rol_id'] == ROL_ADMIN && $data['rol_id'] == ROL_SUPERUSER) {
            flash('error', 'No tienes permisos para registrar usuarios con el rol de Soporte Técnico.');
            return $this->redirect('/admin/usuarios/crear');
        }

        $errors = $this->validar($data);
        if ($errors) {
            $this->withOld($data)->withErrors($errors);
            return $this->redirect('/admin/usuarios/crear');
        }
        try {
            $service = new UsuarioService();
            $service->crear($data, $_FILES['foto'] ?? []);
            flash('success', 'Usuario registrado exitosamente.');
            return $this->redirect('/admin/usuarios');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'No se pudo registrar: ' . $e->getMessage());
            $this->withOld($data);
            return $this->redirect('/admin/usuarios/crear');
        }
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $item = (new Usuario())->findCompleto($id);
        if (!$item) {
            flash('error', 'No encontrado.');
            return $this->redirect('/admin/usuarios');
        }

        $loggedUser = Auth::user();

        // Evitar que un administrador edite a un superusuario
        if ($loggedUser['rol_id'] == ROL_ADMIN && $item['rol_id'] == ROL_SUPERUSER) {
            flash('error', 'No tienes permisos para editar una cuenta de Soporte Técnico.');
            return $this->redirect('/admin/usuarios');
        }

        return $this->view('usuarios.form', [
            'title' => 'Editar Usuario',
            'active' => 'usuarios',
            'breadcrumb' => ['Inicio', 'Usuarios', 'Editar'],
            'item' => $item,
            'roles' => (new Rol())->allowedRolesFor((int) $loggedUser['rol_id']),
            'paises' => (new Direccion())->paises(),
            'action' => url("/admin/usuarios/$id"),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $item = (new Usuario())->findCompleto($id);
        if (!$item) {
            flash('error', 'Usuario no encontrado.');
            return $this->redirect('/admin/usuarios');
        }

        $loggedUser = Auth::user();

        // Evitar que un administrador actualice a un superusuario
        if ($loggedUser['rol_id'] == ROL_ADMIN && $item['rol_id'] == ROL_SUPERUSER) {
            flash('error', 'No tienes permisos para modificar una cuenta de Soporte Técnico.');
            return $this->redirect('/admin/usuarios');
        }

        $data = $this->input($request);

        // Evitar que un administrador asigne el rol de superusuario a alguien
        if ($loggedUser['rol_id'] == ROL_ADMIN && $data['rol_id'] == ROL_SUPERUSER) {
            flash('error', 'No tienes permisos para asignar el rol de Soporte Técnico.');
            return $this->redirect("/admin/usuarios/$id/editar");
        }

        $errors = $this->validar($data, $id);
        if ($errors) {
            $this->withOld($data)->withErrors($errors);
            return $this->redirect("/admin/usuarios/$id/editar");
        }
        try {
            (new UsuarioService())->actualizar($id, $data, $_FILES['foto'] ?? []);
            flash('success', 'Usuario actualizado.');
            return $this->redirect('/admin/usuarios');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'No se pudo actualizar: ' . $e->getMessage());
            $this->withOld($data);
            return $this->redirect("/admin/usuarios/$id/editar");
        }
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');

        // Proteger al superusuario id=1 (cuenta del equipo de desarrollo)
        if ($id === 1) {
            flash('error', 'La cuenta de soporte técnico no puede ser eliminada.');
            return $this->redirect('/admin/usuarios');
        }

        $item = (new Usuario())->findCompleto($id);
        if ($item) {
            $loggedUser = Auth::user();
            // Evitar que un administrador elimine a un superusuario
            if ($loggedUser['rol_id'] == ROL_ADMIN && $item['rol_id'] == ROL_SUPERUSER) {
                flash('error', 'No tienes permisos para eliminar una cuenta de Soporte Técnico.');
                return $this->redirect('/admin/usuarios');
            }
        }

        try {
            // Eliminar dependencias primero
            $respModel = new \App\Models\RespuestaSeguridad();
            $respModel->deleteByUser($id);

            (new Usuario())->delete($id);
            flash('success', 'Usuario eliminado.');
        } catch (Throwable $e) {
            flash('error', 'No se pudo eliminar: El usuario tiene datos vinculados (atletas, categorías o actividades) que impiden borrarlo por seguridad.');
        }
        return $this->redirect('/admin/usuarios');
    }

    public function restablecer(Request $request): Response
    {
        $id = (int) $request->param('id');
        $item = (new Usuario())->findCompleto($id);
        if (!$item) {
            flash('error', 'Usuario no encontrado.');
            return $this->redirect('/admin/usuarios');
        }

        $loggedUser = Auth::user();

        // Evitar que un administrador restablezca a un superusuario
        if ($loggedUser['rol_id'] == ROL_ADMIN && $item['rol_id'] == ROL_SUPERUSER) {
            flash('error', 'No tienes permisos para restablecer una cuenta de Soporte Técnico.');
            return $this->redirect('/admin/usuarios');
        }

        try {
            $db = \App\Core\Database::connection();
            $db->beginTransaction();

            // 1. Eliminar respuestas de seguridad anteriores (para forzar setup)
            $respModel = new \App\Models\RespuestaSeguridad();
            $respModel->deleteByUser($id);

            // 2. Extraer dígitos de cédula para contraseña temporal
            $cedulaDigits = preg_replace('/[^0-9]/', '', (string) $item['cedula']);
            if (empty($cedulaDigits)) {
                $cedulaDigits = '12345678';
            }
            $hashPassword = password_hash($cedulaDigits, PASSWORD_BCRYPT, ['cost' => 12]);

            // 3. Actualizar contraseña en la BD
            (new Usuario())->update($id, [
                'contrasena' => $hashPassword
            ]);

            $db->commit();
            
            flash('success', "Credenciales de <strong>" . e($item['nombre'] . ' ' . $item['apellido']) . "</strong> restablecidas correctamente. Su contraseña temporal es su número de cédula (<strong>" . e($cedulaDigits) . "</strong>). Al ingresar se le forzará a reconfigurar sus datos de seguridad.");
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            Logger::error($e);
            flash('error', 'No se pudo restablecer el usuario.');
        }

        return $this->redirect('/admin/usuarios');
    }

    private function input(Request $request): array
    {
        return [
            'nombre'    => trim((string) $request->input('nombre')),
            'apellido'  => trim((string) $request->input('apellido')),
            'cedula'    => preg_replace('/[^0-9]/', '', (string) $request->input('cedula')),
            'telefono'  => trim((string) $request->input('telefono')),
            'fecha_nac' => trim((string) $request->input('fecha_nac')),
            'correo'    => trim((string) $request->input('correo')),
            'rol_id'    => (int) $request->input('rol_id'),
            // Dirección
            'estado_id'          => $request->input('estado_id') ?: null,
            'municipio_id'       => $request->input('municipio_id') ?: null,
            'parroquia_id'       => $request->input('parroquia_id') ?: null,
            'localidad'          => trim((string) $request->input('localidad', '')),
            'tipo_vivienda'      => trim((string) $request->input('tipo_vivienda', '')),
            'ubicacion_vivienda' => trim((string) $request->input('ubicacion_vivienda', '')),
            'estatus'            => $request->input('estatus') ?: 'Activo',
        ];
    }

    private function validar(array $data, ?int $ignoreId = null): array
    {
        $cedulaRule = 'required|min:7|max:12|regex:/^[0-9]+$/';
        if ($ignoreId) {
            $cedulaRule .= "|unique:usuarios,cedula,usuario_id:$ignoreId";
        } else {
            $cedulaRule .= '|unique:usuarios,cedula';
        }

        $correoRule = 'required|email|max:50';
        if ($ignoreId) {
            $correoRule .= "|unique:usuarios,correo,usuario_id:$ignoreId";
        } else {
            $correoRule .= '|unique:usuarios,correo';
        }

        $v = Validator::make($data, [
            'nombre'    => 'required|min:3|max:30',
            'apellido'  => 'required|min:3|max:30',
            'cedula'    => $cedulaRule,
            'telefono'  => 'required|min:7|max:15|regex:/^[0-9]+$/',
            'correo'    => $correoRule,
            'fecha_nac' => 'required|date',
            'rol_id'    => 'required|integer',
            'parroquia_id' => 'required|integer',
            'localidad' => 'required|min:2|max:100',
            'tipo_vivienda' => 'required',
            'ubicacion_vivienda' => 'required|min:5|max:100',
        ], [
            'parroquia_id' => 'El campo parroquia es obligatorio.',
            'rol_id'       => 'El campo rol / cargo es obligatorio.',
        ]);

        $v->validate();
        $errors = $v->errors();

        if (!empty($data['fecha_nac'])) {
            $fechaNac = new \DateTime($data['fecha_nac']);
            $hoy = new \DateTime();

            if ($fechaNac > $hoy) {
                $errors['fecha_nac'][] = 'La fecha de nacimiento no puede estar en el futuro.';
            } else {
                $edad = $hoy->diff($fechaNac)->y;
                if ($edad < 18) {
                    $errors['fecha_nac'][] = 'Debe ser mayor de edad (18 años o más).';
                }
            }
        }

        return $errors;
    }
}
