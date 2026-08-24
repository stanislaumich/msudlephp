
<?php
$authorName = function($author) {
    if (!$author) return '';
    $parts = [];
    if (!empty($author['last_name'])) $parts[] = $author['last_name'];
    if (!empty($author['first_name'])) $parts[] = $author['first_name'];
    return implode(' ', $parts) ?: ($author['username'] ?? '');
};
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Объявления группы <?php echo htmlspecialchars($group['group_number']); ?></h2>
    <?php if (\Core\Auth::guard() !== 'student'): ?>
    <a href="/students/groups/<?php echo $group['id']; ?>/announcements/create/" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Создать объявление</a>
    <?php endif; ?>
</div>

<?php if (empty($announcements)): ?>
<div class="alert alert-info">Нет объявлений для этой группы.</div>
<?php else: ?>
<div class="list-group">
    <?php foreach ($announcements as $a): ?>
    <div class="list-group-item">
        <div class="d-flex justify-content-between">
            <strong><?php echo htmlspecialchars($authorName($authors[$a['author_id']] ?? null)); ?></strong>
            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($a['created_at'])); ?></small>
        </div>
        <div class="mt-2"><?php echo nl2br(htmlspecialchars($a['text'])); ?></div>
        <?php if (\Core\Auth::guard() !== 'student' && ((int)($authors[$a['author_id']] ?? ['id' => 0])['id']) === \Core\Auth::user()['id'] || \Core\Role::isAdmin($user ?? [], $role ?? '')): ?>
        <form method="POST" action="/students/groups/<?php echo $group['id']; ?>/announcements/<?php echo $a['id']; ?>/delete/" class="d-inline mt-2" onsubmit="return confirm('Удалить объявление?')">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Удалить</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
