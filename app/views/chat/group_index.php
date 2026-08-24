
<h2>Групповые чаты</h2>

<div class="list-group">
    <?php foreach ($group_chats ?? [] as $gc): ?>
    <a href="/chat/groups/<?php echo $gc['group']['id'] ?? $gc['group_id']; ?>/" class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong><?php echo htmlspecialchars($gc['group']['group_number'] ?? 'Группа #' . $gc['group_id']); ?></strong>
                <br><small class="text-muted">Сообщений: <?php
                    if (Auth::guard() === 'student'):
                        $unread = \Core\Database::fetch("SELECT COUNT(*) as cnt FROM chat_groupchatmessage WHERE room_id = ? AND is_read = 0", [$gc['id']]);
                        echo $unread['cnt'] ?? 0;
                    endif;
                ?></small>
            </div>
            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($gc['created_at'])); ?></small>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($group_chats)): ?>
<div class="alert alert-info">Нет групповых чатов.</div>
<?php endif; ?>

<a href="/chat/" class="btn btn-secondary mt-3">Назад к личным чатам</a>
