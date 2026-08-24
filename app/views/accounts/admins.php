
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Администраторы</h2>
    <a href="/accounts/admins/create/" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Добавить администратора</a>
</div>

<?php if (empty($admins)): ?>
<div class="alert alert-info">Нет администраторов.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>ФИО</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars(trim(($a['last_name'] ?? '') . ' ' . ($a['first_name'] ?? ''))); ?></td>
                <td><?php echo htmlspecialchars($a['username']); ?></td>
                <td><?php echo htmlspecialchars($a['email']); ?></td>
                <td>
                    <a href="/accounts/admins/<?php echo $a['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <?php if ((int)$a['id'] !== (int)($user['id'] ?? 0)): ?>
                    <form method="POST" action="/accounts/admins/<?php echo $a['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить администратора?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
