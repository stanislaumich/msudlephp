<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\LearningUnit;
use Models\Step;
use Models\StepQuestion;
use Models\StepChoice;
use Models\StepProgress;

class StepController
{
    public function list(): void
    {
        \Middleware\Middleware::teacher();

        $user = Auth::user();
        $role = Role::getActiveRole($user);

        $units = LearningUnit::where("content_type = 'step_by_step' AND is_deleted = 0", [], '`order` ASC, id DESC');

        foreach ($units as &$u) {
            $u['steps'] = Step::where("learning_unit_id = ?", [$u['id']], '`order` ASC');
        }

        View::render('course.step_list', [
            'user' => $user,
            'role' => $role,
            'units' => $units,
        ]);
    }

    public function createForm(): void
    {
        \Middleware\Middleware::teacher();
        $courses = \Models\Course::all('short_name ASC');
        View::render('course.step_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'unit' => null,
            'courses' => $courses,
        ]);
    }

    public function create(): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/steps/');
            return;
        }

        $courseId = $_POST['course_id'] ?? null;
        $sectionId = $_POST['section_id'] ?? null;
        $topicId = $_POST['topic_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;

        if (!$title) {
            \Core\Flash::error('Введите название единицы.');
            Router::redirect('/steps/');
            return;
        }

        $unitId = LearningUnit::create([
            'topic_id' => $topicId ? (int)$topicId : null,
            'section_id' => $sectionId ? (int)$sectionId : null,
            'title' => $title,
            'content_type' => 'step_by_step',
            '`order`' => $order,
            'visible' => $visible,
            'created_by_id' => $user['id'],
        ]);

        $stepOrder = 0;
        if (!empty($_POST['steps'])) {
            foreach ($_POST['steps'] as $stepData) {
                $stepTitle = trim($stepData['title'] ?? '');
                $stepContent = trim($stepData['content'] ?? '');
                if (!$stepTitle) continue;

                $stepId = Step::create([
                    'learning_unit_id' => $unitId,
                    'title' => $stepTitle,
                    'content' => $stepContent ?: null,
                    '`order`' => $stepOrder++,
                ]);

                if (!empty($stepData['questions'])) {
                    $qOrder = 0;
                    foreach ($stepData['questions'] as $qData) {
                        $qText = trim($qData['text'] ?? '');
                        if (!$qText) continue;

                        $questionId = StepQuestion::create([
                            'step_id' => $stepId,
                            'text' => $qText,
                            '`order`' => $qOrder++,
                        ]);

                        foreach ($qData['choices'] ?? [] as $cData) {
                            $cText = trim($cData['text'] ?? '');
                            if (!$cText) continue;
                            StepChoice::create([
                                'question_id' => $questionId,
                                'text' => $cText,
                                'is_correct' => isset($cData['is_correct']) ? 1 : 0,
                            ]);
                        }
                    }
                }
            }
        }

        \Core\Flash::success('Пошаговая единица создана.');
        Router::redirect('/steps/');
    }

    public function edit(int $id): void
    {
        \Middleware\Middleware::teacher();

        $unit = LearningUnit::findOrFail($id);
        if ($unit['content_type'] !== 'step_by_step') {
            Router::redirect('/steps/');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $unit['steps'] = Step::where("learning_unit_id = ?", [$id], '`order` ASC');
            foreach ($unit['steps'] as &$step) {
                $step['questions'] = StepQuestion::where("step_id = ?", [$step['id']], '`order` ASC');
                foreach ($step['questions'] as &$q) {
                    $q['choices'] = StepChoice::where("question_id = ?", [$q['id']], 'id ASC');
                }
            }
            View::render('course.step_form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'unit' => $unit,
            ]);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $order = (int)($_POST['order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;

        if (!$title) {
            \Core\Flash::error('Введите название единицы.');
            Router::redirect("/steps/{$id}/edit/");
            return;
        }

        LearningUnit::updateWhere('id = ?', [
            'title' => $title,
            '`order`' => $order,
            'visible' => $visible,
        ], [$id]);

        Step::deleteWhere('learning_unit_id = ?', [$id]);

        $stepOrder = 0;
        if (!empty($_POST['steps'])) {
            foreach ($_POST['steps'] as $stepData) {
                $stepTitle = trim($stepData['title'] ?? '');
                $stepContent = trim($stepData['content'] ?? '');
                if (!$stepTitle) continue;

                $stepId = Step::create([
                    'learning_unit_id' => $id,
                    'title' => $stepTitle,
                    'content' => $stepContent ?: null,
                    '`order`' => $stepOrder++,
                ]);

                if (!empty($stepData['questions'])) {
                    $qOrder = 0;
                    foreach ($stepData['questions'] as $qData) {
                        $qText = trim($qData['text'] ?? '');
                        if (!$qText) continue;

                        $questionId = StepQuestion::create([
                            'step_id' => $stepId,
                            'text' => $qText,
                            '`order`' => $qOrder++,
                        ]);

                        foreach ($qData['choices'] ?? [] as $cData) {
                            $cText = trim($cData['text'] ?? '');
                            if (!$cText) continue;
                            StepChoice::create([
                                'question_id' => $questionId,
                                'text' => $cText,
                                'is_correct' => isset($cData['is_correct']) ? 1 : 0,
                            ]);
                        }
                    }
                }
            }
        }

        \Core\Flash::success('Пошаговая единица обновлена.');
        Router::redirect('/steps/');
    }

    public function delete(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/steps/');
            return;
        }

        LearningUnit::updateWhere('id = ?', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], [$id]);

        \Core\Flash::success('Единица перемещена в корзину.');
        Router::redirect('/steps/');
    }

    public function show(int $id): void
    {
        \Middleware\Middleware::auth();

        $unit = LearningUnit::findOrFail($id);
        if ($unit['content_type'] !== 'step_by_step') {
            Router::redirect('/dashboard');
            return;
        }

        $steps = Step::where("learning_unit_id = ?", [$id], '`order` ASC');
        foreach ($steps as &$step) {
            $step['questions'] = StepQuestion::where("step_id = ?", [$step['id']], '`order` ASC');
            foreach ($step['questions'] as &$q) {
                $q['choices'] = StepChoice::where("question_id = ?", [$q['id']], 'id ASC');
            }
        }

        $user = Auth::user();
        $progress = [];
        if (Auth::guard() === 'student') {
            $progressRows = StepProgress::where("student_id = ? AND step_id IN (" . implode(',', array_fill(0, count($steps), '?')) . ")", array_merge([$user['id']], array_column($steps, 'id')), 'step_id ASC');
            foreach ($progressRows as $p) $progress[$p['step_id']] = $p;
        }

        View::render('course.step_show', [
            'user' => $user,
            'role' => Role::getActiveRole($user),
            'unit' => $unit,
            'steps' => $steps,
            'progress' => $progress,
        ]);
    }

    public function take(int $id): void
    {
        \Middleware\Middleware::auth();

        $user = Auth::user();
        if (Auth::guard() !== 'student') {
            Router::redirect('/steps/' . $id . '/');
            return;
        }

        $unit = LearningUnit::findOrFail($id);
        if ($unit['content_type'] !== 'step_by_step') {
            Router::redirect('/dashboard');
            return;
        }

        $stepId = $_POST['step_id'] ?? null;
        $answerData = $_POST['answers'] ?? [];

        if ($stepId) {
            $step = Step::findOrFail((int)$stepId);
            $questions = StepQuestion::where("step_id = ?", [$step['id']], '`order` ASC');
            $correctCount = 0;
            $totalCount = count($questions);

            foreach ($questions as $q) {
                $selected = $answerData[$q['id']] ?? [];
                $correctChoices = array_column(StepChoice::where("question_id = ? AND is_correct = 1", [$q['id']]), 'id');
                $selectedCorrect = array_intersect($selected, $correctChoices);
                $wrongSelected = array_diff($selected, $correctChoices);

                if (empty($wrongSelected) && count($selectedCorrect) === count($correctChoices)) {
                    $correctCount++;
                }
            }

            $passed = $totalCount > 0 && $correctCount === $totalCount;

            $progress = StepProgress::findOne("student_id = ? AND step_id = ?", [$user['id'], $step['id']]);
            if ($progress) {
                StepProgress::updateWhere('id = ?', [
                    'completed' => $passed ? 1 : 0,
                    'completed_at' => $passed ? date('Y-m-d H:i:s') : null,
                    'answers' => json_encode($answerData),
                ], [$progress['id']]);
            } else {
                StepProgress::create([
                    'student_id' => $user['id'],
                    'step_id' => $step['id'],
                    'completed' => $passed ? 1 : 0,
                    'completed_at' => $passed ? date('Y-m-d H:i:s') : null,
                    'answers' => json_encode($answerData),
                ]);
            }

            \Core\Flash::success($passed ? 'Шаг пройден!' : 'Ответы проверены. Попробуйте ещё раз.');
            Router::redirect("/steps/{$id}/take/");
            return;
        }

        $steps = Step::where("learning_unit_id = ?", [$id], '`order` ASC');
        foreach ($steps as &$step) {
            $step['questions'] = StepQuestion::where("step_id = ?", [$step['id']], '`order` ASC');
            foreach ($step['questions'] as &$q) {
                $q['choices'] = StepChoice::where("question_id = ?", [$q['id']], 'id ASC');
            }
            $step['progress'] = StepProgress::findOne("student_id = ? AND step_id = ?", [$user['id'], $step['id']]);
        }

        View::render('course.step_take', [
            'user' => $user,
            'role' => Role::getActiveRole($user),
            'unit' => $unit,
            'steps' => $steps,
        ]);
    }
}
