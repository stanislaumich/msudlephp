<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $shifr ? 'Редактирование' : 'Создание'; ?> кода специальности</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Код *</label>
                        <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($shifr['code'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($shifr['name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Квалификация</label>
                        <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($shifr['qualification'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Факультет</label>
                        <select name="faculty" class="form-select">
                            <option value="">— не выбран —</option>
                            <?php foreach ($faculties ?? [] as $f): ?>
                            <option value="<?php echo $f['id']; ?>" <?php echo ($shifr['faculty_id'] ?? '') == $f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/umo/shifrs/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
