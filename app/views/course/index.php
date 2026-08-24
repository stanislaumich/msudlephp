<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Курсы</h2>
    <a href="/courses/create/" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Создать курс</a>
</div>

<div class="row">
    <?php foreach ($courses ?? [] as $c): ?>
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($c['full_name']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars($c['subject']['full_name'] ?? ''); ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-<?php echo $c['is_deleted'] ? 'danger' : 'success'; ?>">
                        <?php echo $c['is_deleted'] ? 'Удалён' : 'Активный'; ?>
                    </span>
                    <a href="/courses/<?php echo $c['id']; ?>/" class="btn btn-sm btn-primary">Открыть</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($courses)): ?>
<div class="alert alert-info">Курсов пока нет.</div>
<?php endif; ?>
