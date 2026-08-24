<h2>Архив удалённых студентов</h2>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>ФИО</th>
                <th>Логин</th>
                <th>Группа</th>
                <th>Дата удаления</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deleted_students ?? [] as $d): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['fio']); ?></td>
                <td><?php echo htmlspecialchars($d['login']); ?></td>
                <td><?php echo htmlspecialchars($d['group_name']); ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($d['deleted_at'])); ?></td>
                <td>
                    <a href="/students/archive/<?php echo $d['id']; ?>/restore/" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i> Восстановить</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (empty($deleted_students)): ?>
<div class="alert alert-info">Архив пуст.</div>
<?php endif; ?>
