
<h2>Личный кабинет</h2>

<?php if ($is_student ?? false): ?>
<div class="row">
    <?php foreach ($course_data ?? [] as $c): ?>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($c['full_name']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars($c['department']); ?></p>
                <span class="badge bg-info"><?php echo htmlspecialchars($c['permission']); ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($course_data ?? [] as $c): ?>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($c['full_name']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars($c['department']); ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-<?php echo $c['permission'] === 'Полный доступ' ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($c['permission']); ?></span>
                    <?php if ($c['unchecked_count'] > 0): ?>
                    <span class="badge bg-warning text-dark"><?php echo $c['unchecked_count']; ?> не проверено</span>
                    <?php endif; ?>
                    <a href="/courses/<?php echo $c['id']; ?>/" class="btn btn-sm btn-primary">Открыть</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($course_data)): ?>
<div class="alert alert-info">У вас пока нет доступа к курсам.</div>
<?php endif; ?>
