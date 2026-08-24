<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $topic ? 'Редактирование' : 'Создание'; ?> темы</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Раздел *</label>
                        <select name="section" class="form-select" required>
                            <option value="">Выберите раздел</option>
                            <?php foreach ($sections ?? [] as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($topic['section_id'] ?? '') == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Название сущности *</label>
                        <input type="text" name="entity_title" class="form-control" value="<?php echo htmlspecialchars($topic['entity_title'] ?? ''); ?>" placeholder="Тема, Параграф, Лекция">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Содержание</label>
                        <input type="text" name="content" class="form-control" value="<?php echo htmlspecialchars($topic['content'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок</label>
                        <input type="number" name="order" class="form-control" value="<?php echo $topic['order'] ?? 0; ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="visible" class="form-check-input" id="visible" <?php echo ($topic['visible'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="visible">Видима студентам</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/courses/<?php echo $course['id']; ?>/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
