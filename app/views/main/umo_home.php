<h2>Панель УМО / Ректорат</h2>
<p>Роль: <span class="badge bg-success"><?php echo \Core\Role::label($role ?? ''); ?></span></p>

<div class="row">
    <?php foreach ($faculties_data ?? [] as $fac): ?>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo htmlspecialchars($fac['full_name']); ?></h5>
            </div>
            <div class="card-body">
                <?php foreach ($fac['departments'] ?? [] as $dept): ?>
                <div class="mb-3">
                    <strong><?php echo htmlspecialchars($dept['full_name']); ?></strong>
                    <?php foreach ($dept['subjects'] ?? [] as $subj): ?>
                    <div class="ms-3 mt-2">
                        <strong><?php echo htmlspecialchars($subj['full_name']); ?></strong>
                        <?php foreach ($subj['courses'] ?? [] as $c): ?>
                        <div class="ms-3 mt-1">
                            <a href="/courses/<?php echo $c['id']; ?>/" class="text-decoration-none">
                                <?php echo htmlspecialchars($c['full_name']); ?>
                            </a>
                            <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($c['type_name'] ?? '—'); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($faculties_data)): ?>
<div class="alert alert-info">Нет данных для отображения.</div>
<?php endif; ?>
