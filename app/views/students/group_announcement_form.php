
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?php echo $announcement ? 'Редактировать объявление' : 'Новое объявление для группы'; ?> <?php echo htmlspecialchars($group['group_number']); ?></h2>
    <a href="/students/groups/<?php echo $group['id']; ?>/announcements/" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Назад</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/students/groups/<?php echo $group['id']; ?>/announcements/create/">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
            <div class="mb-3">
                <label class="form-label">Текст объявления *</label>
                <textarea name="text" class="form-control" rows="5" required><?php echo htmlspecialchars($announcement['text'] ?? $_POST['text'] ?? ''); ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?php echo $announcement ? 'Сохранить' : 'Создать'; ?></button>
                <a href="/students/groups/<?php echo $group['id']; ?>/announcements/" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
