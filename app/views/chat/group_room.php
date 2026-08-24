
<h4>Групповой чат: <?php echo htmlspecialchars($group['group_number'] ?? 'Группа #' . $group['group_id']); ?></h4>

<div class="border rounded p-3 mb-3" style="height: 400px; overflow-y: auto;" id="chat-messages">
    <?php foreach ($messages ?? [] as $m): ?>
    <div class="mb-2 <?php echo $m['sender_student_id'] == ($user['id'] ?? 0) ? 'text-end' : ''; ?>">
        <div class="d-inline-block p-2 rounded <?php echo $m['sender_student_id'] == ($user['id'] ?? 0) ? 'bg-primary text-white' : 'bg-light'; ?>">
            <strong><?php echo htmlspecialchars($m['sender_student'] ? $m['sender_student']['fio'] : '?'); ?></strong>
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($m['text'])); ?></p>
            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($m['created_at'])); ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (Auth::guard() === 'student'): ?>
<form method="POST" action="/chat/groups/<?php echo $group['id']; ?>/" class="d-flex gap-2">
    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
    <input type="text" name="text" class="form-control" placeholder="Введите сообщение..." required autofocus>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>
<?php endif; ?>

<div class="mt-3">
    <form method="POST" action="/chat/groups/<?php echo $group['id']; ?>/read/" class="d-inline">
        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check-all"></i> Отметить все как прочитанные</button>
    </form>
    <a href="/chat/groups/" class="btn btn-secondary ms-2">Назад</a>
</div>
