<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Core\Flash;
use Models\Test;
use Models\Question;
use Models\Choice;
use Models\DeletedTest;
use Models\TestResult;
use Models\Subject;
use Models\User;

class TestingController
{
    public function index(): void
    {
        \Middleware\Middleware::teacher();
        $tests = Test::all('-created_at');
        foreach ($tests as &$t) {
            $t['author'] = User::find($t['author_id']);
            $t['subject'] = Subject::find($t['subject_id']);
            $t['question_count'] = Question::count("test_id = ?", [$t['id']]);
        }
        View::render('testing.index', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'tests' => $tests,
        ]);
    }

    public function createForm(): void
    {
        \Middleware\Middleware::teacher();
        $subjects = Subject::all('full_name ASC');
        View::render('testing.form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'subjects' => $subjects,
            'test' => null,
        ]);
    }

    public function create(): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/testing/create/');
            return;
        }

        $subjectId = $_POST['subject'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$subjectId || !$name) {
            View::render('testing.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'subjects' => Subject::all('full_name ASC'),
                'test' => null,
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        Test::create([
            'author_id' => Auth::user()['id'],
            'subject_id' => (int)$subjectId,
            'name' => $name,
            'description' => $description ?: null,
        ]);

        Router::redirect('/testing/');
    }

    public function show(int $id): void
    {
        \Middleware\Middleware::teacher();
        $test = Test::findOrFail($id);
        $questions = Question::where("test_id = ?", [$id], '`order` ASC, id ASC');
        foreach ($questions as &$q) {
            $q['choices'] = Choice::where("question_id = ?", [$q['id']], 'id ASC');
        }
        View::render('testing.show', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'test' => $test,
            'questions' => $questions,
        ]);
    }

    public function editForm(int $id): void
    {
        \Middleware\Middleware::teacher();
        $test = Test::findOrFail($id);
        $questions = Question::where("test_id = ?", [$id], '`order` ASC, id ASC');
        foreach ($questions as &$q) {
            $q['choices'] = Choice::where("question_id = ?", [$q['id']], 'id ASC');
        }
        View::render('testing.form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'subjects' => Subject::all('full_name ASC'),
            'test' => $test,
            'questions' => $questions,
        ]);
    }

    public function edit(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/testing/{$id}/edit/");
            return;
        }

        $subjectId = $_POST['subject'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$subjectId || !$name) {
            View::render('testing.form', [
                'user' => Auth::user(),
                'role' => Role::getActiveRole(Auth::user()),
                'subjects' => Subject::all('full_name ASC'),
                'test' => Test::findOrFail($id),
                'error' => 'Заполните обязательные поля.',
            ]);
            return;
        }

        Test::updateWhere('id = ?', [
            'subject_id' => (int)$subjectId,
            'name' => $name,
            'description' => $description ?: null,
        ], [$id]);

        Router::redirect('/testing/');
    }

    public function delete(int $id): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/testing/');
            return;
        }

        $test = Test::findOrFail($id);
        $questions = Question::where("test_id = ?", [$id], '`order` ASC');
        foreach ($questions as &$q) {
            $q['choices'] = Choice::where("question_id = ?", [$q['id']], 'id ASC');
        }

        $subject = Subject::find($test['subject_id']);
        $author = User::find($test['author_id']);

        DeletedTest::create([
            'original_id' => $test['id'],
            'author_id' => $test['author_id'],
            'author_name' => $author ? trim(($author['last_name'] ?? '') . ' ' . ($author['first_name'] ?? '')) : '',
            'subject_id' => $test['subject_id'],
            'subject_name' => $subject ? $subject['full_name'] : '',
            'name' => $test['name'],
            'description' => $test['description'],
            'export_data' => json_encode([
                'test' => $test,
                'questions' => $questions,
            ]),
            'created_at' => $test['created_at'],
        ]);

        Test::deleteWhere('id = ?', [$id]);
        Router::redirect('/testing/');
    }

    public function archive(): void
    {
        \Middleware\Middleware::settingsAccess();
        $deleted = DeletedTest::all('-deleted_at');
        View::render('testing.archive', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'deleted_tests' => $deleted,
        ]);
    }

    public function restore(int $id): void
    {
        \Middleware\Middleware::settingsAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/testing/archive/');
            return;
        }

        $deleted = DeletedTest::findOrFail($id);
        $data = json_decode($deleted['export_data'], true);

        if ($data && is_array($data)) {
            if (isset($data['test'])) {
                $testData = $data['test'];
                unset($testData['id'], $testData['created_at'], $testData['updated_at']);
                $newTestId = Test::create($testData);

            if (isset($data['questions'])) {
                foreach ($data['questions'] as $q) {
                    $choices = $q['choices'] ?? [];
                    unset($q['id'], $q['choices']);
                    $q['test_id'] = $newTestId;
                    $newQuestionId = Question::create($q);

                    foreach ($choices as $c) {
                        unset($c['id']);
                        $c['question_id'] = $newQuestionId;
                        Choice::create($c);
                    }
                }
            }
            } else {
                $testData = $data;
                unset($testData['id'], $testData['created_at'], $testData['updated_at']);
                Test::create($testData);
            }
        }

        DeletedTest::deleteWhere('id = ?', [$id]);
        Flash::success('Тест восстановлен.');
        Router::redirect('/testing/');
    }

    public function questionCreate(int $testId): void
    {
        \Middleware\Middleware::teacher();
        $test = Test::findOrFail($testId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text = trim($_POST['text'] ?? '');
            $questionType = trim($_POST['question_type'] ?? 'single');
            $order = (int)($_POST['order'] ?? 0);
            $score = (int)($_POST['score'] ?? 1);

            if (!$text) {
                View::render('testing.question_form', [
                    'user' => Auth::user(),
                    'role' => Role::getActiveRole(Auth::user()),
                    'test' => $test,
                    'question' => null,
                    'error' => 'Введите текст вопроса.',
                ]);
                return;
            }

            Question::create([
                'test_id' => $testId,
                'text' => $text,
                'question_type' => $questionType,
                'order' => $order,
                'score' => $score,
            ]);

            Flash::success('Вопрос создан.');
            Router::redirect("/testing/{$testId}/");
            return;
        }

        View::render('testing.question_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'test' => $test,
            'question' => null,
        ]);
    }

    public function questionEdit(int $testId, int $questionId): void
    {
        \Middleware\Middleware::teacher();
        $test = Test::findOrFail($testId);
        $question = Question::findOrFail($questionId);

        if ((int)$question['test_id'] !== $testId) {
            Router::redirect("/testing/{$testId}/");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text = trim($_POST['text'] ?? '');
            $questionType = trim($_POST['question_type'] ?? 'single');
            $order = (int)($_POST['order'] ?? 0);
            $score = (int)($_POST['score'] ?? 1);

            if (!$text) {
                View::render('testing.question_form', [
                    'user' => Auth::user(),
                    'role' => Role::getActiveRole(Auth::user()),
                    'test' => $test,
                    'question' => $question,
                    'error' => 'Введите текст вопроса.',
                ]);
                return;
            }

            Question::updateWhere('id = ?', [
                'text' => $text,
                'question_type' => $questionType,
                'order' => $order,
                'score' => $score,
            ], [$questionId]);

            Flash::success('Вопрос изменён.');
            Router::redirect("/testing/{$testId}/");
            return;
        }

        View::render('testing.question_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'test' => $test,
            'question' => $question,
        ]);
    }

    public function questionDelete(int $testId, int $questionId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/testing/{$testId}/");
            return;
        }

        $question = Question::findOrFail($questionId);
        if ((int)$question['test_id'] !== $testId) {
            Router::redirect("/testing/{$testId}/");
            return;
        }

        Question::deleteWhere('id = ?', [$questionId]);
        Flash::success('Вопрос удалён.');
        Router::redirect("/testing/{$testId}/");
    }

    public function choiceCreate(int $testId, int $questionId): void
    {
        \Middleware\Middleware::teacher();
        $test = Test::findOrFail($testId);
        $question = Question::findOrFail($questionId);

        if ((int)$question['test_id'] !== $testId) {
            Router::redirect("/testing/{$testId}/");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text = trim($_POST['text'] ?? '');
            $isCorrect = isset($_POST['is_correct']) ? 1 : 0;

            if (!$text) {
                View::render('testing.choice_form', [
                    'user' => Auth::user(),
                    'role' => Role::getActiveRole(Auth::user()),
                    'test' => $test,
                    'question' => $question,
                    'choice' => null,
                    'error' => 'Введите текст варианта ответа.',
                ]);
                return;
            }

            Choice::create([
                'question_id' => $questionId,
                'text' => $text,
                'is_correct' => $isCorrect,
            ]);

            Flash::success('Вариант ответа создан.');
            Router::redirect("/testing/{$testId}/");
            return;
        }

        View::render('testing.choice_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'test' => $test,
            'question' => $question,
            'choice' => null,
        ]);
    }

    public function choiceEdit(int $testId, int $questionId, int $choiceId): void
    {
        \Middleware\Middleware::teacher();
        $test = Test::findOrFail($testId);
        $question = Question::findOrFail($questionId);
        $choice = Choice::findOrFail($choiceId);

        if ((int)$question['test_id'] !== $testId || (int)$choice['question_id'] !== $questionId) {
            Router::redirect("/testing/{$testId}/");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text = trim($_POST['text'] ?? '');
            $isCorrect = isset($_POST['is_correct']) ? 1 : 0;

            if (!$text) {
                View::render('testing.choice_form', [
                    'user' => Auth::user(),
                    'role' => Role::getActiveRole(Auth::user()),
                    'test' => $test,
                    'question' => $question,
                    'choice' => $choice,
                    'error' => 'Введите текст варианта ответа.',
                ]);
                return;
            }

            Choice::updateWhere('id = ?', [
                'text' => $text,
                'is_correct' => $isCorrect,
            ], [$choiceId]);

            Flash::success('Вариант ответа изменён.');
            Router::redirect("/testing/{$testId}/");
            return;
        }

        View::render('testing.choice_form', [
            'user' => Auth::user(),
            'role' => Role::getActiveRole(Auth::user()),
            'test' => $test,
            'question' => $question,
            'choice' => $choice,
        ]);
    }

    public function choiceDelete(int $testId, int $questionId, int $choiceId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/testing/{$testId}/");
            return;
        }

        $question = Question::findOrFail($questionId);
        $choice = Choice::findOrFail($choiceId);

        if ((int)$question['test_id'] !== $testId || (int)$choice['question_id'] !== $questionId) {
            Router::redirect("/testing/{$testId}/");
            return;
        }

        Choice::deleteWhere('id = ?', [$choiceId]);
        Flash::success('Вариант ответа удалён.');
        Router::redirect("/testing/{$testId}/");
    }

    public function take(int $id): void
    {
        \Middleware\Middleware::auth();

        $user = Auth::user();
        if (Auth::guard() !== 'student') {
            Router::redirect('/dashboard');
            return;
        }

        $test = Test::findOrFail($id);
        $questions = Question::where("test_id = ?", [$id], '`order` ASC, id ASC');
        foreach ($questions as &$q) {
            $q['choices'] = Choice::where("question_id = ?", [$q['id']], 'id ASC');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $answers = $_POST['answers'] ?? [];
            $totalScore = 0;
            $maxScore = 0;

            foreach ($questions as $q) {
                $maxScore += $q['score'];
                $selected = $answers[$q['id']] ?? [];
                if (!is_array($selected)) $selected = [(int)$selected];
                $correctChoices = array_column(Choice::where("question_id = ? AND is_correct = 1", [$q['id']]), 'id');
                $selectedCorrect = array_intersect($selected, $correctChoices);
                $wrongSelected = array_diff($selected, $correctChoices);

                if (empty($wrongSelected) && count($selectedCorrect) === count($correctChoices)) {
                    $totalScore += $q['score'];
                }
            }

            TestResult::create([
                'test_id' => $id,
                'student_id' => $user['id'],
                'score' => $totalScore,
                'max_score' => $maxScore,
                'answers_data' => json_encode([
                    'answers' => $answers,
                    'total_score' => $totalScore,
                    'max_score' => $maxScore,
                ]),
            ]);

            View::render('testing.result', [
                'user' => $user,
                'role' => Role::getActiveRole($user),
                'test' => $test,
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'questions' => $questions,
                'answers' => $answers,
            ]);
            return;
        }

        View::render('testing.take', [
            'user' => $user,
            'role' => Role::getActiveRole($user),
            'test' => $test,
            'questions' => $questions,
        ]);
    }
}
