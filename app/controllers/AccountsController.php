<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\User;
use Models\Course;
use Models\TeacherProfile;
use Models\TeacherGroup;

class AccountsController
{
    public function index(): void
    {
        \Middleware\Middleware::settingsAccess();
        View::render('accounts.index', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
        ]);
    }

    public function admins(): void
    {
        \Middleware\Middleware::settingsAccess();
        $admins = User::where('is_superuser = 1', [], 'last_name ASC');
        View::render('accounts.admins', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'admins' => $admins,
        ]);
    }

    public function adminCreateForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        View::render('accounts.admin_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'admin' => null,
        ]);
    }

    public function adminCreate(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/accounts/admins/create/');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');

        if (!$username || !$password || !$email) {
            View::render('accounts.admin_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'admin' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        if (User::findByUsername($username) || User::findByEmail($email)) {
            View::render('accounts.admin_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'admin' => null,
                'error' => 'Пользователь с таким логином или email уже существует.',
            ]);
            return;
        }

        User::create([
            'username' => $username,
            'email' => $email,
            'password' => User::passwordHash($password),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_superuser' => 1,
            'is_staff' => 1,
            'is_active' => 1,
        ]);

        Router::redirect('/accounts/admins/');
    }

    public function adminEditForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $admin = User::findOrFail($id);
        View::render('accounts.admin_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'admin' => $admin,
        ]);
    }

    public function adminEdit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/accounts/admins/{$id}/edit/");
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email) {
            View::render('accounts.admin_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'admin' => User::findOrFail($id),
                'error' => 'Введите email.',
            ]);
            return;
        }

        $data = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
        if ($password) {
            $data['password'] = User::passwordHash($password);
        }

        User::updateWhere('id = ?', $data, [$id]);

        Router::redirect('/accounts/admins/');
    }

    public function adminDelete(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/accounts/admins/');
            return;
        }

        if ((int)$id === (int)Auth::user()['id']) {
            Router::back();
            return;
        }

        User::deleteWhere('id = ?', [$id]);
        Router::redirect('/accounts/admins/');
    }

    public function teachers(): void
    {
        \Middleware\Middleware::settingsAccess();
        $teachers = User::where('is_superuser = 0', [], 'last_name ASC');
        foreach ($teachers as &$t) {
            $t['teacher_profile'] = TeacherProfile::findOne("user_id = ?", [$t['id']]);
            $t['groups'] = User::groups($t['id']);
        }
        View::render('accounts.teachers', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'teachers' => $teachers,
        ]);
    }

    public function teacherCreateForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        $faculties = \Models\Faculty::all('full_name ASC');
        View::render('accounts.teacher_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'faculties' => $faculties,
            'teacher' => null,
        ]);
    }

    public function teacherCreate(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/accounts/teachers/create/');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $facultyIds = $_POST['faculties'] ?? [];

        if (!$username || !$password || !$email) {
            View::render('accounts.teacher_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'teacher' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        if (User::findByUsername($username)) {
            View::render('accounts.teacher_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'teacher' => null,
                'error' => 'Пользователь с таким логином уже существует.',
            ]);
            return;
        }

        if (User::findByEmail($email)) {
            View::render('accounts.teacher_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'teacher' => null,
                'error' => 'Пользователь с таким email уже существует.',
            ]);
            return;
        }

        $userId = User::create([
            'username' => $username,
            'email' => $email,
            'password' => User::passwordHash($password),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_superuser' => 0,
            'is_staff' => 0,
            'is_active' => 1,
        ]);

        $profileId = TeacherProfile::create([
            'user_id' => $userId,
            'middle_name' => $middleName,
            'department' => $department,
            'position' => $position,
        ]);

        foreach ($facultyIds as $fid) {
            \Core\Database::insert('accounts_teacherprofile_faculties', [
                'teacherprofile_id' => $profileId,
                'faculty_id' => (int)$fid,
            ]);
        }

        Router::redirect('/accounts/teachers/');
    }

    public function teacherEditForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $teacher = User::findOrFail($id);
        $teacher['teacher_profile'] = TeacherProfile::findOne("user_id = ?", [$id]) ?: [];
        if (!empty($teacher['teacher_profile']['id'])) {
            $teacher['teacher_profile']['faculties'] = \Core\Database::fetchAll(
                "SELECT f.id, f.full_name FROM structure_faculty f
                 JOIN accounts_teacherprofile_faculties tpf ON f.id = tpf.faculty_id
                 WHERE tpf.teacherprofile_id = ?",
                [$teacher['teacher_profile']['id']]
            );
        } else {
            $teacher['teacher_profile']['faculties'] = [];
        }
        $faculties = \Models\Faculty::all('full_name ASC');
        View::render('accounts.teacher_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'faculties' => $faculties,
            'teacher' => $teacher,
        ]);
    }

    public function teacherEdit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/accounts/teachers/{$id}/edit/");
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $facultyIds = $_POST['faculties'] ?? [];

        if (!$email) {
            View::render('accounts.teacher_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'teacher' => User::findOrFail($id),
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        $existing = User::findByEmail($email);
        if ($existing && (int)$existing['id'] !== (int)$id) {
            $teacher = User::findOrFail($id);
            $teacher['teacher_profile'] = TeacherProfile::findOne("user_id = ?", [$id]) ?: [];
            $teacher['teacher_profile']['faculties'] = \Core\Database::fetchAll(
                "SELECT f.id, f.full_name FROM structure_faculty f
                 JOIN accounts_teacherprofile_faculties tpf ON f.id = tpf.faculty_id
                 WHERE tpf.teacherprofile_id = ?",
                [$teacher['teacher_profile']['id'] ?? 0]
            );
            View::render('accounts.teacher_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'teacher' => $teacher,
                'error' => 'Пользователь с таким email уже существует.',
            ]);
            return;
        }

        User::updateWhere('id = ?', [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ], [$id]);

        $profile = TeacherProfile::findOne("user_id = ?", [$id]);
        if ($profile) {
            TeacherProfile::updateWhere('id = ?', [
                'middle_name' => $middleName,
                'department' => $department,
                'position' => $position,
            ], [$profile['id']]);
            $profileId = $profile['id'];
        } else {
            $profileId = TeacherProfile::create([
                'user_id' => $id,
                'middle_name' => $middleName,
                'department' => $department,
                'position' => $position,
            ]);
        }

        \Core\Database::delete('accounts_teacherprofile_faculties', 'teacherprofile_id = ?', [$profileId]);
        foreach ($facultyIds as $fid) {
            \Core\Database::insert('accounts_teacherprofile_faculties', [
                'teacherprofile_id' => $profileId,
                'faculty_id' => (int)$fid,
            ]);
        }

        Router::redirect('/accounts/teachers/');
    }

    public function teacherDelete(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/accounts/teachers/');
            return;
        }

        User::deleteWhere('id = ?', [$id]);
        Router::redirect('/accounts/teachers/');
    }

    public function groups(): void
    {
        \Middleware\Middleware::settingsAccess();
        $groups = TeacherGroup::all('name ASC');
        foreach ($groups as &$g) {
            $g['users'] = \Core\Database::fetchAll(
                "SELECT u.id, u.first_name, u.last_name, u.username FROM auth_user u
                 JOIN accounts_teachergroup_users tgu ON u.id = tgu.user_id
                 WHERE tgu.teachergroup_id = ?",
                [$g['id']]
            );
        }
        View::render('accounts.groups', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'groups' => $groups,
        ]);
    }

    public function groupCreateForm(): void
    {
        \Middleware\Middleware::settingsAccess();
        $users = User::all('last_name ASC');
        View::render('accounts.group_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'users' => $users,
            'group' => null,
        ]);
    }

    public function groupCreate(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/accounts/groups/create/');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $userIds = $_POST['users'] ?? [];

        if (!$name) {
            View::render('accounts.group_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'users' => User::all('last_name ASC'),
                'group' => null,
                'error' => 'Введите название группы.',
            ]);
            return;
        }

        $groupId = TeacherGroup::create([
            'name' => $name,
            'description' => $description ?: null,
        ]);

        foreach ($userIds as $uid) {
            \Core\Database::insert('accounts_teachergroup_users', [
                'teachergroup_id' => $groupId,
                'user_id' => (int)$uid,
            ]);
        }

        Router::redirect('/accounts/groups/');
    }

    public function groupEditForm(int $id): void
    {
        \Middleware\Middleware::settingsAccess();
        $group = TeacherGroup::findOrFail($id);
        $users = User::all('last_name ASC');
        $memberIds = array_column(
            \Core\Database::fetchAll("SELECT user_id FROM accounts_teachergroup_users WHERE teachergroup_id = ?", [$id]),
            'user_id'
        );

        View::render('accounts.group_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'users' => $users,
            'group' => $group,
            'member_ids' => $memberIds,
        ]);
    }

    public function groupEdit(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/accounts/groups/{$id}/edit/");
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $userIds = $_POST['users'] ?? [];

        if (!$name) {
            View::render('accounts.group_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'users' => User::all('last_name ASC'),
                'group' => TeacherGroup::findOrFail($id),
                'error' => 'Введите название группы.',
            ]);
            return;
        }

        TeacherGroup::updateWhere('id = ?', [
            'name' => $name,
            'description' => $description ?: null,
        ], [$id]);

        \Core\Database::delete('accounts_teachergroup_users', 'teachergroup_id = ?', [$id]);
        foreach ($userIds as $uid) {
            \Core\Database::insert('accounts_teachergroup_users', [
                'teachergroup_id' => $id,
                'user_id' => (int)$uid,
            ]);
        }

        Router::redirect('/accounts/groups/');
    }

    public function groupDelete(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/accounts/groups/');
            return;
        }

        TeacherGroup::deleteWhere('id = ?', [$id]);
        Router::redirect('/accounts/groups/');
    }

    // Teacher group announcements
    public function announcements(int $groupId): void
    {
        \Middleware\Middleware::settingsAccess();
        $group = TeacherGroup::findOrFail($groupId);
        $announcements = \Core\Database::fetchAll(
            "SELECT a.*, u.username, u.first_name, u.last_name FROM accounts_teachergroupannouncement a
             JOIN auth_user u ON a.author_id = u.id
             WHERE a.teacher_group_id = ?
             ORDER BY a.created_at DESC",
            [$groupId]
        );
        View::render('accounts.announcements', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'group' => $group,
            'announcements' => $announcements,
        ]);
    }

    public function announcementCreateForm(int $groupId): void
    {
        \Middleware\Middleware::settingsAccess();
        $group = TeacherGroup::findOrFail($groupId);
        View::render('accounts.announcement_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'group' => $group,
            'announcement' => null,
        ]);
    }

    public function announcementCreate(int $groupId): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/accounts/groups/{$groupId}/announcements/create/");
            return;
        }

        $text = trim($_POST['text'] ?? '');
        $authorId = Auth::user()['id'];

        if (!$text) {
            View::render('accounts.announcement_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'group' => TeacherGroup::findOrFail($groupId),
                'announcement' => null,
                'error' => 'Введите текст объявления.',
            ]);
            return;
        }

        TeacherGroupAnnouncement::create([
            'teacher_group_id' => $groupId,
            'author_id' => $authorId,
            'text' => $text,
        ]);

        Router::redirect("/accounts/groups/{$groupId}/announcements/");
    }

    public function announcementDelete(int $groupId, int $announcementId): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/accounts/groups/{$groupId}/announcements/");
            return;
        }

        TeacherGroupAnnouncement::deleteWhere('id = ?', [$announcementId]);
        Router::redirect("/accounts/groups/{$groupId}/announcements/");
    }

    // Course permissions management page
    public function coursePermissions(int $courseId): void
    {
        \Middleware\Middleware::settingsAccess();
        $course = Course::findOrFail($courseId);

        $userPermissions = \Core\Database::fetchAll(
            "SELECT cup.id, cup.permission, cup.user_id, u.username, u.first_name, u.last_name, u.is_superuser
             FROM course_courseuserpermission cup
             JOIN auth_user u ON cup.user_id = u.id
             WHERE cup.course_id = ?
             ORDER BY u.last_name ASC, u.first_name ASC",
            [$courseId]
        );

        $groupPermissions = \Core\Database::fetchAll(
            "SELECT cgp.id, cgp.permission, cgp.group_id, g.name
             FROM course_coursegrouppermission cgp
             JOIN auth_group g ON cgp.group_id = g.id
             WHERE cgp.course_id = ?
             ORDER BY g.name ASC",
            [$courseId]
        );

        $allUsers = User::where('is_superuser = 0', [], 'last_name ASC, first_name ASC');
        $allGroups = \Core\Database::fetchAll("SELECT * FROM auth_group ORDER BY name ASC");

        $userPermUserIds = array_column($userPermissions, 'user_id');
        $groupPermGroupIds = array_column($groupPermissions, 'group_id');

        View::render('accounts.course_permissions', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'user_permissions' => $userPermissions,
            'group_permissions' => $groupPermissions,
            'all_users' => $allUsers,
            'all_groups' => $allGroups,
            'user_perm_user_ids' => $userPermUserIds,
            'group_perm_group_ids' => $groupPermGroupIds,
        ]);
    }

    // Course permissions
    public function addCourseUserPermission(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        $permission = $_POST['permission'] ?? 'view';

        if ($userId) {
            $existing = \Models\CourseUserPermission::findOne("course_id = ? AND user_id = ?", [$id, $userId]);
            if ($existing) {
                \Models\CourseUserPermission::updateWhere('id = ?', ['permission' => $permission], [$existing['id']]);
            } else {
                \Models\CourseUserPermission::create([
                    'course_id' => $id,
                    'user_id' => (int)$userId,
                    'permission' => $permission,
                ]);
            }
        }

        Router::redirect("/accounts/permissions/course/{$id}/");
    }

    public function removeCourseUserPermission(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/accounts/permissions/course/{$id}/");
            return;
        }

        $upId = $_POST['up_id'] ?? '';
        if ($upId) {
            \Models\CourseUserPermission::deleteWhere('id = ?', [(int)$upId]);
        }

        Router::redirect("/accounts/permissions/course/{$id}/");
    }

    public function addCourseGroupPermission(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $groupId = $_POST['group_id'] ?? '';
        $permission = $_POST['permission'] ?? 'view';

        if ($groupId) {
            $existing = \Models\CourseGroupPermission::findOne("course_id = ? AND group_id = ?", [$id, $groupId]);
            if ($existing) {
                \Models\CourseGroupPermission::updateWhere('id = ?', ['permission' => $permission], [$existing['id']]);
            } else {
                \Models\CourseGroupPermission::create([
                    'course_id' => $id,
                    'group_id' => (int)$groupId,
                    'permission' => $permission,
                ]);
            }
        }

        Router::redirect("/accounts/permissions/course/{$id}/");
    }

    public function removeCourseGroupPermission(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/accounts/permissions/course/{$id}/");
            return;
        }

        $gpId = $_POST['gp_id'] ?? '';
        if ($gpId) {
            \Models\CourseGroupPermission::deleteWhere('id = ?', [(int)$gpId]);
        }

        Router::redirect("/accounts/permissions/course/{$id}/");
    }
}
