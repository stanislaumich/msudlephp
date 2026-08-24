
<h4>Чат: <?php echo htmlspecialchars($room['course']['short_name'] ?? ''); ?> — <?php echo htmlspecialchars($room['student']['fio'] ?? ''); ?></h4>

<div class="border rounded p-3 mb-3" style="height: 400px; overflow-y: auto;" id="chat-messages">
    <?php foreach ($messages ?? [] as $m): ?>
    <div class="mb-2 <?php echo $m['sender_student_id'] == ($user['id'] ?? 0) ? 'text-end' : ''; ?>">
        <div class="d-inline-block p-2 rounded <?php echo $m['sender_student_id'] == ($user['id'] ?? 0) ? 'bg-primary text-white' : 'bg-light'; ?>">
            <strong><?php echo htmlspecialchars($m['sender_student'] ? $m['sender_student']['fio'] : ($m['sender_user'] ? trim(($m['sender_user']['last_name'] ?? '') . ' ' . ($m['sender_user']['first_name'] ?? '')) : '?')); ?></strong>
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($m['text'])); ?></p>
            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($m['created_at'])); ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<form method="POST" action="/chat/<?php echo $room['id']; ?>/send/" class="d-flex gap-2">
    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
    <input type="text" name="text" class="form-control" placeholder="Введите сообщение..." required autofocus>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>

<a href="/chat/" class="btn btn-secondary mt-3">Назад</a>
