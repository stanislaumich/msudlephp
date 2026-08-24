<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\Course;
use Models\Subject;
use Models\CourseType;
use Models\CourseTypeSection;
use Models\CourseSection;
use Models\CourseTopic;
use Models\LearningUnit;
use Models\CourseUserPermission;
use Models\CourseGroupPermission;
use Models\CourseGroupStudent;
use Models\StudentAnswer;
use Models\CourseAnnouncement;
use Models\AnnouncementDismiss;
use Models\Step;
use Models\StepQuestion;
use Models\StepChoice;
use Models\StepProgress;

class CourseController
{
    public function index(): void
    {
        \Middleware\Middleware::teacher();

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $visibleIds = Role::getVisibleCourseIds($user, $role);

        if ($visibleIds !== null) {
            $allCourses = Course::where('is_deleted = 0');
            $courses = array_filter($allCourses, fn($c) => in_array($c['id'], $visibleIds));
        } else {
            $courses = Course::where('is_deleted = 0');
        }

        foreach ($courses as &$c) {
            $c['subject'] = $c['subject_id'] ? Subject::find($c['subject_id']) : null;
            $c['course_type'] = $c['course_type_id'] ? CourseType::find($c['course_type_id']) : null;
        }

        View::render('course.index', [
            'user' => $user,
            'role' => $role,
            'courses' => $courses,
        ]);
    }

    public function createForm(): void
    {
        \Middleware\Middleware::teacher();
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        $subjects = Subject::all('full_name ASC');
        if ($facultyIds !== null) {
            $subjects = array_filter($subjects, function ($s) use ($facultyIds) {
                $dept = \Core\Database::fetch("SELECT faculty_id FROM structure_department WHERE id = ?", [$s['department_id']]);
                return $dept && in_array($dept['faculty_id'], $facultyIds);
            });
        }

        $courseTypes = CourseType::all('`order` ASC');

        View::render('course.form', [
            'user' => $user,
            'role' => $role,
            'subjects' => $subjects,
            'course_types' => $courseTypes,
            'course' => null,
        ]);
    }

