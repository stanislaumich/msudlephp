<h2>Типы курсов</h2>
<a href="/course-types/create/" class="btn btn-primary mb-3" data-bs-toggle="tooltip" title="Добавить тип курса"><i class="bi bi-plus-circle me-1"></i>Добавить тип курса</a>

<?php if (empty($types)): ?>
<div class="alert alert-info">Типы курсов ещё не созданы.</div>
<?php endif; ?>

<div class="row">
    <?php foreach ($types ?? [] as $t): ?>
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                <div>
                    <a href="/course-types/<?php echo $t['id']; ?>/edit/" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Редактировать тип курса"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/course-types/<?php echo $t['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить тип курса? Разделы по умолчанию также будут удалены.');">
                        <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Удалить тип курса"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <?php if ($t['description']): ?>
                <p class="text-muted mb-2"><?php echo htmlspecialchars($t['description']); ?></p>
                <?php endif; ?>
                <p class="mb-1"><strong>Разделы по умолчанию:</strong></p>
                <?php if (!empty($t['sections'])): ?>
                <ol class="mb-0">
                    <?php foreach ($t['sections'] as $s): ?>
                    <li><?php echo htmlspecialchars($s['name']); ?></li>
                    <?php endforeach; ?>
                </ol>
                <?php else: ?>
                <p class="text-muted mb-0">Не заданы</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
