
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $test ? 'Редактирование' : 'Создание'; ?> теста</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Дисциплина *</label>
                        <select name="subject" class="form-select" required>
                            <option value="">Выберите дисциплину</option>
                            <?php foreach ($subjects ?? [] as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($test['subject_id'] ?? '') == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($test['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($test['description'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/testing/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($test && ($questions ?? [])): ?>
<div class="mt-4">
    <h5>Вопросы</h5>
    <?php foreach ($questions as $q): ?>
    <div class="card mb-2">
        <div class="card-body">
            <p><?php echo htmlspecialchars($q['text']); ?></p>
            <?php foreach ($q['choices'] ?? [] as $c): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" disabled <?php echo $c['is_correct'] ? 'checked' : ''; ?>>
                <label class="form-check-label"><?php echo htmlspecialchars($c['text']); ?></label>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
