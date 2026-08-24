<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $student ? 'Редактирование' : 'Создание'; ?> студента</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">ФИО *</label>
                        <input type="text" name="fio" class="form-control" value="<?php echo htmlspecialchars($student['fio'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Логин *</label>
                        <input type="text" name="login" class="form-control" value="<?php echo htmlspecialchars($student['login'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль <?php echo $student ? '(оставьте пустым, чтобы не менять)' : '*'; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo $student ? '' : 'required'; ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Группа *</label>
                        <select name="group" class="form-select" required>
                            <option value="">Выберите группу</option>
                            <?php foreach ($groups ?? [] as $g): ?>
                            <option value="<?php echo $g['id']; ?>" <?php echo ($student['group_id'] ?? '') == $g['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['group_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Номер зачётной книжки</label>
                        <input type="text" name="record_book_number" class="form-control" value="<?php echo htmlspecialchars($student['record_book_number'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/students/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
