
<h2>Тесты</h2>
<a href="/testing/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Создать тест</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Название</th><th>Дисциплина</th><th>Автор</th><th>Дата создания</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($tests ?? [] as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['name']); ?></td>
                <td><?php echo htmlspecialchars($t['subject']['full_name'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($t['author'] ? trim(($t['author']['last_name'] ?? '') . ' ' . ($t['author']['first_name'] ?? '')) : '—'); ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($t['created_at'])); ?></td>
                <td>
                    <a href="/testing/<?php echo $t['id']; ?>/" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <a href="/testing/<?php echo $t['id']; ?>/edit/" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/testing/<?php echo $t['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить тест?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
