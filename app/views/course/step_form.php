<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $unit ? 'Редактирование' : 'Создание'; ?> пошаговой единицы</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" id="step-form">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Название единицы *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($unit['title'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок</label>
                        <input type="number" name="order" class="form-control" value="<?php echo $unit['order'] ?? 0; ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="visible" class="form-check-input" id="visible" <?php echo ($unit['visible'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="visible">Видима студентам</label>
                    </div>

                    <hr>
                    <h5>Шаги</h5>
                    <div id="steps-container">
                        <?php if (!empty($unit['steps'] ?? [])): ?>
                            <?php foreach ($unit['steps'] as $stepIndex => $step): ?>
                            <div class="step-block border rounded p-3 mb-3">
                                <div class="mb-2">
                                    <label class="form-label">Название шага *</label>
                                    <input type="text" name="steps[<?php echo $stepIndex; ?>][title]" class="form-control" value="<?php echo htmlspecialchars($step['title']); ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Материал</label>
                                    <textarea name="steps[<?php echo $stepIndex; ?>][content]" class="form-control" rows="3"><?php echo htmlspecialchars($step['content'] ?? ''); ?></textarea>
                                </div>
                                <div class="questions-container">
                                    <h6>Вопросы</h6>
                                    <?php foreach ($step['questions'] ?? [] as $qIndex => $q): ?>
                                    <div class="question-block border rounded p-2 mb-2">
                                        <div class="mb-2">
                                            <label class="form-label">Текст вопроса</label>
                                            <input type="text" name="steps[<?php echo $stepIndex; ?>][questions][<?php echo $qIndex; ?>][text]" class="form-control" value="<?php echo htmlspecialchars($q['text']); ?>" required>
                                        </div>
                                        <div class="choices-container">
                                            <?php foreach ($q['choices'] ?? [] as $cIndex => $c): ?>
                                            <div class="input-group mb-1">
                                                <input type="text" name="steps[<?php echo $stepIndex; ?>][questions][<?php echo $qIndex; ?>][choices][<?php echo $cIndex; ?>][text]" class="form-control" value="<?php echo htmlspecialchars($c['text']); ?>" required>
                                                <div class="input-group-text">
                                                    <input type="checkbox" name="steps[<?php echo $stepIndex; ?>][questions][<?php echo $qIndex; ?>][choices][<?php echo $cIndex; ?>][is_correct]" <?php echo $c['is_correct'] ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-choice" data-step="<?php echo $stepIndex; ?>" data-question="<?php echo $qIndex; ?>">Добавить вариант</button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary add-question" data-step="<?php echo $stepIndex; ?>">Добавить вопрос</button>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary mb-3" id="add-step">Добавить шаг</button>

                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/steps/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let stepCount = <?php echo count($unit['steps'] ?? []); ?>;
document.getElementById('add-step').addEventListener('click', function() {
    const container = document.getElementById('steps-container');
    const div = document.createElement('div');
    div.className = 'step-block border rounded p-3 mb-3';
    div.innerHTML = `
        <div class="mb-2">
            <label class="form-label">Название шага *</label>
            <input type="text" name="steps[${stepCount}][title]" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Материал</label>
            <textarea name="steps[${stepCount}][content]" class="form-control" rows="3"></textarea>
        </div>
        <div class="questions-container">
            <h6>Вопросы</h6>
            <div class="question-block border rounded p-2 mb-2">
                <div class="mb-2">
                    <label class="form-label">Текст вопроса</label>
                    <input type="text" name="steps[${stepCount}][questions][0][text]" class="form-control" required>
                </div>
                <div class="choices-container">
                    <div class="input-group mb-1">
                        <input type="text" name="steps[${stepCount}][questions][0][choices][0][text]" class="form-control" required>
                        <div class="input-group-text">
                            <input type="checkbox" name="steps[${stepCount}][questions][0][choices][0][is_correct]">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-choice" data-step="${stepCount}" data-question="0">Добавить вариант</button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary add-question" data-step="${stepCount}">Добавить вопрос</button>
        </div>
    `;
    container.appendChild(div);
    stepCount++;
});

document.getElementById('steps-container').addEventListener('click', function(e) {
    if (e.target.classList.contains('add-question')) {
        const stepIndex = e.target.dataset.step;
        const stepBlock = e.target.closest('.step-block');
        const questionsContainer = stepBlock.querySelector('.questions-container');
        const questionCount = questionsContainer.querySelectorAll('.question-block').length;

        const div = document.createElement('div');
        div.className = 'question-block border rounded p-2 mb-2';
        div.innerHTML = `
            <div class="mb-2">
                <label class="form-label">Текст вопроса</label>
                <input type="text" name="steps[${stepIndex}][questions][${questionCount}][text]" class="form-control" required>
            </div>
            <div class="choices-container">
                <div class="input-group mb-1">
                    <input type="text" name="steps[${stepIndex}][questions][${questionCount}][choices][0][text]" class="form-control" required>
                    <div class="input-group-text">
                        <input type="checkbox" name="steps[${stepIndex}][questions][${questionCount}][choices][0][is_correct]">
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary add-choice" data-step="${stepIndex}" data-question="${questionCount}">Добавить вариант</button>
        `;
        questionsContainer.insertBefore(div, e.target);
    }

    if (e.target.classList.contains('add-choice')) {
        const stepIndex = e.target.dataset.step;
        const questionIndex = e.target.dataset.question;
        const questionBlock = e.target.closest('.question-block');
        const choicesContainer = questionBlock.querySelector('.choices-container');
        const choiceCount = choicesContainer.querySelectorAll('.input-group').length;

        const div = document.createElement('div');
        div.className = 'input-group mb-1';
        div.innerHTML = `
            <input type="text" name="steps[${stepIndex}][questions][${questionIndex}][choices][${choiceCount}][text]" class="form-control" required>
            <div class="input-group-text">
                <input type="checkbox" name="steps[${stepIndex}][questions][${questionIndex}][choices][${choiceCount}][is_correct]">
            </div>
        `;
        choicesContainer.appendChild(div);
    }
});
</script>
