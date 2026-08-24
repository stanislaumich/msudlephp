
<h2><?php echo htmlspecialchars($test['name']); ?></h2>
<p class="text-muted"><?php echo htmlspecialchars($test['description'] ?? ''); ?></p>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
    <?php foreach ($questions ?? [] as $q): ?>
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold"><?php echo htmlspecialchars($q['text']); ?> <span class="text-muted">(<?php echo $q['score']; ?> балл<?php echo $q['score'] > 1 ? 'ов' : ''; ?>)</span></p>
            <?php if ($q['question_type'] === 'multiple'): ?>
                <?php foreach ($q['choices'] ?? [] as $c): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="answers[<?php echo $q['id']; ?>][]" value="<?php echo $c['id']; ?>" id="choice_<?php echo $c['id']; ?>">
                    <label class="form-check-label" for="choice_<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['text']); ?></label>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($q['choices'] ?? [] as $c): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $c['id']; ?>" id="choice_<?php echo $c['id']; ?>">
                    <label class="form-check-label" for="choice_<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['text']); ?></label>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-primary btn-lg">Завершить тест</button>
</form>

<a href="/testing/" class="btn btn-secondary mt-3">Отмена</a>
