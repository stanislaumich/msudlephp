
<h2><?php echo htmlspecialchars($unit['title']); ?></h2>

<?php if (empty($steps)): ?>
<div class="alert alert-info">В этой единице пока нет шагов.</div>
<?php else: ?>
<?php
$currentStepIndex = 0;
$currentStep = null;
if (!empty($_POST['step_id'])) {
    foreach ($steps as $i => $step) {
        if ($step['id'] == $_POST['step_id']) {
            $currentStepIndex = $i;
            $currentStep = $step;
            break;
        }
    }
}
if (!$currentStep && !empty($steps)) {
    $currentStep = $steps[0];
    $currentStepIndex = 0;
}

$allCompleted = true;
foreach ($steps as $step) {
    if (empty($step['progress']) || !$step['progress']['completed']) {
        $allCompleted = false;
        break;
    }
}
?>

<?php if ($allCompleted): ?>
<div class="alert alert-success">
    <h4>Поздравляем!</h4>
    <p>Вы успешно прошли все шаги пошаговой единицы.</p>
</div>
<?php else: ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Прогресс</h5>
    </div>
    <div class="card-body">
        <div class="progress mb-3">
            <?php
            $completedCount = 0;
            foreach ($steps as $step) {
                if (!empty($step['progress']) && $step['progress']['completed']) $completedCount++;
            }
            $percent = count($steps) > 0 ? round($completedCount / count($steps) * 100) : 0;
            ?>
            <div class="progress-bar" role="progressbar" style="width: <?php echo $percent; ?>%"><?php echo $percent; ?>%</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($steps as $i => $step): ?>
            <?php
            $isCompleted = !empty($step['progress']) && $step['progress']['completed'];
            $isCurrent = $step['id'] == ($currentStep['id'] ?? 0);
            ?>
            <a href="/steps/<?php echo $unit['id']; ?>/take/?step=<?php echo $step['id']; ?>" class="btn btn-sm <?php echo $isCompleted ? 'btn-success' : ($isCurrent ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                Шаг <?php echo $i + 1; ?>
                <?php if ($isCompleted): ?> ✓ <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($currentStep): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Шаг <?php echo $currentStepIndex + 1; ?>: <?php echo htmlspecialchars($currentStep['title']); ?></h5>
    </div>
    <div class="card-body">
        <?php if ($currentStep['content']): ?>
        <p><?php echo nl2br(htmlspecialchars($currentStep['content'])); ?></p>
        <hr>
        <?php endif; ?>

        <form method="POST" action="/steps/<?php echo $unit['id']; ?>/take/">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <input type="hidden" name="step_id" value="<?php echo $currentStep['id']; ?>">
            <?php foreach ($currentStep['questions'] as $q): ?>
            <div class="mb-3">
                <p class="fw-bold"><?php echo htmlspecialchars($q['text']); ?></p>
                <?php foreach ($q['choices'] as $c): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="answers[<?php echo $q['id']; ?>][]" value="<?php echo $c['id']; ?>" id="choice_<?php echo $c['id']; ?>" <?php echo !empty($step['progress']['answers']) && in_array($c['id'], json_decode($step['progress']['answers'] ?? '[]', true) ?: []) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="choice_<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['text']); ?></label>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Проверить и продолжить</button>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<a href="/dashboard" class="btn btn-secondary mt-3">Назад в дашборд</a>
