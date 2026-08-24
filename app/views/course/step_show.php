
<h2><?php echo htmlspecialchars($unit['title']); ?></h2>

<?php if (Core\Auth::guard() === 'teacher'): ?>
<a href="/steps/<?php echo $unit['id']; ?>/edit/" class="btn btn-outline-primary mb-3"><i class="bi bi-pencil me-1"></i>Редактировать</a>
<a href="/steps/" class="btn btn-secondary mb-3">Назад к списку</a>
<?php endif; ?>

<?php foreach ($steps ?? [] as $step): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Шаг <?php echo $step['order'] + 1; ?>: <?php echo htmlspecialchars($step['title']); ?></h5>
        <?php if (isset($progress[$step['id']]) && $progress[$step['id']]['completed']): ?>
        <span class="badge bg-success">Пройден</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($step['content']): ?>
        <p><?php echo nl2br(htmlspecialchars($step['content'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($step['questions'])): ?>
        <form method="POST" action="/steps/<?php echo $unit['id']; ?>/take/">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <input type="hidden" name="step_id" value="<?php echo $step['id']; ?>">
            <?php foreach ($step['questions'] as $q): ?>
            <div class="mb-3">
                <p class="fw-bold"><?php echo htmlspecialchars($q['text']); ?></p>
                <?php foreach ($q['choices'] as $c): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="answers[<?php echo $q['id']; ?>][]" value="<?php echo $c['id']; ?>" id="choice_<?php echo $c['id']; ?>">
                    <label class="form-check-label" for="choice_<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['text']); ?></label>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Проверить и продолжить</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
