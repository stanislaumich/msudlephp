
<h2>Группы преподавателей</h2>
<a href="/accounts/groups/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Создать группу</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Название</th><th>Описание</th><th>Участников</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($groups ?? [] as $g): ?>
            <tr>
                <td><?php echo htmlspecialchars($g['name']); ?></td>
                <td><?php echo htmlspecialchars($g['description'] ?? '—'); ?></td>
                <td><?php echo count($g['users'] ?? []); ?></td>
                <td>
                    <a href="/accounts/groups/<?php echo $g['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/accounts/groups/<?php echo $g['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить группу?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
