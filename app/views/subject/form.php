<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $subject ? 'Редактирование' : 'Создание'; ?> дисциплины</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Кафедра *</label>
                        <select name="department" class="form-select" required>
                            <option value="">Выберите кафедру</option>
                            <?php foreach ($departments ?? [] as $d): ?>
                            <option value="<?php echo $d['id']; ?>" <?php echo ($subject['department_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Полное наименование *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($subject['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Краткое наименование *</label>
                        <input type="text" name="short_name" class="form-control" value="<?php echo htmlspecialchars($subject['short_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Идентификатор</label>
                        <input type="text" name="identifier" class="form-control" value="<?php echo htmlspecialchars($subject['identifier'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/subjects/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
