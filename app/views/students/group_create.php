
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $group ? 'Редактирование' : 'Создание'; ?> группы</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Номер группы *</label>
                        <input type="text" name="group_number" class="form-control" value="<?php echo htmlspecialchars($group['group_number'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Шифр специальности</label>
                        <select name="shifr" class="form-select">
                            <option value="">— не выбрано —</option>
                            <?php foreach ($shifrs ?? [] as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($group['shifr_id'] ?? '') == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['code'] . ' — ' . ($s['name'] ?? '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Год поступления</label>
                            <input type="number" name="enrollment_year" class="form-control" value="<?php echo htmlspecialchars($group['enrollment_year'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Срок (лет)</label>
                            <input type="number" name="study_duration_years" class="form-control" value="<?php echo htmlspecialchars($group['study_duration_years'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Срок (месяцев)</label>
                            <input type="number" name="study_duration_months" class="form-control" value="<?php echo htmlspecialchars($group['study_duration_months'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Факультет</label>
                        <select name="faculty" class="form-select">
                            <option value="">— не выбрано —</option>
                            <?php foreach ($faculties ?? [] as $f): ?>
                            <option value="<?php echo $f['id']; ?>" <?php echo ($group['faculty_id'] ?? '') == $f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Форма обучения</label>
                        <select name="education_form" class="form-select">
                            <option value="">— не выбрано —</option>
                            <option value="daytime" <?php echo ($group['education_form'] ?? '') === 'daytime' ? 'selected' : ''; ?>>Дневная</option>
                            <option value="correspondence" <?php echo ($group['education_form'] ?? '') === 'correspondence' ? 'selected' : ''; ?>>Заочная</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/students/groups/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
