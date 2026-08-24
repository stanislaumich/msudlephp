<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\CourseType;
use Models\CourseTypeSection;

class CourseTypeController
{
    public function index(): void
    {
        \Middleware\Middleware::settingsAccess();

        $types = CourseType::all('`order` ASC, name ASC');
        foreach ($types as &$t) {
            $t['sections'] = CourseTypeSection::where("course_type_id = ?", [$t['id']], '`order` ASC');
        }

        View::render('course_type.index', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'types' => $types,
        ]);
    }

    public function createForm(): void
    {
        \Middleware\Middleware::settingsAccess();

        View::render('course_type.form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'type' => null,
            'sections' => [],
        ]);
    }

    public function create(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/course-types/create/');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $order = (int)($_POST['order'] ?? 0);

        if (!$name) {
            View::render('course_type.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'type' => null,
                'sections' => $this->sectionsFromPost(),
                'error' => 'Введите название типа курса.',
            ]);
            return;
        }

        $existing = CourseType::findOne("name = ?", [$name]);
        if ($existing) {
            View::render('course_type.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'type' => null,
                'sections' => $this->sectionsFromPost(),
                'error' => 'Тип курса с таким названием уже существует.',
            ]);
            return;
        }

        $typeId = CourseType::create([
            'name' => $name,
            'description' => $description ?: null,
            'order' => $order,
        ]);

        $this->syncSections($typeId, $this->sectionsFromPost());

        \Core\Flash::success('Тип курса создан.');
        Router::redirect('/course-types/');
    }

    public function editForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        $type = CourseType::findOrFail($id);
        $sections = CourseTypeSection::where("course_type_id = ?", [$id], '`order` ASC');

        View::render('course_type.form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'type' => $type,
            'sections' => $sections,
        ]);
    }

    public function edit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/course-types/{$id}/edit/");
            return;
        }

        $type = CourseType::findOrFail($id);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $order = (int)($_POST['order'] ?? 0);

        if (!$name) {
            View::render('course_type.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'type' => $type,
                'sections' => $this->sectionsFromPost(),
                'error' => 'Введите название типа курса.',
            ]);
            return;
        }

        $existing = CourseType::findOne("name = ? AND id != ?", [$name, $id]);
        if ($existing) {
            View::render('course_type.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'type' => $type,
                'sections' => $this->sectionsFromPost(),
                'error' => 'Тип курса с таким названием уже существует.',
            ]);
            return;
        }

        CourseType::updateWhere('id = ?', [
            'name' => $name,
            'description' => $description ?: null,
            'order' => $order,
        ], [$id]);

        $this->syncSections($id, $this->sectionsFromPost());

        \Core\Flash::success('Тип курса обновлён.');
        Router::redirect('/course-types/');
    }

    public function delete(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/course-types/');
            return;
        }

        CourseType::deleteWhere('id = ?', [$id]);

        \Core\Flash::success('Тип курса удалён.');
        Router::redirect('/course-types/');
    }

    private function sectionsFromPost(): array
    {
        $names = $_POST['section_name'] ?? [];
        $orders = $_POST['section_order'] ?? [];
        $sections = [];
        foreach ($names as $i => $n) {
            $n = trim($n);
            if ($n === '') continue;
            $sections[] = [
                'name' => $n,
                'order' => isset($orders[$i]) && $orders[$i] !== '' ? (int)$orders[$i] : ($i + 1),
            ];
        }
        return $sections;
    }

    private function syncSections(int $typeId, array $sections): void
    {
        $pdo = \Core\Database::getConnection();
        $pdo->beginTransaction();
        try {
            CourseTypeSection::deleteWhere('course_type_id = ?', [$typeId]);
            foreach ($sections as $sec) {
                CourseTypeSection::create([
                    'course_type_id' => $typeId,
                    'name' => $sec['name'],
                    'order' => $sec['order'],
                ]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
