
<h2>Преподаватели</h2>
<a href="/accounts/teachers/create/" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Добавить преподавателя</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>ФИО</th><th>Логин</th><th>Email</th><th>Должность</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($teachers ?? [] as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars(trim(($t['last_name'] ?? '') . ' ' . ($t['first_name'] ?? ''))); ?></td>
                <td><?php echo htmlspecialchars($t['username']); ?></td>
                <td><?php echo htmlspecialchars($t['email']); ?></td>
                <td><?php echo htmlspecialchars($t['teacher_profile']['position'] ?? '—'); ?></td>
                <td>
                    <a href="/accounts/teachers/<?php echo $t['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/accounts/teachers/<?php echo $t['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить преподавателя?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
