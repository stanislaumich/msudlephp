<h2>Коды специальностей (УМО)</h2>
<a href="/umo/shifrs/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Добавить код</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Код</th><th>Название</th><th>Квалификация</th><th>Факультет</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($shifrs ?? [] as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['code']); ?></td>
                <td><?php echo htmlspecialchars($s['name'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($s['qualification'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($s['faculty']['full_name'] ?? '—'); ?></td>
                <td>
                    <a href="/umo/shifrs/<?php echo $s['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
