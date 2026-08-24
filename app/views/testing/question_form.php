<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $question ? 'Редактирование вопроса' : 'Новый вопрос'; ?> для теста "<?php echo htmlspecialchars($test['name']); ?>"</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Текст вопроса *</label>
                        <textarea name="text" class="form-control" rows="3" required><?php echo htmlspecialchars($question['text'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип вопроса</label>
                        <select name="question_type" class="form-select">
                            <option value="single" <?php echo ($question['question_type'] ?? 'single') === 'single' ? 'selected' : ''; ?>>Один ответ</option>
                            <option value="multiple" <?php echo ($question['question_type'] ?? 'single') === 'multiple' ? 'selected' : ''; ?>>Несколько ответов</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Порядок</label>
                            <input type="number" name="order" class="form-control" value="<?php echo $question['order'] ?? 0; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Баллы</label>
                            <input type="number" name="score" class="form-control" value="<?php echo $question['score'] ?? 1; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $question ? 'Сохранить' : 'Создать'; ?></button>
                    <a href="/testing/<?php echo $test['id']; ?>/" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
</div>
