<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Пакетный импорт студентов из students.csv</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">Файл <strong>students.csv</strong> должен находиться в корне проекта. Формат: Фамилия;Имя;Отчество;Факультет;Форма обучения;Вид обучения;Специальность;Группа;Номер зачетки. Группы и факультеты будут созданы автоматически.</p>

                <?php if (!empty($created_faculties)): ?>
                <div class="alert alert-success">
                    <strong>Созданы факультеты:</strong>
                    <ul class="mb-0">
                        <?php foreach ($created_faculties as $f): ?>
                        <li><?php echo htmlspecialchars($f); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($created_groups)): ?>
                <div class="alert alert-info">
                    <strong>Созданы группы:</strong>
                    <ul class="mb-0">
                        <?php foreach ($created_groups as $g): ?>
                        <li><?php echo htmlspecialchars($g); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_count) && empty($errors)): ?>
                <div class="alert alert-success">Импортировано студентов: <?php echo $success_count; ?></div>
                <?php elseif (!empty($success_count)): ?>
                <div class="alert alert-warning">Импортировано: <?php echo $success_count; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <button type="submit" class="btn btn-primary">Импортировать из students.csv</button>
                    <a href="/students/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
