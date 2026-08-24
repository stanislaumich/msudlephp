<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $university ? 'Редактирование' : 'Создание'; ?> университета</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Полное наименование *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($university['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Краткое наименование *</label>
                        <input type="text" name="short_name" class="form-control" value="<?php echo htmlspecialchars($university['short_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Идентификатор</label>
                        <input type="text" name="identifier" class="form-control" value="<?php echo htmlspecialchars($university['identifier'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/structure/universities/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
