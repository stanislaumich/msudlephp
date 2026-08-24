<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $faculty ? 'Редактирование' : 'Создание'; ?> факультета</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Университет *</label>
                        <select name="university" class="form-select" required>
                            <option value="">Выберите университет</option>
                            <?php foreach ($universities ?? [] as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($faculty['university_id'] ?? '') == $u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Полное наименование *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($faculty['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Краткое наименование *</label>
                        <input type="text" name="short_name" class="form-control" value="<?php echo htmlspecialchars($faculty['short_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Идентификатор</label>
                        <input type="text" name="identifier" class="form-control" value="<?php echo htmlspecialchars($faculty['identifier'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Декан</label>
                        <select name="dean" class="form-select">
                            <option value="">— не выбран —</option>
                            <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($faculty['dean_id'] ?? '') == $u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars(trim(($u['last_name'] ?? '') . ' ' . ($u['first_name'] ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Номера групп</label>
                        <input type="text" name="group_numbers" class="form-control" value="<?php echo htmlspecialchars($faculty['group_numbers'] ?? ''); ?>" placeholder="1,2,3">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/structure/faculties/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
