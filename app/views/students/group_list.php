
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Группы студентов</h2>
    <a href="/students/groups/create/" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Создать группу</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/students/groups/" class="row g-3">
            <div class="col-md-4">
                <select name="faculty" class="form-select" onchange="this.form.submit()">
                    <option value="">Все факультеты</option>
                    <?php foreach ($faculties ?? [] as $f): ?>
                    <option value="<?php echo $f['id']; ?>" <?php echo ($current_faculty ?? null) == $f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" name="group_search" class="form-control" placeholder="Поиск по номеру группы..." value="<?php echo htmlspecialchars($group_search ?? ''); ?>">
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
                <th>Номер группы</th>
                <th>Факультет</th>
                <th>Шифр</th>
                <th>Форма</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups ?? [] as $g): ?>
            <tr>
                <td><a href="/students/groups/<?php echo $g['id']; ?>/edit/" class="text-decoration-none"><?php echo htmlspecialchars($g['group_number']); ?></a></td>
                <td><?php echo htmlspecialchars($g['faculty_id'] ? ($faculty_map[$g['faculty_id']]['short_name'] ?? $g['faculty_id']) : '—'); ?></td>
                <td><?php echo htmlspecialchars($g['shifr_id'] ? ($shifr_map[$g['shifr_id']]['code'] ?? $g['shifr_id']) : '—'); ?></td>
                <td><?php echo htmlspecialchars($g['education_form'] ?? '—'); ?></td>
                    <td>
                        <a href="/students/groups/<?php echo $g['id']; ?>/edit/" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="/students/groups/<?php echo $g['id']; ?>/report/" class="btn btn-sm btn-outline-info"><i class="bi bi-bar-chart"></i></a>
                        <a href="/students/groups/<?php echo $g['id']; ?>/announcements/" class="btn btn-sm btn-outline-info"><i class="bi bi-megaphone"></i></a>
                        <form method="POST" action="/students/groups/<?php echo $g['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить группу?')">
                            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
