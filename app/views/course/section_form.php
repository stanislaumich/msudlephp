<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $section ? 'Редактирование' : 'Создание'; ?> раздела</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($section['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок</label>
                        <input type="number" name="order" class="form-control" value="<?php echo $section['order'] ?? 0; ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="visible" class="form-check-input" id="visible" <?php echo ($section['visible'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="visible">Видим студентам</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/courses/<?php echo $course['id']; ?>/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
