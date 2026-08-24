<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\Shifr;
use Models\Faculty;

class UmoController
{
    public function index(): void
    {
        \Middleware\Middleware::settingsAccess();
        View::render('umo.index', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
        ]);
    }

    public function shifrs(): void
    {
        \Middleware\Middleware::settingsAccess();
        $shifrs = Shifr::all('code ASC');
        foreach ($shifrs as &$s) {
            $s['faculty'] = $s['faculty_id'] ? Faculty::find($s['faculty_id']) : null;
        }
        View::render('umo.shifrs', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'shifrs' => $shifrs,
        ]);
    }

    public function shifrCreateForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        $faculties = Faculty::all('full_name ASC');

        View::render('umo.shifr_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'faculties' => $faculties,
            'shifr' => null,
        ]);
    }

    public function shifrCreate(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/umo/shifrs/create/');
            return;
        }

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $facultyId = $_POST['faculty'] ?? null;

        if (!$code) {
            View::render('umo.shifr_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => Faculty::all('full_name ASC'),
                'shifr' => null,
                'error' => 'Введите код.',
            ]);
            return;
        }

        Shifr::create([
            'code' => $code,
            'name' => $name ?: null,
            'qualification' => $qualification ?: null,
            'faculty_id' => $facultyId ? (int)$facultyId : null,
        ]);

        Router::redirect('/umo/shifrs/');
    }

    public function shifrEditForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $shifr = Shifr::findOrFail($id);
        $faculties = Faculty::all('full_name ASC');

        View::render('umo.shifr_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'faculties' => $faculties,
            'shifr' => $shifr,
        ]);
    }

    public function shifrEdit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/umo/shifrs/{$id}/edit/");
            return;
        }

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $facultyId = $_POST['faculty'] ?? null;

        if (!$code) {
            View::render('umo.shifr_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => Faculty::all('full_name ASC'),
                'shifr' => Shifr::findOrFail($id),
                'error' => 'Введите код.',
            ]);
            return;
        }

        Shifr::updateWhere('id = ?', [
            'code' => $code,
            'name' => $name ?: null,
            'qualification' => $qualification ?: null,
            'faculty_id' => $facultyId ? (int)$facultyId : null,
        ], [$id]);

        Router::redirect('/umo/shifrs/');
    }
}
