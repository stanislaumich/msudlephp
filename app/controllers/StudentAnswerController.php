<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\LearningUnit;
use Models\StudentAnswer;
use Models\Course;

class StudentAnswerController
{
    public function submitForm(int $courseId, int $unitId): void
    {
        \Middleware\Middleware::auth();

        $user = Auth::user();
        if (Auth::guard() !== 'student') {
            Router::redirect('/dashboard');
            return;
        }

        $unit = LearningUnit::findOrFail($unitId);
        $course = Course::findOrFail($courseId);

        $answer = StudentAnswer::findOne("student_id = ? AND learning_unit_id = ?", [$user['id'], $unitId]);

        View::render('course.submit_answer', [
            'user' => $user,
            'role' => Role::getActiveRole($user),
            'course' => $course,
            'unit' => $unit,
            'answer' => $answer,
        ]);
    }

    public function submit(int $courseId, int $unitId): void
    {
        \Middleware\Middleware::auth();

        $user = Auth::user();
        if (Auth::guard() !== 'student') {
            Router::redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/units/{$unitId}/submit/");
            return;
        }

        $answerText = trim($_POST['answer_text'] ?? '');

        $existing = StudentAnswer::findOne("student_id = ? AND learning_unit_id = ?", [$user['id'], $unitId]);

        $filePath = null;
        if (isset($_FILES['answer_file']) && $_FILES['answer_file']['error'] === UPLOAD_ERR_OK) {
            $filePath = $this->handleUpload($_FILES['answer_file']);
        }

        if ($existing) {
            StudentAnswer::updateWhere('id = ?', [
                'answer_text' => $answerText ?: null,
                'answer_file' => $filePath ?: $existing['answer_file'],
                'modified_at' => date('Y-m-d H:i:s'),
            ], [$existing['id']]);
            \Core\Flash::success('Ответ обновлён.');
        } else {
            StudentAnswer::create([
                'student_id' => $user['id'],
                'learning_unit_id' => $unitId,
                'answer_text' => $answerText ?: null,
                'answer_file' => $filePath,
            ]);
            \Core\Flash::success('Ответ отправлен.');
        }

        Router::redirect("/courses/{$courseId}/");
    }

    public function list(int $courseId): void
    {
        \Middleware\Middleware::teacher();

        $course = Course::findOrFail($courseId);
        $units = LearningUnit::where("content_type = 'control' AND (topic_id IN (SELECT id FROM course_coursetopic WHERE section_id IN (SELECT id FROM course_coursesection WHERE course_id = ?)) OR section_id IN (SELECT id FROM course_coursesection WHERE course_id = ?))", [$courseId, $courseId], '`order` ASC');

        $unitIds = array_column($units, 'id');
        $answers = [];
        if ($unitIds) {
            $placeholders = implode(',', array_fill(0, count($unitIds), '?'));
            $answers = \Core\Database::fetchAll(
                "SELECT sa.*, s.fio as student_fio, s.login as student_login FROM course_studentanswer sa
                 JOIN students_student s ON sa.student_id = s.id
                 WHERE sa.learning_unit_id IN ({$placeholders})
                 ORDER BY sa.created_at DESC",
                $unitIds
            );
        }

        $answerMap = [];
        foreach ($answers as $a) $answerMap[$a['learning_unit_id']][] = $a;

        View::render('course.student_answers', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => $course,
            'units' => $units,
            'answer_map' => $answerMap,
        ]);
    }

    public function checkForm(int $courseId, int $answerId): void
    {
        \Middleware\Middleware::teacher();

        $answer = StudentAnswer::findOrFail($answerId);
        $unit = LearningUnit::findOrFail($answer['learning_unit_id']);

        View::render('course.check_answer', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'course' => Course::findOrFail($courseId),
            'unit' => $unit,
            'answer' => $answer,
        ]);
    }

    public function check(int $courseId, int $answerId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/courses/{$courseId}/answers/");
            return;
        }

        $answer = StudentAnswer::findOrFail($answerId);
        $score = $_POST['score'] ?? null;
        $passed = $_POST['passed'] ?? null;
        $comment = trim($_POST['comment'] ?? '');

        if ($score !== null && $score !== '') {
            $score = (int)$score;
        } else {
            $score = null;
        }

        if ($passed !== null) {
            $passed = $passed === '1' || $passed === 'on' ? 1 : 0;
        } else {
            $passed = null;
        }

        StudentAnswer::updateWhere('id = ?', [
            'checked' => 1,
            'score' => $score,
            'passed' => $passed,
            'comment' => $comment ?: null,
            'checked_at' => date('Y-m-d H:i:s'),
            'checked_modified_at' => date('Y-m-d H:i:s'),
        ], [$answerId]);

        \Core\Flash::success('Ответ проверён.');
        Router::redirect("/courses/{$courseId}/answers/");
    }

    private function handleUpload(array $file): ?string
    {
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return null;
        }

        $uploadDir = STORAGE_PATH . '/data/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = uniqid() . '.' . $ext;
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'data/' . $filename;
        }
        return null;
    }
}
