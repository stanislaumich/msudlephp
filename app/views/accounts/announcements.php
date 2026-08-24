<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Объявления группы: <?php echo htmlspecialchars($group['name']); ?></h2>
    <a href="/accounts/groups/<?php echo $group['id']; ?>/announcements/create/" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Создать объявление</a>
</div>

<?php if (empty($announcements)): ?>
<div class="alert alert-info">Нет объявлений.</div>
<?php else: ?>
<div class="list-group">
    <?php foreach ($announcements as $a): ?>
    <div class="list-group-item">
        <div class="d-flex justify-content-between">
            <strong><?php echo htmlspecialchars(($a['author']['last_name'] ?? $a['author']['username']) . ' ' . ($a['author']['first_name'] ?? '')); ?></strong>
            <small class="text-muted"><?php echo htmlspecialchars($a['created_at']); ?></small>
        </div>
        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($a['text'])); ?></p>
        <form method="POST" action="/accounts/groups/<?php echo $group['id']; ?>/announcements/<?php echo $a['id']; ?>/delete/" class="mt-2" onsubmit="return confirm('Удалить объявление?')">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Удалить</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<a href="/accounts/groups/" class="btn btn-secondary mt-3">← Назад к группам</a>
