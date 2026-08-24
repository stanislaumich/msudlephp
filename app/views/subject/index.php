<h2>Дисциплины</h2>
<a href="/subjects/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Добавить дисциплину</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Полное наименование</th><th>Краткое наименование</th><th>Кафедра</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($subjects ?? [] as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                <td><?php echo htmlspecialchars($s['short_name']); ?></td>
                <td><?php echo htmlspecialchars($s['department']['full_name'] ?? '—'); ?></td>
                <td>
                    <a href="/subjects/<?php echo $s['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
