<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\Student;
use Models\StudentGroup;
use Models\DeletedStudent;
use Models\GroupAnnouncement;

class StudentsController
{
    public function list(): void
    {
        \Middleware\Middleware::teacher();

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        if ($facultyIds !== null) {
            $groups = StudentGroup::where(
                "faculty_id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                $facultyIds,
                'group_number ASC'
            );
            $students = Student::where(
                "group_id IN (SELECT id FROM students_studentgroup WHERE faculty_id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . "))",
                $facultyIds
            );
        } else {
            $groups = StudentGroup::all('group_number ASC');
            $students = Student::all('fio ASC');
        }

        $groupId = $_GET['group'] ?? null;
        if ($groupId) {
            $students = array_filter($students, fn($s) => $s['group_id'] == $groupId);
        }

        $search = trim($_GET['search'] ?? '');
        if ($search) {
            $searchLower = mb_strtolower($search);
            $students = array_filter($students, fn($s) =>
                mb_strpos(mb_strtolower($s['fio']), $searchLower) !== false ||
                mb_strpos(mb_strtolower($s['login']), $searchLower) !== false
            );
        }

        $sortField = $_GET['sort'] ?? 'fio';
        $sortDir = $_GET['dir'] ?? 'asc';

        $allowedSortFields = ['fio', 'login', 'group_id'];
        if (!in_array($sortField, $allowedSortFields)) $sortField = 'fio';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'asc';

        usort($students, function ($a, $b) use ($sortField, $sortDir) {
            $valA = $a[$sortField] ?? '';
            $valB = $b[$sortField] ?? '';
            if (is_numeric($valA) && is_numeric($valB)) {
                $cmp = (float)$valA <=> (float)$valB;
            } else {
                $cmp = strcmp((string)$valA, (string)$valB);
            }
            return $sortDir === 'asc' ? $cmp : -$cmp;
        });

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $total = count($students);
        $offset = ($page - 1) * $perPage;
        $students = array_slice($students, $offset, $perPage);

        View::render('students.list', [
            'user' => $user,
            'role' => $role,
            'students' => $students,
            'groups' => $groups,
            'current_group' => $groupId ? (int)$groupId : null,
            'search' => $search,
            'sort' => $sortField,
            'dir' => $sortDir,
            'page' => $page,
            'total' => $total,
            'per_page' => $perPage,
        ]);
    }

    public function createForm(): void
    {
        \Middleware\Middleware::teacher();
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        $groups = [];
        if ($facultyIds !== null) {
            $groups = StudentGroup::where("faculty_id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")", $facultyIds, 'group_number ASC');
        } else {
            $groups = StudentGroup::all('group_number ASC');
        }

        View::render('students.create', [
            'user' => $user,
            'role' => $role,
            'groups' => $groups,
            'student' => null,
        ]);
    }

    public function create(): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/students/create/');
            return;
        }

        $fio = trim($_POST['fio'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $groupId = $_POST['group'] ?? '';
        $recordBook = trim($_POST['record_book_number'] ?? '');

        $errors = [];
        if (!$fio) $errors[] = 'Введите ФИО студента.';
        if (!$login) $errors[] = 'Введите логин.';
        elseif (Student::findByLogin($login)) $errors[] = 'Студент с таким логином уже существует.';
        if (!$password) $errors[] = 'Введите пароль.';
        if (!$groupId) $errors[] = 'Выберите группу.';

        if ($errors) {
            $user = Auth::user();
            $role = Role::getActiveRole($user);
            $groups = StudentGroup::all('group_number ASC');
            View::render('students.create', [
                'user' => $user, 'role' => $role, 'groups' => $groups, 'student' => null, 'errors' => $errors
            ]);
            return;
        }

        $studentId = Student::create([
            'fio' => $fio,
            'login' => $login,
            'group_id' => $groupId ?: null,
            'record_book_number' => $recordBook,
            'password' => Student::passwordHash($password),
        ]);

        Router::redirect('/students/');
    }

    public function editForm(int $studentId): void
    {
        \Middleware\Middleware::teacher();
        $student = Student::findOrFail($studentId);
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        $groups = [];
        if ($facultyIds !== null) {
            $groups = StudentGroup::where("faculty_id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")", $facultyIds, 'group_number ASC');
        } else {
            $groups = StudentGroup::all('group_number ASC');
        }

        View::render('students.create', [
            'user' => $user,
            'role' => $role,
            'groups' => $groups,
            'student' => $student,
        ]);
    }

    public function edit(int $studentId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/students/{$studentId}/edit/");
            return;
        }

        $student = Student::findOrFail($studentId);

        $fio = trim($_POST['fio'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $groupId = $_POST['group'] ?? '';
        $recordBook = trim($_POST['record_book_number'] ?? '');

        $errors = [];
        if (!$fio) $errors[] = 'Введите ФИО студента.';
        if (!$login) $errors[] = 'Введите логин.';
        elseif (Student::findByLogin($login) && Student::findByLogin($login)['id'] != $studentId) {
            $errors[] = 'Студент с таким логином уже существует.';
        }
        if (!$groupId) $errors[] = 'Выберите группу.';

        if ($errors) {
            $user = Auth::user();
            $role = Role::getActiveRole($user);
            $groups = StudentGroup::all('group_number ASC');
            View::render('students.create', [
                'user' => $user, 'role' => $role, 'groups' => $groups, 'student' => $student, 'errors' => $errors
            ]);
            return;
        }

        $data = [
            'fio' => $fio,
            'login' => $login,
            'group_id' => $groupId ?: null,
            'record_book_number' => $recordBook,
        ];
        if ($password) {
            $data['password'] = Student::passwordHash($password);
        }

        Student::updateWhere('id = ?', $data, [$studentId]);
        Router::redirect('/students/');
    }

    public function delete(int $studentId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/students/');
            return;
        }

        Student::deleteWhere('id = ?', [$studentId]);
        Router::redirect('/students/');
    }

    public function softDelete(int $studentId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/students/');
            return;
        }

        $student = Student::findOrFail($studentId);

        DeletedStudent::create([
            'original_id' => $student['id'],
            'fio' => $student['fio'],
            'login' => $student['login'],
            'record_book_number' => $student['record_book_number'],
            'password' => $student['password'],
            'group_id' => $student['group_id'],
            'group_name' => $student['group_id'] ? (StudentGroup::find($student['group_id'])['group_number'] ?? '') : '',
            'last_login' => $student['last_login'],
        ]);

        Student::deleteWhere('id = ?', [$studentId]);

        $groupId = $student['group_id'] ?? null;
        if ($groupId) {
            Router::redirect("/students/groups/{$groupId}/edit/");
        }
        Router::redirect('/students/groups/');
    }

    public function groupList(): void
    {
        \Middleware\Middleware::teacher();

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        if ($facultyIds !== null) {
            $groups = StudentGroup::where(
                "faculty_id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                $facultyIds,
                'group_number ASC'
            );
            $faculties = \Core\Database::fetchAll(
                "SELECT * FROM structure_faculty WHERE id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ") ORDER BY full_name",
                $facultyIds
            );
        } else {
            $groups = StudentGroup::all('group_number ASC');
            $faculties = \Core\Database::fetchAll("SELECT * FROM structure_faculty ORDER BY full_name");
        }

        $shifrs = \Models\Shifr::all('code ASC');

        $facultyId = $_GET['faculty'] ?? null;
        if ($facultyId) {
            $groups = array_filter($groups, fn($g) => $g['faculty_id'] == $facultyId);
        }

        $groupSearch = trim($_GET['group_search'] ?? '');
        if ($groupSearch) {
            $groups = array_filter($groups, fn($g) => mb_stripos($g['group_number'], $groupSearch) !== false);
        }

        $currentFacultyLabel = '';
        if ($facultyId) {
            $faculty = \Core\Database::fetch("SELECT full_name FROM structure_faculty WHERE id = ?", [$facultyId]);
            $currentFacultyLabel = $faculty ? $faculty['full_name'] : '';
        }

        $facultyMap = [];
        foreach ($faculties as $f) $facultyMap[$f['id']] = $f;
        $shifrMap = [];
        foreach ($shifrs as $s) $shifrMap[$s['id']] = $s;

        $studentCounts = [];
        foreach ($groups as $g) {
            $studentCounts[$g['id']] = \Core\Database::count('students_student', 'group_id = ?', [$g['id']]);
        }

        View::render('students.group_list', [
            'user' => $user,
            'role' => $role,
            'groups' => $groups,
            'faculties' => $faculties,
            'shifrs' => $shifrs,
            'faculty_map' => $facultyMap,
            'shifr_map' => $shifrMap,
            'current_faculty' => $facultyId,
            'current_faculty_label' => $currentFacultyLabel,
            'group_search' => $groupSearch,
            'student_counts' => $studentCounts,
        ]);
    }

    public function groupCreateForm(): void
    {
        \Middleware\Middleware::teacher();
        $user = Auth::user();
        $role = Role::getActiveRole($user);

        $shifrs = \Models\Shifr::all('code ASC');
        $faculties = \Models\Faculty::all('full_name ASC');

        View::render('students.group_create', [
            'user' => $user,
            'role' => $role,
            'shifrs' => $shifrs,
            'faculties' => $faculties,
            'group' => null,
        ]);
    }

    public function groupCreate(): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/students/groups/create/');
            return;
        }

        $groupNumber = trim($_POST['group_number'] ?? '');
        $shifrId = $_POST['shifr'] ?? null;
        $enrollmentYear = trim($_POST['enrollment_year'] ?? '');
        $studyDurationYears = trim($_POST['study_duration_years'] ?? '');
        $studyDurationMonths = trim($_POST['study_duration_months'] ?? '');
        $facultyId = $_POST['faculty'] ?? null;
        $educationForm = trim($_POST['education_form'] ?? '');

        if (!$groupNumber) {
            View::render('students.group_create', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'shifrs' => \Models\Shifr::all('code ASC'),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'error' => 'Введите номер группы.',
            ]);
            return;
        }

        if (StudentGroup::findOne("group_number = ?", [$groupNumber])) {
            View::render('students.group_create', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'shifrs' => \Models\Shifr::all('code ASC'),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'error' => 'Группа с таким номером уже существует.',
            ]);
            return;
        }

        StudentGroup::create([
            'group_number' => $groupNumber,
            'shifr_id' => $shifrId ?: null,
            'enrollment_year' => $enrollmentYear ? (int)$enrollmentYear : null,
            'study_duration_years' => $studyDurationYears ? (int)$studyDurationYears : null,
            'study_duration_months' => $studyDurationMonths ? (int)$studyDurationMonths : null,
            'faculty_id' => $facultyId ?: null,
            'education_form' => $educationForm ?: null,
        ]);

        Router::redirect('/students/groups/');
    }

    public function groupEditForm(int $groupId): void
    {
        \Middleware\Middleware::teacher();
        $group = StudentGroup::findOrFail($groupId);
        $user = Auth::user();
        $role = Role::getActiveRole($user);

        $students = Student::where("group_id = ?", [$groupId], 'fio ASC');
        $shifrLabel = $group['shifr_id'] ? (\Models\Shifr::find($group['shifr_id'])['code'] ?? '') : '';

        View::render('students.group_create', [
            'user' => $user,
            'role' => $role,
            'shifrs' => \Models\Shifr::all('code ASC'),
            'faculties' => \Models\Faculty::all('full_name ASC'),
            'group' => $group,
            'students' => $students,
            'shifr_label' => $shifrLabel,
        ]);
    }

    public function groupEdit(int $groupId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/students/groups/{$groupId}/edit/");
            return;
        }

        $group = StudentGroup::findOrFail($groupId);

        $groupNumber = trim($_POST['group_number'] ?? '');
        $shifrId = $_POST['shifr'] ?? null;
        $enrollmentYear = trim($_POST['enrollment_year'] ?? '');
        $studyDurationYears = trim($_POST['study_duration_years'] ?? '');
        $studyDurationMonths = trim($_POST['study_duration_months'] ?? '');
        $facultyId = $_POST['faculty'] ?? null;
        $educationForm = trim($_POST['education_form'] ?? '');

        if (!$groupNumber) {
            View::render('students.group_create', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'shifrs' => \Models\Shifr::all('code ASC'),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'group' => $group,
                'error' => 'Введите номер группы.',
            ]);
            return;
        }

        if (StudentGroup::findOne("group_number = ? AND id != ?", [$groupNumber, $groupId])) {
            View::render('students.group_create', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'shifrs' => \Models\Shifr::all('code ASC'),
                'faculties' => \Models\Faculty::all('full_name ASC'),
                'group' => $group,
                'error' => 'Группа с таким номером уже существует.',
            ]);
            return;
        }

        StudentGroup::updateWhere('id = ?', [
            'group_number' => $groupNumber,
            'shifr_id' => $shifrId ?: null,
            'enrollment_year' => $enrollmentYear ? (int)$enrollmentYear : null,
            'study_duration_years' => $studyDurationYears ? (int)$studyDurationYears : null,
            'study_duration_months' => $studyDurationMonths ? (int)$studyDurationMonths : null,
            'faculty_id' => $facultyId ?: null,
            'education_form' => $educationForm ?: null,
        ], [$groupId]);

        Router::redirect('/students/groups/');
    }

    public function groupDelete(int $groupId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/students/groups/');
            return;
        }

        $studentCount = \Core\Database::count('students_student', 'group_id = ?', [$groupId]);
        if ($studentCount > 0) {
            \Core\Flash::error('Нельзя удалить группу: в ней есть студенты.');
            Router::redirect('/students/groups/');
            return;
        }

        StudentGroup::deleteWhere('id = ?', [$groupId]);
        Router::redirect('/students/groups/');
    }

    public function archiveList(): void
    {
        \Middleware\Middleware::teacher();
        $deleted = DeletedStudent::all('-deleted_at');

        View::render('students.archive_list', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'deleted_students' => $deleted,
        ]);
    }

    public function archiveRestoreForm(int $deletedId): void
    {
        \Middleware\Middleware::teacher();
        $deleted = DeletedStudent::findOrFail($deletedId);
        $groups = StudentGroup::all('group_number ASC');

        View::render('students.archive_restore', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'deleted' => $deleted,
            'groups' => $groups,
        ]);
    }

    public function archiveRestore(int $deletedId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/students/archive/');
            return;
        }

        $deleted = DeletedStudent::findOrFail($deletedId);

        if (Student::findByLogin($deleted['login'])) {
            View::render('students.archive_restore', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'deleted' => $deleted,
                'groups' => StudentGroup::all('group_number ASC'),
                'error' => "Невозможно восстановить: логин «{$deleted['login']}» уже занят.",
            ]);
            return;
        }

        $groupId = $_POST['group'] ?? '';
        $group = $groupId ? StudentGroup::find((int)$groupId) : null;

        try {
            Student::create([
                'id' => $deleted['original_id'],
                'fio' => $deleted['fio'],
                'login' => $deleted['login'],
                'group_id' => $group ? $group['id'] : null,
                'record_book_number' => $deleted['record_book_number'],
                'password' => $deleted['password'],
                'last_login' => $deleted['last_login'],
            ]);
        } catch (\Exception $e) {
            Student::create([
                'fio' => $deleted['fio'],
                'login' => $deleted['login'],
                'group_id' => $group ? $group['id'] : null,
                'record_book_number' => $deleted['record_book_number'],
                'password' => $deleted['password'],
                'last_login' => $deleted['last_login'],
            ]);
        }

        DeletedStudent::deleteWhere('id = ?', [$deletedId]);
        Router::redirect('/students/archive/');
    }

    public function exportCsv(): void
    {
        \Middleware\Middleware::teacher();

        $groupId = $_GET['group'] ?? null;
        $search = trim($_GET['search'] ?? '');

        $students = Student::all('fio ASC');
        $groups = StudentGroup::all('group_number ASC');
        $groupMap = [];
        foreach ($groups as $g) $groupMap[$g['id']] = $g;

        if ($groupId) {
            $students = array_filter($students, fn($s) => ($s['group_id'] ?? null) == $groupId);
        }
        if ($search) {
            $searchLower = mb_strtolower($search);
            $students = array_filter($students, fn($s) =>
                mb_strpos(mb_strtolower($s['fio']), $searchLower) !== false ||
                mb_strpos(mb_strtolower($s['login']), $searchLower) !== false
            );
        }

        $lines = [];
        $lines[] = ['ФИО', 'Логин', 'Группа', 'Номер зачётки'];
        foreach ($students as $s) {
            $groupName = $s['group_id'] && isset($groupMap[$s['group_id']])
                ? $groupMap[$s['group_id']]['group_number']
                : '';
            $lines[] = [
                $s['fio'] ?? '',
                $s['login'] ?? '',
                $groupName,
                $s['record_book_number'] ?? '',
            ];
        }

        $csvContent = '';
        foreach ($lines as $line) {
            $escaped = array_map(function($field) {
                $field = str_replace('"', '""', $field);
                return '"' . $field . '"';
            }, $line);
            $csvContent .= implode(',', $escaped) . "\r\n";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');
        echo $csvContent;
        exit;
    }

    public function importForm(): void
    {
        \Middleware\Middleware::settingsAccess();

        $groups = StudentGroup::all('group_number ASC');

        View::render('students.import', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'groups' => $groups,
            'errors' => $_SESSION['student_import_errors'] ?? [],
            'success_count' => $_SESSION['student_import_count'] ?? 0,
        ]);

        unset($_SESSION['student_import_errors'], $_SESSION['student_import_count']);
    }

    public function import(): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['csv_file']['tmp_name'])) {
            Router::redirect('/students/import/');
            return;
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['student_import_errors'] = ['Ошибка загрузки файла.'];
            Router::redirect('/students/import/');
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $_SESSION['student_import_errors'] = ['Файл должен быть CSV.'];
            Router::redirect('/students/import/');
            return;
        }

        $content = file_get_contents($file['tmp_name']);
        $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
        $lines = str_getcsv($content, "\r\n");

        $errors = [];
        $imported = 0;
        $header = null;

        foreach ($lines as $i => $line) {
            if (empty(trim($line))) continue;

            $fields = str_getcsv($line);

            if ($i === 0) {
                $header = array_map('trim', $fields);
                continue;
            }

            if (count($fields) < 2) {
                $errors[] = "Строка " . ($i + 1) . ": недостаточно полей.";
                continue;
            }

            $fio = trim($fields[0] ?? '');
            $login = trim($fields[1] ?? '');
            $password = trim($fields[2] ?? '');
            $groupNumber = trim($fields[3] ?? '');
            $recordBook = trim($fields[4] ?? '');

            if (!$fio || !$login || !$password) {
                $errors[] = "Строка " . ($i + 1) . ": ФИО, логин и пароль обязательны.";
                continue;
            }

            if (Student::findByLogin($login)) {
                $errors[] = "Строка " . ($i + 1) . ": студент с логином «{$login}» уже существует.";
                continue;
            }

            $groupId = null;
            if ($groupNumber) {
                $group = StudentGroup::findOne("group_number = ?", [$groupNumber]);
                if ($group) {
                    $groupId = $group['id'];
                } else {
                    $errors[] = "Строка " . ($i + 1) . ": группа «{$groupNumber}» не найдена.";
                    continue;
                }
            }

            Student::create([
                'fio' => $fio,
                'login' => $login,
                'group_id' => $groupId,
                'record_book_number' => $recordBook,
                'password' => Student::passwordHash($password),
            ]);
            $imported++;
        }

        $_SESSION['student_import_errors'] = $errors;
        $_SESSION['student_import_count'] = $imported;
        Router::redirect('/students/import/');
    }

    public function importBatchForm(): void
    {
        \Middleware\Middleware::teacher();

        View::render('students.import_batch', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'errors' => $_SESSION['student_import_errors'] ?? [],
            'success_count' => $_SESSION['student_import_count'] ?? 0,
            'created_faculties' => $_SESSION['student_import_faculties'] ?? [],
            'created_groups' => $_SESSION['student_import_groups'] ?? [],
        ]);

        unset($_SESSION['student_import_errors'], $_SESSION['student_import_count'], $_SESSION['student_import_faculties'], $_SESSION['student_import_groups']);
    }

    public function importBatch(): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/students/import/batch/');
            return;
        }

        $csvPath = dirname(__DIR__, 2) . '/students.csv';
        if (!file_exists($csvPath)) {
            $_SESSION['student_import_errors'] = ['Файл students.csv не найден в корне проекта.'];
            Router::redirect('/students/import/batch/');
            return;
        }

        $content = file_get_contents($csvPath);
        $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
        $content = str_replace("\xEF\xBB\xBF", '', $content);
        $lines = preg_split('/\r\n|\r|\n/', $content);

        $errors = [];
        $imported = 0;
        $createdFaculties = [];
        $createdGroups = [];
        $header = null;

        $university = \Core\Database::fetch("SELECT * FROM structure_university LIMIT 1");
        if (!$university) {
            $universityId = \Core\Database::insert('structure_university', [
                'full_name' => 'Московский государственный университет',
                'short_name' => 'МГУ',
                'identifier' => 'msu',
            ]);
        } else {
            $universityId = $university['id'];
        }

        $facultyCache = [];
        $groupCache = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if ($line === '') continue;

            $fields = str_getcsv($line, ';');

            if ($i === 0) {
                $header = array_map('trim', $fields);
                continue;
            }

            if (count($fields) < 9) {
                $errors[] = "Строка " . ($i + 1) . ": недостаточно полей.";
                continue;
            }

            $surname = trim($fields[0] ?? '');
            $name = trim($fields[1] ?? '');
            $patronymic = trim($fields[2] ?? '');
            $facultyName = trim($fields[3] ?? '');
            $groupNumber = trim($fields[7] ?? '');
            $recordBook = trim($fields[8] ?? '');

            $fio = trim($surname . ' ' . $name . ' ' . $patronymic);

            if (!$fio || !$groupNumber || !$recordBook) {
                $errors[] = "Строка " . ($i + 1) . ": недостаточно данных (ФИО, группа или номер зачётки).";
                continue;
            }

            if (!isset($facultyCache[$facultyName])) {
                $faculty = \Core\Database::fetch("SELECT * FROM structure_faculty WHERE full_name = ?", [$facultyName]);
                if (!$faculty) {
                    $shortName = $this->generateShortName($facultyName);
                    $identifier = $this->generateIdentifier($facultyName);
                    $facultyId = \Core\Database::insert('structure_faculty', [
                        'university_id' => $universityId,
                        'full_name' => $facultyName,
                        'short_name' => $shortName,
                        'identifier' => $identifier,
                    ]);
                    $createdFaculties[] = $facultyName;
                } else {
                    $facultyId = $faculty['id'];
                }
                $facultyCache[$facultyName] = $facultyId;
            }
            $facultyId = $facultyCache[$facultyName];

            $groupKey = $groupNumber . '|' . $facultyId;
            if (!isset($groupCache[$groupKey])) {
                $group = \Core\Database::fetch("SELECT * FROM students_studentgroup WHERE group_number = ? AND faculty_id = ?", [$groupNumber, $facultyId]);
                if (!$group) {
                    $groupId = \Core\Database::insert('students_studentgroup', [
                        'group_number' => $groupNumber,
                        'faculty_id' => $facultyId,
                    ]);
                    $createdGroups[] = $groupNumber . ' (' . $facultyName . ')';
                } else {
                    $groupId = $group['id'];
                }
                $groupCache[$groupKey] = $groupId;
            }
            $groupId = $groupCache[$groupKey];

            $existing = Student::findByLogin($recordBook);
            if ($existing) {
                $errors[] = "Строка " . ($i + 1) . ": студент с номером зачётки «{$recordBook}» уже существует.";
                continue;
            }

            $existingRb = \Core\Database::fetch("SELECT id FROM students_student WHERE record_book_number = ?", [$recordBook]);
            if ($existingRb) {
                $errors[] = "Строка " . ($i + 1) . ": номер зачётки «{$recordBook}» уже используется.";
                continue;
            }

            Student::create([
                'fio' => $fio,
                'login' => $recordBook,
                'group_id' => $groupId,
                'record_book_number' => $recordBook,
                'password' => Student::passwordHash($recordBook),
            ]);
            $imported++;
        }

        $_SESSION['student_import_errors'] = $errors;
        $_SESSION['student_import_count'] = $imported;
        $_SESSION['student_import_faculties'] = $createdFaculties;
        $_SESSION['student_import_groups'] = $createdGroups;
        Router::redirect('/students/import/batch/');
    }

    private function generateShortName(string $fullName): string
    {
        $words = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $short = '';
        foreach ($words as $w) {
            $short .= mb_substr($w, 0, 1);
            if (mb_strlen($short) >= 10) break;
        }
        return mb_strtoupper($short);
    }

    private function generateIdentifier(string $fullName): string
    {
        $translit = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
            'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
            'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '',
            'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        ];
        $result = '';
        $name = $fullName;
        for ($j = 0; $j < mb_strlen($name); $j++) {
            $char = mb_substr($name, $j, 1);
            $result .= $translit[$char] ?? $char;
        }
        $result = preg_replace('/[^a-zA-Z0-9_]/', '_', $result);
        $result = preg_replace('/_+/', '_', $result);
        $result = trim($result, '_');
        return mb_substr($result, 0, 50);
    }

    public function groupReport(int $groupId): void
    {
        \Middleware\Middleware::teacher();

        $group = StudentGroup::findOrFail($groupId);
        $students = Student::where("group_id = ?", [$groupId], 'fio ASC');

        $reportRows = [];
        foreach ($students as $student) {
            $units = \Core\Database::fetchAll(
                "SELECT lu.id, lu.title FROM course_learningunit lu
                 JOIN course_coursesection cs ON lu.section_id = cs.id
                 JOIN course_course c ON cs.course_id = c.id
                 WHERE c.is_deleted = 0 AND lu.content_type = 'control'
                 UNION
                 SELECT lu.id, lu.title FROM course_learningunit lu
                 JOIN course_coursetopic ct ON lu.topic_id = ct.id
                 JOIN course_coursesection cs ON ct.section_id = cs.id
                 JOIN course_course c ON cs.course_id = c.id
                 WHERE c.is_deleted = 0 AND lu.content_type = 'control'
                 ORDER BY id"
            );

            $unitIds = array_column($units, 'id');
            $scores = [];
            $totalScore = 0;
            $checkedCount = 0;
            $checkedTotal = 0;

            if ($unitIds) {
                $ph = implode(',', array_fill(0, count($unitIds), '?'));
                $answers = \Core\Database::fetchAll(
                    "SELECT learning_unit_id, score, checked FROM course_studentanswer WHERE student_id = ? AND learning_unit_id IN ({$ph})",
                    array_merge([$student['id']], $unitIds)
                );
                $answerMap = [];
                foreach ($answers as $a) $answerMap[$a['learning_unit_id']] = $a;
                foreach ($units as $u) {
                    $ans = $answerMap[$u['id']] ?? null;
                    if ($ans && $ans['checked'] && $ans['score'] !== null) {
                        $totalScore += (float)$ans['score'];
                        $checkedTotal++;
                        $scores[] = $ans['score'];
                    }
                    if ($ans && $ans['checked']) $checkedCount++;
                }
            }

            $avgScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;

            $reportRows[] = [
                'student' => $student,
                'avg_score' => $avgScore,
                'total_score' => $totalScore,
                'checked_count' => $checkedCount,
                'total_units' => count($units),
                'unit_ids' => $unitIds,
            ];
        }

        View::render('students.group_report', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'group' => $group,
            'report_rows' => $reportRows,
        ]);
    }
}
