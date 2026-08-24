<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Восстановление студента</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <p><strong>Студент:</strong> <?php echo htmlspecialchars($deleted['fio']); ?></p>
                <p><strong>Логин:</strong> <?php echo htmlspecialchars($deleted['login']); ?></p>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Группа</label>
                        <select name="group" class="form-select">
                            <option value="">Без группы</option>
                            <?php foreach ($groups ?? [] as $g): ?>
                            <option value="<?php echo $g['id']; ?>" <?php echo ($deleted['group_id'] ?? '') == $g['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['group_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Восстановить</button>
                    <a href="/students/archive/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
