
<h2>Результат теста: <?php echo htmlspecialchars($test['name']); ?></h2>

<div class="alert alert-success">
    <h4>Ваш результат: <?php echo $total_score; ?> из <?php echo $max_score; ?> баллов</h4>
    <p class="mb-0">Процент: <?php echo $max_score > 0 ? round($total_score / $max_score * 100) : 0; ?>%</p>
</div>

<?php foreach ($questions ?? [] as $q): ?>
<div class="card mb-3">
    <div class="card-body">
        <p class="fw-bold"><?php echo htmlspecialchars($q['text']); ?></p>
        <?php foreach ($q['choices'] as $c): ?>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" disabled <?php echo in_array($c['id'], $answers[$q['id']] ?? []) ? 'checked' : ''; ?>>
            <label class="form-check-label <?php echo $c['is_correct'] ? 'text-success fw-bold' : ''; ?>">
                <?php echo htmlspecialchars($c['text']); ?>
                <?php if ($c['is_correct']): ?> ✓ <?php endif; ?>
            </label>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<a href="/testing/" class="btn btn-primary">К списку тестов</a>
