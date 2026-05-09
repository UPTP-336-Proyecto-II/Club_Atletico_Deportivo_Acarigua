<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $user = Auth::user();
        if ($user === null) {
            if ($request->isJson()) {
                return Response::json(['error' => 'No autenticado'], 401);
            }
            flash('error', 'Debes iniciar sesión.');
            return Response::redirect('/login');
        }
        // Check if user needs to set up account (first login)
        if (isset($_SESSION['must_change_password']) && $_SESSION['must_change_password'] === true) {
            $allowedPaths = ['/admin/setup', '/admin/setup/save', '/logout'];
            if (!in_array($request->uri(), $allowedPaths)) {
                if ($request->isJson()) {
                    return Response::json(['error' => 'Configuración de cuenta requerida', 'redirect' => '/admin/setup'], 403);
                }
                return Response::redirect('/admin/setup');
            }
        }

        $request->setUser($user);
        return $next($request);
    }
}
