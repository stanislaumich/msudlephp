<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\Subject;
use Models\Department;
use Models\Faculty;

class SubjectController
{
    public function index(): void
    {
        \Middleware\Middleware::settingsAccess();

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        $subjects = Subject::all('full_name ASC');
        foreach ($subjects as &$s) {
            $s['department'] = $s['department_id'] ? Department::find($s['department_id']) : null;
            if ($s['department']) {
                $s['faculty'] = $s['department']['faculty_id'] ? Faculty::find($s['department']['faculty_id']) : null;
            }
        }

        View::render('subject.index', [
            'user' => $user,
            'role' => $role,
            'subjects' => $subjects,
        ]);
    }

    public function createForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        $departments = Department::all('full_name ASC');

        View::render('subject.form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'departments' => $departments,
            'subject' => null,
        ]);
    }

    public function create(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/subjects/create/');
            return;
        }

        $departmentId = $_POST['department'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');

        if (!$departmentId || !$fullName || !$shortName) {
            View::render('subject.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'departments' => Department::all('full_name ASC'),
                'subject' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        Subject::create([
            'department_id' => (int)$departmentId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
        ]);

        Router::redirect('/subjects/');
    }

    public function editForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $subject = Subject::findOrFail($id);
        $departments = Department::all('full_name ASC');

        View::render('subject.form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'departments' => $departments,
            'subject' => $subject,
        ]);
    }

    public function edit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/subjects/{$id}/edit/");
            return;
        }

        $departmentId = $_POST['department'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');

        if (!$departmentId || !$fullName || !$shortName) {
            View::render('subject.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'departments' => Department::all('full_name ASC'),
                'subject' => Subject::findOrFail($id),
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        Subject::updateWhere('id = ?', [
            'department_id' => (int)$departmentId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
        ], [$id]);

        Router::redirect('/subjects/');
    }
}
