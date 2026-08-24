<h2>Отчёт по группе: <?php echo htmlspecialchars($group['group_number']); ?></h2>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>ФИО</th>
                <th>Средний балл</th>
                <th>Всего баллов</th>
                <th>Проверено</th>
                <th>Всего заданий</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_rows ?? [] as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student']['fio']); ?></td>
                <td><?php echo $row['avg_score']; ?></td>
                <td><?php echo $row['total_score']; ?></td>
                <td><?php echo $row['checked_count']; ?></td>
                <td><?php echo $row['total_units']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<a href="/students/groups/" class="btn btn-secondary mt-3">← Назад к группам</a>
