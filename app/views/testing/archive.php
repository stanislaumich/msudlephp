<h2>Архив удалённых тестов</h2>
<a href="/testing/" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i>Назад к тестам</a>

<?php if (empty($deleted_tests)): ?>
<div class="alert alert-info">Архив пуст.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Название</th><th>Автор</th><th>Дисциплина</th><th>Дата удаления</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($deleted_tests as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['name']); ?></td>
                <td><?php echo htmlspecialchars($t['author_name']); ?></td>
                <td><?php echo htmlspecialchars($t['subject_name']); ?></td>
                <td><?php echo $t['deleted_at'] ? date('d.m.Y H:i', strtotime($t['deleted_at'])) : '—'; ?></td>
                <td>
                    <form method="POST" action="/testing/archive/<?php echo $t['id']; ?>/restore/" class="d-inline" onsubmit="return confirm('Восстановить тест «<?php echo htmlspecialchars($t['name']); ?>»?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i> Восстановить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
