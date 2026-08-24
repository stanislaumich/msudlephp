<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\University;
use Models\Faculty;
use Models\Department;
use Models\User;

class StructureController
{
    public function index(): void
    {
        \Middleware\Middleware::settingsAccess();
        View::render('structure.index', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
        ]);
    }

    public function universities(): void
    {
        \Middleware\Middleware::settingsAccess();
        $universities = University::all('full_name ASC');
        View::render('structure.universities', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'universities' => $universities,
        ]);
    }

    public function universityCreateForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        View::render('structure.university_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'university' => null,
        ]);
    }

    public function universityCreate(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/structure/universities/create/');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');

        if (!$fullName || !$shortName) {
            View::render('structure.university_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'university' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        University::create([
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
        ]);

        Router::redirect('/structure/universities/');
    }

    public function universityEditForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $university = University::findOrFail($id);
        View::render('structure.university_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'university' => $university,
        ]);
    }

    public function universityEdit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/structure/universities/{$id}/edit/");
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');

        if (!$fullName || !$shortName) {
            View::render('structure.university_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'university' => University::findOrFail($id),
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        University::updateWhere('id = ?', [
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
        ], [$id]);

        Router::redirect('/structure/universities/');
    }

    public function universityDelete(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/structure/universities/');
            return;
        }

        $faculties = Faculty::where("university_id = ?", [$id]);
        foreach ($faculties as $faculty) {
            $this->removeDeanGroup((int)$faculty['dean_id']);

            $departments = Department::where("faculty_id = ?", [$faculty['id']]);
            foreach ($departments as $dept) {
                $this->removeHeadGroup((int)$dept['head_id']);
            }

            Department::deleteWhere('faculty_id = ?', [$faculty['id']]);
        }

        Faculty::deleteWhere('university_id = ?', [$id]);
        University::deleteWhere('id = ?', [$id]);

        Router::redirect('/structure/universities/');
    }

    public function faculties(): void
    {
        \Middleware\Middleware::settingsAccess();
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        if ($facultyIds !== null) {
            $faculties = Faculty::where(
                "id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                $facultyIds,
                'full_name ASC'
            );
        } else {
            $faculties = Faculty::all('full_name ASC');
        }

        foreach ($faculties as &$fac) {
            $fac['dean'] = $fac['dean_id'] ? User::find($fac['dean_id']) : null;
        }

        $universities = University::all('full_name ASC');
        $universityMap = [];
        foreach ($universities as $u) $universityMap[$u['id']] = $u;

        View::render('structure.faculties', [
            'user' => $user,
            'role' => $role,
            'faculties' => $faculties,
            'universities' => $universities,
            'university_map' => $universityMap,
        ]);
    }

    public function facultyCreateForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        $universities = University::all('full_name ASC');
        $users = User::all('last_name ASC');

        View::render('structure.faculty_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'universities' => $universities,
            'users' => $users,
            'faculty' => null,
        ]);
    }

    public function facultyCreate(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/structure/faculties/create/');
            return;
        }

        $universityId = $_POST['university'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');
        $deanId = $_POST['dean'] ?? null;

        if (!$universityId || !$fullName || !$shortName) {
            View::render('structure.faculty_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'universities' => University::all('full_name ASC'),
                'users' => User::all('last_name ASC'),
                'faculty' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        $facultyId = Faculty::create([
            'university_id' => (int)$universityId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
            'dean_id' => $deanId ? (int)$deanId : null,
            'group_numbers' => trim($_POST['group_numbers'] ?? ''),
        ]);

        if ($deanId) {
            $this->syncDeanGroup((int)$deanId);
        }

        Router::redirect('/structure/faculties/');
    }

    public function facultyEditForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $faculty = Faculty::findOrFail($id);
        $universities = University::all('full_name ASC');
        $users = User::all('last_name ASC');

        View::render('structure.faculty_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'universities' => $universities,
            'users' => $users,
            'faculty' => $faculty,
        ]);
    }

    public function facultyEdit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/structure/faculties/{$id}/edit/");
            return;
        }

        $faculty = Faculty::findOrFail($id);
        $oldDeanId = $faculty['dean_id'];

        $universityId = $_POST['university'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');
        $deanId = $_POST['dean'] ?? null;

        if (!$universityId || !$fullName || !$shortName) {
            View::render('structure.faculty_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'universities' => University::all('full_name ASC'),
                'users' => User::all('last_name ASC'),
                'faculty' => $faculty,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        Faculty::updateWhere('id = ?', [
            'university_id' => (int)$universityId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
            'dean_id' => $deanId ? (int)$deanId : null,
            'group_numbers' => trim($_POST['group_numbers'] ?? ''),
        ], [$id]);

        if ($oldDeanId != $deanId) {
            if ($oldDeanId && !Faculty::findOne("dean_id = ? AND id != ?", [$oldDeanId, $id])) {
                $this->removeDeanGroup($oldDeanId);
            }
            if ($deanId) {
                $this->syncDeanGroup((int)$deanId);
            }
        }

        Router::redirect('/structure/faculties/');
    }

    public function departments(): void
    {
        \Middleware\Middleware::settingsAccess();
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        if ($facultyIds !== null) {
            $departments = Department::where(
                "faculty_id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                $facultyIds,
                'full_name ASC'
            );
            $faculties = Faculty::where(
                "id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                $facultyIds,
                'full_name ASC'
            );
        } else {
            $departments = Department::all('full_name ASC');
            $faculties = Faculty::all('full_name ASC');
        }

        foreach ($departments as &$dept) {
            $dept['head'] = $dept['head_id'] ? User::find($dept['head_id']) : null;
        }

        $facultyMap = [];
        foreach ($faculties as $f) $facultyMap[$f['id']] = $f;

        View::render('structure.departments', [
            'user' => $user,
            'role' => $role,
            'departments' => $departments,
            'faculties' => $faculties,
            'faculty_map' => $facultyMap,
        ]);
    }

    public function departmentCreateForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        if ($facultyIds !== null) {
            $faculties = Faculty::where(
                "id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                $facultyIds,
                'full_name ASC'
            );
        } else {
            $faculties = Faculty::all('full_name ASC');
        }
        $users = User::all('last_name ASC');

        View::render('structure.department_form', [
            'user' => $user,
            'role' => $role,
            'faculties' => $faculties,
            'users' => $users,
            'department' => null,
        ]);
    }

    public function departmentCreate(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/structure/departments/create/');
            return;
        }

        $facultyId = $_POST['faculty'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');
        $headId = $_POST['head'] ?? null;

        if (!$facultyId || !$fullName || !$shortName) {
            View::render('structure.department_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => Faculty::all('full_name ASC'),
                'users' => User::all('last_name ASC'),
                'department' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        $deptId = Department::create([
            'faculty_id' => (int)$facultyId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
            'head_id' => $headId ? (int)$headId : null,
        ]);

        if ($headId) {
            $this->syncHeadGroup((int)$headId);
        }

        Router::redirect('/structure/departments/');
    }

    public function departmentEditForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $department = Department::findOrFail($id);
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        if ($facultyIds !== null) {
            $faculties = Faculty::where(
                "id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                $facultyIds,
                'full_name ASC'
            );
        } else {
            $faculties = Faculty::all('full_name ASC');
        }
        $users = User::all('last_name ASC');

        View::render('structure.department_form', [
            'user' => $user,
            'role' => $role,
            'faculties' => $faculties,
            'users' => $users,
            'department' => $department,
        ]);
    }

    public function departmentEdit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/structure/departments/{$id}/edit/");
            return;
        }

        $department = Department::findOrFail($id);
        $oldHeadId = $department['head_id'];

        $facultyId = $_POST['faculty'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');
        $headId = $_POST['head'] ?? null;

        if (!$facultyId || !$fullName || !$shortName) {
            View::render('structure.department_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => Faculty::all('full_name ASC'),
                'users' => User::all('last_name ASC'),
                'department' => $department,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        Department::updateWhere('id = ?', [
            'faculty_id' => (int)$facultyId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
            'head_id' => $headId ? (int)$headId : null,
        ], [$id]);

        if ($oldHeadId != $headId) {
            if ($oldHeadId && !Department::findOne("head_id = ? AND id != ?", [$oldHeadId, $id])) {
                $this->removeHeadGroup($oldHeadId);
            }
            if ($headId) {
                $this->syncHeadGroup((int)$headId);
            }
        }

        Router::redirect('/structure/departments/');
    }

    private function syncDeanGroup(int $userId): void
    {
        $group = \Core\Database::findOne("SELECT id FROM auth_group WHERE name = 'Декан'");
        if (!$group) {
            $groupId = \Core\Database::insert('auth_group', ['name' => 'Декан']);
            $group = ['id' => $groupId];
        }
        $tg = \Core\Database::findOne("SELECT id FROM accounts_teachergroup WHERE name = 'Деканы'");
        if (!$tg) {
            $tgId = \Core\Database::insert('accounts_teachergroup', ['name' => 'Деканы', 'description' => 'Деканы факультетов']);
            $tg = ['id' => $tgId];
        }

        if (!\Core\Database::findOne("SELECT id FROM auth_user_groups WHERE user_id = ? AND group_id = ?", [$userId, $group['id']])) {
            \Core\Database::insert('auth_user_groups', ['user_id' => $userId, 'group_id' => $group['id']]);
        }
        if (!\Core\Database::findOne("SELECT id FROM accounts_teachergroup_users WHERE user_id = ? AND teachergroup_id = ?", [$userId, $tg['id']])) {
            \Core\Database::insert('accounts_teachergroup_users', ['user_id' => $userId, 'teachergroup_id' => $tg['id']]);
        }
    }

    private function removeDeanGroup(int $userId): void
    {
        $group = \Core\Database::findOne("SELECT id FROM auth_group WHERE name = 'Декан'");
        $tg = \Core\Database::findOne("SELECT id FROM accounts_teachergroup WHERE name = 'Деканы'");

        if ($group) \Core\Database::delete('auth_user_groups', 'user_id = ? AND group_id = ?', [$userId, $group['id']]);
        if ($tg) \Core\Database::delete('accounts_teachergroup_users', 'user_id = ? AND teachergroup_id = ?', [$userId, $tg['id']]);
    }

    private function syncHeadGroup(int $userId): void
    {
        $group = \Core\Database::findOne("SELECT id FROM auth_group WHERE name = 'Заведующий кафедрой'");
        if (!$group) {
            $groupId = \Core\Database::insert('auth_group', ['name' => 'Заведующий кафедрой']);
            $group = ['id' => $groupId];
        }
        $tg = \Core\Database::findOne("SELECT id FROM accounts_teachergroup WHERE name = 'Заведующий кафедрой'");
        if (!$tg) {
            $tgId = \Core\Database::insert('accounts_teachergroup', ['name' => 'Заведующий кафедрой', 'description' => 'Заведующие кафедрами']);
            $tg = ['id' => $tgId];
        }

        if (!\Core\Database::findOne("SELECT id FROM auth_user_groups WHERE user_id = ? AND group_id = ?", [$userId, $group['id']])) {
            \Core\Database::insert('auth_user_groups', ['user_id' => $userId, 'group_id' => $group['id']]);
        }
        if (!\Core\Database::findOne("SELECT id FROM accounts_teachergroup_users WHERE user_id = ? AND teachergroup_id = ?", [$userId, $tg['id']])) {
            \Core\Database::insert('accounts_teachergroup_users', ['user_id' => $userId, 'teachergroup_id' => $tg['id']]);
        }
    }

    private function removeHeadGroup(int $userId): void
    {
        $group = \Core\Database::findOne("SELECT id FROM auth_group WHERE name = 'Заведующий кафедрой'");
        $tg = \Core\Database::findOne("SELECT id FROM accounts_teachergroup WHERE name = 'Заведующий кафедрой'");

        if ($group) \Core\Database::delete('auth_user_groups', 'user_id = ? AND group_id = ?', [$userId, $group['id']]);
        if ($tg) \Core\Database::delete('accounts_teachergroup_users', 'user_id = ? AND teachergroup_id = ?', [$userId, $tg['id']]);
    }
}