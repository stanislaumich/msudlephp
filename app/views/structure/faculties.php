<h2>Факультеты</h2>
<a href="/structure/faculties/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Добавить факультет</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Полное наименование</th><th>Краткое наименование</th><th>Университет</th><th>Декан</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($faculties ?? [] as $f): ?>
            <tr>
                <td><?php echo htmlspecialchars($f['full_name']); ?></td>
                <td><?php echo htmlspecialchars($f['short_name']); ?></td>
                <td><?php echo htmlspecialchars($f['university_id'] ? ($universities[array_search($f['university_id'], array_column($universities, 'id'))]['short_name'] ?? $f['university_id']) : '—'); ?></td>
                <td><?php echo htmlspecialchars($f['dean'] ? trim(($f['dean']['last_name'] ?? '') . ' ' . ($f['dean']['first_name'] ?? '')) : '—'); ?></td>
                <td>
                    <a href="/structure/faculties/<?php echo $f['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
