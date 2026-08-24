<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Проверка ответа</h4>
            </div>
            <div class="card-body">
                <p><strong>Студент:</strong> <?php echo htmlspecialchars($answer['student_fio'] ?? ''); ?></p>
                <p><strong>Единица:</strong> <?php echo htmlspecialchars($unit['title']); ?></p>
                <p><strong>Курс:</strong> <?php echo htmlspecialchars($course['full_name']); ?></p>

                <hr>

                <?php if ($answer['answer_text']): ?>
                <div class="mb-3">
                    <h5>Текст ответа:</h5>
                    <div class="border rounded p-3"><?php echo nl2br(htmlspecialchars($answer['answer_text'])); ?></div>
                </div>
                <?php endif; ?>

                <?php if ($answer['answer_file']): ?>
                <div class="mb-3">
                    <h5>Файл ответа:</h5>
                    <a href="/<?php echo htmlspecialchars($answer['answer_file']); ?>" target="_blank" class="btn btn-outline-primary"><i class="bi bi-download"></i> Скачать файл</a>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Балл (<?php echo $unit['max_score']; ?> — макс.)</label>
                        <input type="number" name="score" class="form-control" min="0" max="<?php echo $unit['max_score']; ?>" value="<?php echo $answer['score'] ?? ''; ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="passed" class="form-check-input" id="passed" <?php echo $answer['passed'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="passed">Зачтено</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Комментарий</label>
                        <textarea name="comment" class="form-control" rows="3"><?php echo htmlspecialchars($answer['comment'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить проверку</button>
                    <a href="/courses/<?php echo $course['id']; ?>/answers/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
