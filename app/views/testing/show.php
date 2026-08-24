
<h2><?php echo htmlspecialchars($test['name']); ?></h2>
<p class="text-muted"><?php echo htmlspecialchars($test['description'] ?? ''); ?></p>

<?php foreach ($questions ?? [] as $q): ?>
<div class="card mb-3">
    <div class="card-body">
        <p><?php echo htmlspecialchars($q['text']); ?></p>
        <?php foreach ($q['choices'] ?? [] as $c): ?>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" disabled <?php echo $c['is_correct'] ? 'checked' : ''; ?>>
            <label class="form-check-label"><?php echo htmlspecialchars($c['text']); ?></label>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<a href="/testing/" class="btn btn-secondary">Назад</a>
