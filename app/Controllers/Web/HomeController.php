<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('public.home', [
            'title'  => config('app.name') . ' - Inicio',
            'active' => 'home',
        ], 'public');
    }

    public function nosotros(Request $request): Response
    {
        return $this->view('public.nosotros', [
            'title'  => 'Nosotros - ' . config('app.name'),
            'active' => 'nosotros',
        ], 'public');
    }

    public function contacto(Request $request): Response
    {
        return $this->view('public.contacto', [
            'title'  => 'Contacto - ' . config('app.name'),
            'active' => 'contacto',
        ], 'public');
    }
}

