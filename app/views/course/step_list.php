<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Пошаговые единицы</h2>
    <a href="/steps/create/" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Создать единицу</a>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Название</th><th>Шагов</th><th>Порядок</th><th>Видима</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($units ?? [] as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['title']); ?></td>
                <td><?php echo count($u['steps'] ?? []); ?></td>
                <td><?php echo $u['order']; ?></td>
                <td><?php echo $u['visible'] ? 'Да' : 'Нет'; ?></td>
                <td>
                    <a href="/steps/<?php echo $u['id']; ?>/" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <a href="/steps/<?php echo $u['id']; ?>/edit/" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/steps/<?php echo $u['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить единицу?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (empty($units)): ?>
<div class="alert alert-info">Пошаговых единиц пока нет.</div>
<?php endif; ?>
