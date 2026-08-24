<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $department ? 'Редактирование' : 'Создание'; ?> кафедры</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Факультет *</label>
                        <select name="faculty" class="form-select" required>
                            <option value="">Выберите факультет</option>
                            <?php foreach ($faculties ?? [] as $f): ?>
                            <option value="<?php echo $f['id']; ?>" <?php echo ($department['faculty_id'] ?? '') == $f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Полное наименование *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($department['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Краткое наименование *</label>
                        <input type="text" name="short_name" class="form-control" value="<?php echo htmlspecialchars($department['short_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Идентификатор</label>
                        <input type="text" name="identifier" class="form-control" value="<?php echo htmlspecialchars($department['identifier'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Заведующий кафедрой</label>
                        <select name="head" class="form-select">
                            <option value="">— не выбран —</option>
                            <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($department['head_id'] ?? '') == $u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim(($u['last_name'] ?? '') . ' ' . ($u['first_name'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/structure/departments/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
