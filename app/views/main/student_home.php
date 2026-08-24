
<h2>Моя успеваемость</h2>

<?php if (empty($courses_data)): ?>
<div class="alert alert-info">Вы пока не записаны на курсы.</div>
<?php else: ?>
<?php foreach ($courses_data as $cd): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><?php echo htmlspecialchars($cd['course']['full_name']); ?></h5>
    </div>
    <div class="card-body">
<div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Единица контроля</th>
                    <th>Статус</th>
                    <th>Балл</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cd['cells'] as $cell): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cell['unit']['title']); ?></td>
                    <td>
                        <?php if ($cell['answer']): ?>
                            <?php if ($cell['answer']['checked']): ?>
                                <span class="badge bg-success">Проверено</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Ожидает проверки</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-secondary">Не сдано</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $cell['answer']['score'] ?? '—'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        <strong>Средний балл: <?php echo $cd['avg_score']; ?></strong>
    </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($announcements)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Объявления</h5>
    </div>
    <div class="card-body">
        <?php foreach ($announcements as $a): ?>
        <div class="alert alert-info mb-2">
            <small class="text-muted"><?php echo htmlspecialchars($a['course_short_name'] ?? ''); ?> · <?php echo date('d.m.Y H:i', strtotime($a['created_at'])); ?></small>
            <div><?php echo htmlspecialchars($a['text']); ?></div>
            <form method="POST" action="/announcements/<?php echo $a['id']; ?>/dismiss/" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary">✕</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($unread_chats)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Непрочитанные сообщения</h5>
    </div>
    <div class="card-body">
        <?php foreach ($unread_chats as $chat): ?>
        <div class="alert alert-warning mb-2">
            <small class="text-muted">Курс #<?php echo $chat['course_id']; ?> · <?php echo count($chat['messages']); ?> сообщений</small>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
