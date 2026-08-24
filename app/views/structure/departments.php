<h2>Кафедры</h2>
<a href="/structure/departments/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Добавить кафедру</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Полное наименование</th><th>Краткое наименование</th><th>Факультет</th><th>Заведующий</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($departments ?? [] as $d): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['full_name']); ?></td>
                <td><?php echo htmlspecialchars($d['short_name']); ?></td>
                <td><?php echo htmlspecialchars($d['faculty_id'] ? ($faculties[array_search($d['faculty_id'], array_column($faculties, 'id'))]['short_name'] ?? $d['faculty_id']) : '—'); ?></td>
                <td><?php echo htmlspecialchars($d['head'] ? trim(($d['head']['last_name'] ?? '') . ' ' . ($d['head']['first_name'] ?? '')) : '—'); ?></td>
                <td>
                    <a href="/structure/departments/<?php echo $d['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
