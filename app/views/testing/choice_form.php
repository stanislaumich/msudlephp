<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $choice ? 'Редактирование варианта' : 'Новый вариант'; ?> ответа</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Текст варианта ответа *</label>
                        <textarea name="text" class="form-control" rows="2" required><?php echo htmlspecialchars($choice['text'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_correct" id="is_correct" <?php echo ($choice['is_correct'] ?? false) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_correct">Правильный ответ</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $choice ? 'Сохранить' : 'Создать'; ?></button>
                    <a href="/testing/<?php echo $test['id']; ?>/" class="btn btn-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
</div>
