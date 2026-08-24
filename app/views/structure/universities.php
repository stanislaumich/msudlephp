
<h2>Университеты</h2>
<a href="/structure/universities/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Добавить университет</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Полное наименование</th><th>Краткое наименование</th><th>Идентификатор</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($universities ?? [] as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td><?php echo htmlspecialchars($u['short_name']); ?></td>
                <td><?php echo htmlspecialchars($u['identifier'] ?? '—'); ?></td>
                <td>
                    <a href="/structure/universities/<?php echo $u['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
