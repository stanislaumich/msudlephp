<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Ответ на контрольную единицу: <?php echo htmlspecialchars($unit['title']); ?></h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($answer && $answer['checked']): ?>
                <div class="alert alert-info">
                    <p><strong>Статус:</strong> <?php echo $answer['passed'] ? 'Зачтено' : 'Не зачтено'; ?></p>
                    <?php if ($answer['score'] !== null): ?>
                    <p><strong>Балл:</strong> <?php echo $answer['score']; ?></p>
                    <?php endif; ?>
                    <?php if ($answer['comment']): ?>
                    <p><strong>Комментарий:</strong> <?php echo nl2br(htmlspecialchars($answer['comment'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Текст ответа</label>
                        <textarea name="answer_text" class="form-control" rows="5"><?php echo htmlspecialchars($answer['answer_text'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Файл ответа</label>
                        <input type="file" name="answer_file" class="form-control">
                        <?php if (!empty($answer['answer_file'])): ?>
                        <div class="form-text">Текущий файл: <a href="/<?php echo htmlspecialchars($answer['answer_file']); ?>" target="_blank">Скачать</a></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Отправить ответ</button>
                    <a href="/courses/<?php echo $course['id']; ?>/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
