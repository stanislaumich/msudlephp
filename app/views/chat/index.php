
<h2>Чат</h2>

<div class="list-group">
    <?php foreach ($rooms ?? [] as $r): ?>
    <a href="/chat/<?php echo $r['id']; ?>/" class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Курс <?php echo htmlspecialchars($r['course']['short_name'] ?? ''); ?></strong>
                <br><small class="text-muted">Студент: <?php echo htmlspecialchars($r['student']['fio'] ?? ''); ?></small>
            </div>
            <?php if ($r['last_message']): ?>
            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($r['last_message']['created_at'])); ?></small>
            <?php endif; ?>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($rooms)): ?>
<div class="alert alert-info">Нет активных чатов.</div>
<?php endif; ?>
