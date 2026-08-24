<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $course ? 'Редактирование' : 'Создание'; ?> курса</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Предмет *</label>
                        <select name="subject" class="form-select" required>
                            <option value="">Выберите предмет</option>
                            <?php foreach ($subjects ?? [] as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($course['subject_id'] ?? '') == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Полное наименование *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($course['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Краткое наименование *</label>
                        <input type="text" name="short_name" class="form-control" value="<?php echo htmlspecialchars($course['short_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Идентификатор</label>
                        <input type="text" name="identifier" class="form-control" value="<?php echo htmlspecialchars($course['identifier'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип курса</label>
                        <select name="course_type" class="form-select">
                            <option value="">— не выбран —</option>
                            <?php foreach ($course_types ?? [] as $ct): ?>
                            <option value="<?php echo $ct['id']; ?>" <?php echo ($course['course_type_id'] ?? '') == $ct['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ct['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/courses/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
