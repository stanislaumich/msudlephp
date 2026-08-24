<?php
$groupMap = [];
foreach ($groups ?? [] as $g) {
    $groupMap[$g['id']] = $g;
}
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Импорт студентов из CSV</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">Формат CSV: ФИО, логин, пароль, номер группы, номер зачётки (заголовок не обязателен).</p>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_count) && empty($errors)): ?>
                <div class="alert alert-success">Импортировано студентов: <?php echo $success_count; ?></div>
                <?php elseif (!empty($success_count)): ?>
                <div class="alert alert-warning">Импортировано: <?php echo $success_count; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">CSV-файл</label>
                        <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Импортировать</button>
                    <a href="/students/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
