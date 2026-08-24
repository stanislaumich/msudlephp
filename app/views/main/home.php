
<h2>Добро пожаловать, <?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?>!</h2>

<?php if ($role && \Core\Role::isUmo($user, $role)): ?>
<div class="alert alert-info">
    <h5>Режим: <?php echo \Core\Role::label($role); ?></h5>
</div>
<?php endif; ?>

<?php if (!empty($unchecked_courses)): ?>
<div class="card mb-4">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Непроверенные ответы</h5>
    </div>
    <div class="card-body">
        <?php foreach ($unchecked_courses as $uc): ?>
        <a href="/courses/<?php echo $uc['id']; ?>/" class="text-decoration-none">
            <div class="card card-stat mb-2 border-warning">
                <div class="card-body py-2">
                    <strong><?php echo htmlspecialchars($uc['full_name']); ?></strong>
                    <span class="badge bg-warning text-dark ms-2"><?php echo $uc['unchecked_count']; ?> не проверено</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($unread_chats)): ?>
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Непрочитанные сообщения</h5>
    </div>
    <div class="card-body">
        <?php foreach ($unread_chats as $chat): ?>
        <a href="/chat/<?php echo $chat['room_id']; ?>/" class="text-decoration-none">
            <div class="card card-stat mb-2 border-primary">
                <div class="card-body py-2">
                    <strong><?php echo htmlspecialchars($chat['course_id'] ? 'Курс ' . $chat['course_id'] : ''); ?></strong>
                    <span class="badge bg-primary ms-2"><?php echo $chat['unread_count']; ?> новых</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($announcements)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>Объявления</h5>
    </div>
    <div class="card-body">
        <?php foreach ($announcements as $a): ?>
        <div class="border rounded p-3 mb-3" id="announcement-<?php echo $a['id']; ?>">
            <div class="d-flex justify-content-between">
                <strong><?php echo htmlspecialchars($a['course_short_name'] ?? ''); ?></strong>
                <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($a['created_at'])); ?></small>
            </div>
            <p class="mb-0 mt-2" id="announcement-text-<?php echo $a['id']; ?>"><?php echo nl2br(htmlspecialchars($a['text'])); ?></p>

            <?php if (($user['id'] ?? null) == $a['author_id']): ?>
            <form method="POST" action="/announcements/<?php echo $a['id']; ?>/edit/" class="mt-2" id="announcement-edit-form-<?php echo $a['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                <div class="input-group">
                    <textarea name="text" class="form-control" rows="2" id="announcement-edit-text-<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['text']); ?></textarea>
                    <button type="submit" class="btn btn-sm btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit(<?php echo $a['id']; ?>)">Отмена</button>
                </div>
            </form>
            <div class="mt-2" id="announcement-actions-<?php echo $a['id']; ?>">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="startEdit(<?php echo $a['id']; ?>)"><i class="bi bi-pencil"></i> Редактировать</button>
                <form method="POST" action="/announcements/<?php echo $a['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить объявление?')">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Удалить</button>
                </form>
            </div>
            <?php endif; ?>

            <form method="POST" action="/announcements/<?php echo $a['id']; ?>/dismiss/" class="mt-2" id="announcement-dismiss-<?php echo $a['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Скрыть</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function startEdit(id) {
    document.getElementById('announcement-text-' + id).style.display = 'none';
    document.getElementById('announcement-actions-' + id).style.display = 'none';
    document.getElementById('announcement-dismiss-' + id).style.display = 'none';
    document.getElementById('announcement-edit-form-' + id).style.display = 'block';
}
function cancelEdit(id) {
    document.getElementById('announcement-text-' + id).style.display = 'block';
    document.getElementById('announcement-actions-' + id).style.display = 'block';
    document.getElementById('announcement-dismiss-' + id).style.display = 'block';
    document.getElementById('announcement-edit-form-' + id).style.display = 'none';
}
</script>
