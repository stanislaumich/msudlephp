
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $teacher ? 'Редактирование' : 'Создание'; ?> преподавателя</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Логин *</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($teacher['username'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль <?php echo $teacher ? '(оставьте пустым, чтобы не менять)' : '*'; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo $teacher ? '' : 'required'; ?>>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Фамилия</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($teacher['last_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($teacher['first_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Отчество</label>
                        <input type="text" name="middle_name" class="form-control" value="<?php echo htmlspecialchars($teacher['teacher_profile']['middle_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Кафедра</label>
                        <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($teacher['teacher_profile']['department'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Должность</label>
                        <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($teacher['teacher_profile']['position'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Факультеты</label>
                        <?php foreach ($faculties ?? [] as $f): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="faculties[]" value="<?php echo $f['id']; ?>" id="fac_<?php echo $f['id']; ?>" <?php echo ($teacher && in_array($f['id'], array_column($teacher['teacher_profile']['faculties'] ?? [], 'id'))) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="fac_<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['full_name']); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/accounts/teachers/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
