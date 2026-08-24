
<?php
$groupMap = [];
foreach ($groups ?? [] as $g) {
    $groupMap[$g['id']] = $g;
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Список студентов</h2>
    <div>
        <a href="/students/import/batch/" class="btn btn-success"><i class="bi bi-file-earmark-arrow-down me-1"></i>Импорт из students.csv</a>
        <a href="/students/create/" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Добавить студента</a>
    </div>
</div>

<?php if (!empty($search)): ?>
<div class="alert alert-info">Результаты поиска: «<?php echo htmlspecialchars($search); ?>»</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/students/" class="row g-3">
            <div class="col-md-4">
                <select name="group" class="form-select" onchange="this.form.submit()">
                    <option value="">Все группы</option>
                    <?php foreach ($groups ?? [] as $g): ?>
                    <option value="<?php echo $g['id']; ?>" <?php echo ($current_group ?? null) == $g['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['group_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Поиск по ФИО или логину..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Фильтр</button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>ФИО</th>
                <th>Логин</th>
                <th>Группа</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students ?? [] as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['fio']); ?></td>
                <td><?php echo htmlspecialchars($s['login']); ?></td>
                <td><?php echo htmlspecialchars($s['group_id'] ? ($groupMap[$s['group_id']]['group_number'] ?? $s['group_id']) : '—'); ?></td>
                <td>
                    <a href="/students/<?php echo $s['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/students/<?php echo $s['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить студента?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    <form method="POST" action="/students/<?php echo $s['id']; ?>/soft-delete/" class="d-inline" onsubmit="return confirm('Переместить в архив?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-archive"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (($total ?? 0) > ($per_page ?? 30)): ?>
<nav>
    <ul class="pagination">
        <?php for ($i = 1; $i <= ceil(($total ?? 1) / ($per_page ?? 30)); $i++): ?>
        <li class="page-item <?php echo ($page ?? 1) == $i ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($current_group) && $current_group ? '&group=' . $current_group : ''; ?><?php echo isset($search) && $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
