<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Создание объявления</h4>
                <small class="text-muted">Группа: <?php echo htmlspecialchars($group['name']); ?></small>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Текст объявления *</label>
                        <textarea name="text" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['text'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/accounts/groups/<?php echo $group['id']; ?>/announcements/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