    public function create(): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/courses/create/');
            return;
        }

        $subjectId = $_POST['subject'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');
        $courseTypeId = $_POST['course_type'] ?? null;

        if (!$subjectId || !$fullName || !$shortName) {
            View::render('course.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'subjects' => Subject::all('full_name ASC'),
                'course_types' => CourseType::all('`order` ASC'),
                'course' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        if ($identifier) {
            $existing = Course::findOne("identifier = ?", [$identifier]);
            if ($existing) {
                View::render('course.form', [
                    'user' => Auth::user(),
                    'role' => Role::getActiveRole(Auth::user()),
                    'subjects' => Subject::all('full_name ASC'),
                    'course_types' => CourseType::all('`order` ASC'),
                    'course' => null,
                    'error' => 'Курс с таким идентификатором уже существует.',
                ]);
                return;
            }
        }

        $courseId = Course::create([
            'subject_id' => (int)$subjectId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
            'course_type_id' => $courseTypeId ? (int)$courseTypeId : null,
        ]);

        if ($courseTypeId) {
            $this->createDefaultSections((int)$courseTypeId, $courseId);
        }

        Router::redirect('/courses/');
    }

    public function show(int $id): void
    {
        \Middleware\Middleware::teacher();

        $course = Course::findOrFail($id);
        $course['subject'] = Subject::find($course['subject_id']);

        $sections = CourseSection::where("course_id = ?", [$id], '`order` ASC');
        foreach ($sections as &$sec) {
            $sec['topics'] = CourseTopic::where("section_id = ?", [$sec['id']], '`order` ASC');
            foreach ($sec['topics'] as &$topic) {
                $topic['units'] = LearningUnit::where("topic_id = ?", [$topic['id']], '`order` ASC');
            }
            $sec['direct_units'] = LearningUnit::where("section_id = ? AND topic_id IS NULL", [$sec['id']], '`order` ASC');
        }

        View::render('course.show', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'sections' => $sections,
        ]);
    }

    public function editForm(int $id): void
    {
        \Middleware\Middleware::teacher();
        $course = Course::findOrFail($id);
        $subjects = Subject::all('full_name ASC');
        $courseTypes = CourseType::all('`order` ASC');

        View::render('course.form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'subjects' => $subjects,
            'course_types' => $courseTypes,
            'course' => $course,
        ]);
    }

    public function edit(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/edit/");
            return;
        }

        $subjectId = $_POST['subject'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $identifier = trim($_POST['identifier'] ?? '');
        $courseTypeId = $_POST['course_type'] ?? null;

        if (!$subjectId || !$fullName || !$shortName) {
            View::render('course.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'subjects' => Subject::all('full_name ASC'),
                'course_types' => CourseType::all('`order` ASC'),
                'course' => Course::findOrFail($id),
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        Course::updateWhere('id = ?', [
            'subject_id' => (int)$subjectId,
            'full_name' => $fullName,
            'short_name' => $shortName,
            'identifier' => $identifier ?: null,
            'course_type_id' => $courseTypeId ? (int)$courseTypeId : null,
        ], [$id]);

        Router::redirect("/courses/{$id}/");
    }

    public function delete(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/courses/');
            return;
        }

        Course::updateWhere('id = ?', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], [$id]);

        Router::redirect('/courses/');
    }

    public function restore(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/courses/');
            return;
        }

        Course::updateWhere('id = ?', [
            'is_deleted' => 0,
            'deleted_at' => null,
        ], [$id]);

        Router::redirect('/courses/');
    }

    public function clone(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/courses/');
            return;
        }

        $course = Course::findOrFail($id);
        $newId = Course::create([
            'subject_id' => $course['subject_id'],
            'full_name' => $course['full_name'] . ' (копия)',
            'short_name' => $course['short_name'] . ' (копия)',
            'identifier' => null,
            'course_type_id' => $course['course_type_id'],
            'is_deleted' => 0,
        ]);

        $sections = CourseSection::where("course_id = ?", [$id], '`order` ASC');
        foreach ($sections as $sec) {
            $newSecId = CourseSection::create([
                'course_id' => $newId,
                'name' => $sec['name'],
                '`order`' => $sec['order'],
                'visible' => $sec['visible'],
            ]);

            $topics = CourseTopic::where("section_id = ?", [$sec['id']], '`order` ASC');
            foreach ($topics as $topic) {
                $newTopicId = CourseTopic::create([
                    'section_id' => $newSecId,
                    'entity_title' => $topic['entity_title'],
                    'content' => $topic['content'],
                    '`order`' => $topic['order'],
                    'visible' => $topic['visible'],
                ]);

                $units = LearningUnit::where("topic_id = ?", [$topic['id']], '`order` ASC');
                foreach ($units as $unit) {
                    LearningUnit::create([
                        'topic_id' => $newTopicId,
                        'section_id' => $newSecId,
                        'title' => $unit['title'],
                        'content_type' => $unit['content_type'],
                        'file' => $unit['file'],
                        '`link`' => $unit['link'],
                        '`order`' => $unit['order'],
                        'visible' => $unit['visible'],
                        'grading_type' => $unit['grading_type'],
                        'test_id' => $unit['test_id'],
                        'max_score' => $unit['max_score'],
                        'created_by_id' => $unit['created_by_id'],
                    ]);
                }
            }

            $directUnits = LearningUnit::where("section_id = ? AND topic_id IS NULL", [$sec['id']], '`order` ASC');
            foreach ($directUnits as $unit) {
                LearningUnit::create([
                    'section_id' => $newSecId,
                    'title' => $unit['title'],
                    'content_type' => $unit['content_type'],
                    'file' => $unit['file'],
                    '`link`' => $unit['link'],
                    '`order`' => $unit['order'],
                    'visible' => $unit['visible'],
                    'grading_type' => $unit['grading_type'],
                    'test_id' => $unit['test_id'],
                    'max_score' => $unit['max_score'],
                    'created_by_id' => $unit['created_by_id'],
                ]);
            }
        }

        Router::redirect('/courses/');
    }

    private function createDefaultSections(int $courseTypeId, int $courseId): void
    {
        $sections = CourseTypeSection::where("course_type_id = ?", [$courseTypeId], '`order` ASC');
        foreach ($sections as $sec) {
            CourseSection::create([
                'course_id' => $courseId,
                'name' => $sec['name'],
                '`order`' => $sec['order'],
            ]);
        }
    }

    // Sections
    public function sectionCreateForm(int $courseId): void
    {
        \Middleware\Middleware::teacher();
        $course = Course::findOrFail($courseId);
        View::render('course.section_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'section' => null,
        ]);
    }

    public function sectionCreate(int $courseId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/sections/create/");
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;

        if (!$name) {
            View::render('course.section_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'course' => Course::findOrFail($courseId),
                'section' => null,
                'error' => 'Введите название раздела.',
            ]);
            return;
        }

        CourseSection::create([
            'course_id' => $courseId,
            'name' => $name,
            '`order`' => $order,
            'visible' => $visible,
        ]);

        Router::redirect("/courses/{$courseId}/");
    }

    public function sectionEditForm(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();
        $section = CourseSection::findOrFail($id);
        $course = Course::findOrFail($courseId);
        View::render('course.section_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'section' => $section,
        ]);
    }

    public function sectionEdit(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/sections/{$id}/edit/");
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;

        if (!$name) {
            View::render('course.section_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'course' => Course::findOrFail($courseId),
                'section' => CourseSection::findOrFail($id),
                'error' => 'Введите название раздела.',
            ]);
            return;
        }

        CourseSection::updateWhere('id = ?', [
            'name' => $name,
            '`order`' => $order,
            'visible' => $visible,
        ], [$id]);

        Router::redirect("/courses/{$courseId}/");
    }

    public function sectionDelete(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/");
            return;
        }

        CourseSection::deleteWhere('id = ?', [$id]);
        Router::redirect("/courses/{$courseId}/");
    }

    // Topics
    public function topicCreateForm(int $courseId): void
    {
        \Middleware\Middleware::teacher();
        $course = Course::findOrFail($courseId);
        $sections = CourseSection::where("course_id = ?", [$courseId], '`order` ASC');
        View::render('course.topic_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'sections' => $sections,
            'topic' => null,
        ]);
    }

    public function topicCreate(int $courseId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/topics/create/");
            return;
        }

        $sectionId = $_POST['section'] ?? '';
        $entityTitle = trim($_POST['entity_title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;

        if (!$sectionId || !$entityTitle) {
            View::render('course.topic_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'course' => Course::findOrFail($courseId),
                'sections' => CourseSection::where("course_id = ?", [$courseId], '`order` ASC'),
                'topic' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        CourseTopic::create([
            'section_id' => (int)$sectionId,
            'entity_title' => $entityTitle,
            'content' => $content,
            '`order`' => $order,
            'visible' => $visible,
        ]);

        Router::redirect("/courses/{$courseId}/");
    }

    public function topicEditForm(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();
        $topic = CourseTopic::findOrFail($id);
        $course = Course::findOrFail($courseId);
        $sections = CourseSection::where("course_id = ?", [$courseId], '`order` ASC');
        View::render('course.topic_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'sections' => $sections,
            'topic' => $topic,
        ]);
    }

    public function topicEdit(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/topics/{$id}/edit/");
            return;
        }

        $sectionId = $_POST['section'] ?? '';
        $entityTitle = trim($_POST['entity_title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;

        if (!$sectionId || !$entityTitle) {
            View::render('course.topic_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'course' => Course::findOrFail($courseId),
                'sections' => CourseSection::where("course_id = ?", [$courseId], '`order` ASC'),
                'topic' => CourseTopic::findOrFail($id),
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        CourseTopic::updateWhere('id = ?', [
            'section_id' => (int)$sectionId,
            'entity_title' => $entityTitle,
            'content' => $content,
            '`order`' => $order,
            'visible' => $visible,
        ], [$id]);

        Router::redirect("/courses/{$courseId}/");
    }

    public function topicDelete(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/");
            return;
        }

        CourseTopic::deleteWhere('id = ?', [$id]);
        Router::redirect("/courses/{$courseId}/");
    }

    // Units
    public function unitCreateForm(int $courseId): void
    {
        \Middleware\Middleware::teacher();
        $course = Course::findOrFail($courseId);
        $sections = CourseSection::where("course_id = ?", [$courseId], '`order` ASC');
        $tests = \Models\Test::all('name ASC');
        $courses = Course::all('short_name ASC');

        View::render('course.unit_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'courses' => $courses,
            'sections' => $sections,
            'tests' => $tests,
            'unit' => null,
        ]);
    }

    public function unitCreate(int $courseId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/courses/');
            return;
        }

        $courseId = $_POST['course'] ?? $courseId;
        $topicId = $_POST['topic'] ?? null;
        $sectionId = $_POST['section'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $contentType = $_POST['content_type'] ?? 'methodical';
        $link = trim($_POST['link'] ?? '');
        if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
            $link = 'https://' . $link;
        }
        if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
            \Core\Flash::error('Некорректный URL.');
            Router::redirect('/courses/');
            return;
        }
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;
        $gradingType = $_POST['grading_type'] ?? null;
        $testId = $_POST['test'] ?? null;
        $maxScore = (int)($_POST['max_score'] ?? 10);

        if (!$courseId || !$title) {
            \Core\Flash::error('Заполните обязательные поля.');
            Router::redirect('/courses/');
            return;
        }

        $filePath = null;
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $filePath = $this->handleUpload($_FILES['file'], (int)$courseId);
        }

        LearningUnit::create([
            'topic_id' => $topicId ? (int)$topicId : null,
            'section_id' => $sectionId ? (int)$sectionId : null,
            'title' => $title,
            'content_type' => $contentType,
            'file' => $filePath,
            '`link`' => $link ?: null,
            '`order`' => $order,
            'visible' => $visible,
            'grading_type' => $gradingType,
            'test_id' => $testId ? (int)$testId : null,
            'max_score' => $maxScore,
            'created_by_id' => Auth::user()['id'],
        ]);

        \Core\Flash::success('Единица создана.');
        Router::redirect("/courses/{$courseId}/");
    }

    public function unitEditForm(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();
        $unit = LearningUnit::findOrFail($id);
        $course = Course::findOrFail($courseId);
        $sections = CourseSection::where("course_id = ?", [$courseId], '`order` ASC');
        $tests = \Models\Test::all('name ASC');
        $courses = Course::all('short_name ASC');

        View::render('course.unit_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'courses' => $courses,
            'sections' => $sections,
            'tests' => $tests,
            'unit' => $unit,
        ]);
    }

    public function unitEdit(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/units/{$id}/edit/");
            return;
        }

        $topicId = $_POST['topic'] ?? null;
        $sectionId = $_POST['section'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $contentType = $_POST['content_type'] ?? 'methodical';
        $link = trim($_POST['link'] ?? '');
        if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
            $link = 'https://' . $link;
        }
        if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
            \Core\Flash::error('Некорректный URL.');
            Router::redirect("/courses/{$courseId}/units/{$id}/edit/");
            return;
        }
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;
        $gradingType = $_POST['grading_type'] ?? null;
        $testId = $_POST['test'] ?? null;
        $maxScore = (int)($_POST['max_score'] ?? 10);

        $data = [
            'topic_id' => $topicId ? (int)$topicId : null,
            'section_id' => $sectionId ? (int)$sectionId : null,
            'title' => $title,
            'content_type' => $contentType,
            '`link`' => $link ?: null,
            '`order`' => $order,
            'visible' => $visible,
            'grading_type' => $gradingType,
            'test_id' => $testId ? (int)$testId : null,
            'max_score' => $maxScore,
        ];

        $unit = LearningUnit::find($id);
        $uploadCourseId = $courseId;
        if ($unit) {
            if ($unit['section_id']) {
                $sec = CourseSection::find($unit['section_id']);
                if ($sec) $uploadCourseId = $sec['course_id'];
            } elseif ($unit['topic_id']) {
                $topic = CourseTopic::find($unit['topic_id']);
                if ($topic) {
                    $sec = CourseSection::find($topic['section_id']);
                    if ($sec) $uploadCourseId = $sec['course_id'];
                }
            }
        }

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $data['file'] = $this->handleUpload($_FILES['file'], $uploadCourseId);
        }

        LearningUnit::updateWhere('id = ?', $data, [$id]);
        \Core\Flash::success('Единица обновлена.');
        Router::redirect("/courses/{$uploadCourseId}/");
    }

    public function unitDelete(int $courseId, int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/");
            return;
        }

        LearningUnit::deleteWhere('id = ?', [$id]);
        Router::redirect("/courses/{$courseId}/");
    }

    // Permissions
    public function addUserPermission(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $perm = Role::getHighestPermission($user, $role, $id);
        if ($perm !== 'full_access') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        $permission = $_POST['permission'] ?? 'view';

        if ($userId) {
            $existing = CourseUserPermission::findOne("course_id = ? AND user_id = ?", [$id, $userId]);
            if ($existing) {
                CourseUserPermission::updateWhere('id = ?', ['permission' => $permission], [$existing['id']]);
            } else {
                CourseUserPermission::create([
                    'course_id' => $id,
                    'user_id' => (int)$userId,
                    'permission' => $permission,
                ]);
            }
        }

        Router::redirect("/courses/{$id}/");
    }

    public function removeUserPermission(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        if (Role::getHighestPermission($user, $role, $id) !== 'full_access') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $upId = $_POST['up_id'] ?? '';
        if ($upId) {
            CourseUserPermission::deleteWhere('id = ?', [(int)$upId]);
        }

        Router::redirect("/courses/{$id}/");
    }

    public function addGroupPermission(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        if (Role::getHighestPermission($user, $role, $id) !== 'full_access') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $groupId = $_POST['group_id'] ?? '';
        $permission = $_POST['permission'] ?? 'view';

        if ($groupId) {
            $existing = CourseGroupPermission::findOne("course_id = ? AND group_id = ?", [$id, $groupId]);
            if ($existing) {
                CourseGroupPermission::updateWhere('id = ?', ['permission' => $permission], [$existing['id']]);
            } else {
                CourseGroupPermission::create([
                    'course_id' => $id,
                    'group_id' => (int)$groupId,
                    'permission' => $permission,
                ]);
            }
        }

        Router::redirect("/courses/{$id}/");
    }

    public function removeGroupPermission(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        if (Role::getHighestPermission($user, $role, $id) !== 'full_access') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $gpId = $_POST['gp_id'] ?? '';
        if ($gpId) {
            CourseGroupPermission::deleteWhere('id = ?', [(int)$gpId]);
        }

        Router::redirect("/courses/{$id}/");
    }

    // Announcements
    public function announcementCreate(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $perm = Role::getHighestPermission($user, $role, $id);
        $allowed = ['edit', 'create_delete', 'full_access'];
        if (!in_array($perm, $allowed)) {
            Router::redirect("/courses/{$id}/");
            return;
        }

        $text = trim($_POST['text'] ?? '');
        if ($text) {
            CourseAnnouncement::create([
                'course_id' => $id,
                'author_id' => $user['id'],
                'text' => $text,
            ]);
        }

        Router::redirect("/courses/{$id}/");
    }

    public function announcementHide(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/home');
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $perm = Role::getHighestPermission($user, $role, $id);
        $allowed = ['edit', 'create_delete', 'full_access'];
        if (!in_array($perm, $allowed)) {
            Router::redirect('/home');
            return;
        }

        CourseAnnouncement::deleteWhere('id = ?', [$id]);
        Router::redirect('/home');
    }

    public function announcementDismiss(int $id): void
    {
        \Middleware\Middleware::auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/home');
            return;
        }

        $user = Auth::user();
        $announcement = CourseAnnouncement::findOrFail($id);

        if (Auth::guard() === 'student') {
            AnnouncementDismiss::create([
                'announcement_id' => $id,
                'student_id' => $user['id'],
                'user_id' => null,
            ]);
        } else {
            AnnouncementDismiss::create([
                'announcement_id' => $id,
                'student_id' => null,
                'user_id' => $user['id'],
            ]);
        }

        Router::redirect('/home');
    }

    private function handleUpload(array $file, ?int $courseId = null): ?string
    {
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return null;
        }

        $baseDir = STORAGE_PATH . '/data/';
        if ($courseId) {
            $course = Course::find($courseId);
            if ($course) {
                $subject = \Models\Subject::find($course['subject_id']);
                if ($subject) {
                    $department = \Models\Department::find($subject['department_id']);
                    if ($department) {
                        $faculty = \Models\Faculty::find($department['faculty_id']);
                        $facultyDir = $faculty ? ($faculty['identifier'] ?: $this->safeDirname($faculty['full_name'])) : 'unknown_faculty';
                        $deptDir = $department['identifier'] ?: $this->safeDirname($department['full_name']);
                        $courseDir = $course['identifier'] ?: $this->safeDirname($course['short_name']);
                        $baseDir .= "{$facultyDir}/{$deptDir}/{$courseDir}/";
                    }
                }
            }
        }

        if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);

        $filename = uniqid() . '.' . $ext;
        $dest = $baseDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $relative = str_replace(STORAGE_PATH . '/', '', $dest);
            return $relative;
        }
        return null;
    }

    private function safeDirname(string $name): string
    {
        $translit = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo',
            'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        ];
        $result = '';
        for ($i = 0; $i < mb_strlen($name); $i++) {
            $ch = mb_substr($name, $i, 1);
            $result .= $translit[$ch] ?? $ch;
        }
        $result = preg_replace('/[^a-zA-Z0-9\-]/', '-', $result);
        $result = preg_replace('/-+/', '-', $result);
        $result = trim($result, '-');
        return mb_strtolower($result) ?: 'unnamed';
    }

    public function download(int $courseId, int $id): void
    {
        \Middleware\Middleware::auth();

        $unit = LearningUnit::findOrFail($id);
        if (!$unit['file']) {
            Router::redirect("/courses/{$courseId}/");
            return;
        }

        $filePath = STORAGE_PATH . '/' . $unit['file'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "Файл не найден";
            return;
        }

        $filename = basename($filePath);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function announcementEdit(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/home');
            return;
        }

        $announcement = CourseAnnouncement::findOrFail($id);
        $user = Auth::user();
        $role = Role::getActiveRole($user);

        if ($announcement['author_id'] != $user['id'] && Role::getHighestPermission($user, $role, $announcement['course_id']) !== 'full_access') {
            Router::redirect('/home');
            return;
        }

        $text = trim($_POST['text'] ?? '');
        if ($text) {
            CourseAnnouncement::updateWhere('id = ?', ['text' => $text], [$id]);
            \Core\Flash::success('Объявление обновлено.');
        }

        Router::redirect('/home');
    }

    public function announcementDelete(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/home');
            return;
        }

        $announcement = CourseAnnouncement::findOrFail($id);
        $user = Auth::user();
        $role = Role::getActiveRole($user);

        if ($announcement['author_id'] != $user['id'] && Role::getHighestPermission($user, $role, $announcement['course_id']) !== 'full_access') {
            Router::redirect('/home');
            return;
        }

        CourseAnnouncement::deleteWhere('id = ?', [$id]);
        \Core\Flash::success('Объявление удалено.');
        Router::redirect('/home');
    }
}
