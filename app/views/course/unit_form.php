
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $unit ? 'Редактирование' : 'Создание'; ?> единицы обучения</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Тема</label>
                        <select name="topic" class="form-select">
                            <option value="">— без темы —</option>
                            <?php foreach ($sections ?? [] as $sec): ?>
                                <?php foreach (($sec['topics'] ?? []) as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo ($unit['topic_id'] ?? '') == $t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec['name'] . ' — ' . $t['content']); ?></option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Раздел</label>
                        <select name="section" class="form-select">
                            <option value="">— без раздела —</option>
                            <?php foreach ($sections ?? [] as $sec): ?>
                            <option value="<?php echo $sec['id']; ?>" <?php echo ($unit['section_id'] ?? '') == $sec['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($unit['title'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип содержимого</label>
                        <select name="content_type" class="form-select">
                            <option value="methodical" <?php echo ($unit['content_type'] ?? '') === 'methodical' ? 'selected' : ''; ?>>Методическая единица</option>
                            <option value="lecture" <?php echo ($unit['content_type'] ?? '') === 'lecture' ? 'selected' : ''; ?>>Лекционная единица</option>
                            <option value="control" <?php echo ($unit['content_type'] ?? '') === 'control' ? 'selected' : ''; ?>>Контрольная единица</option>
                            <option value="step_by_step" <?php echo ($unit['content_type'] ?? '') === 'step_by_step' ? 'selected' : ''; ?>>Пошаговая единица</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Файл</label>
                        <input type="file" name="file" class="form-control">
                        <?php if (!empty($unit['file'])): ?>
                        <div class="form-text">Текущий файл: <?php echo htmlspecialchars(basename($unit['file'])); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ссылка</label>
                        <input type="text" name="link" class="form-control" value="<?php echo htmlspecialchars($unit['link'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок</label>
                        <input type="number" name="order" class="form-control" value="<?php echo $unit['order'] ?? 0; ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="visible" class="form-check-input" id="visible" <?php echo ($unit['visible'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="visible">Видима студентам</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип оценки</label>
                        <select name="grading_type" class="form-select">
                            <option value="">— не выбран —</option>
                            <option value="pass_fail" <?php echo ($unit['grading_type'] ?? '') === 'pass_fail' ? 'selected' : ''; ?>>Зачтено / не зачтено</option>
                            <option value="score_100" <?php echo ($unit['grading_type'] ?? '') === 'score_100' ? 'selected' : ''; ?>>Баллы</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тест</label>
                        <select name="test" class="form-select">
                            <option value="">— не выбран —</option>
                            <?php foreach ($tests ?? [] as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($unit['test_id'] ?? '') == $t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Максимальный балл</label>
                        <input type="number" name="max_score" class="form-control" value="<?php echo $unit['max_score'] ?? 10; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/courses/<?php echo $course['id']; ?>/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
