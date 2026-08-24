<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?php echo htmlspecialchars($course['full_name']); ?></h2>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($course['subject']['full_name'] ?? ''); ?></p>
    </div>
    <div>
        <a href="/courses/<?php echo $course['id']; ?>/edit/" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Редактировать курс"><i class="bi bi-pencil me-1"></i>Редактировать</a>
        <?php if ($course['is_deleted']): ?>
        <form method="POST" action="/courses/<?php echo $course['id']; ?>/restore/" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <button type="submit" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Восстановить курс"><i class="bi bi-arrow-counterclockwise me-1"></i>Восстановить</button>
        </form>
        <?php else: ?>
        <form method="POST" action="/courses/<?php echo $course['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить курс?')">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Удалить курс"><i class="bi bi-trash me-1"></i>Удалить</button>
        </form>
        <?php endif; ?>
        <form method="POST" action="/courses/<?php echo $course['id']; ?>/clone/" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <button type="submit" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Клонировать курс"><i class="bi bi-copy me-1"></i>Клонировать</button>
        </form>
        </div>
    </div>

<a href="/courses/<?php echo $course['id']; ?>/sections/create/" class="btn btn-primary mb-3" data-bs-toggle="tooltip" title="Добавить раздел"><i class="bi bi-plus-circle me-1"></i>Добавить раздел</a>

<?php foreach ($sections ?? [] as $sec): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?php echo htmlspecialchars($sec['name']); ?></h5>
        <div>
            <span data-bs-toggle="tooltip" title="Добавить единицу в раздел">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#add-unit-sec-<?php echo $sec['id']; ?>" aria-expanded="false"><i class="bi bi-plus-square"></i></button>
            </span>
            <span data-bs-toggle="tooltip" title="Добавить тему в раздел">
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="collapse" data-bs-target="#add-topic-<?php echo $sec['id']; ?>" aria-expanded="false"><i class="bi bi-plus-circle"></i></button>
            </span>
            <a href="/courses/<?php echo $course['id']; ?>/sections/<?php echo $sec['id']; ?>/edit/" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Редактировать раздел"><i class="bi bi-pencil"></i></a>
            <form method="POST" action="/courses/<?php echo $course['id']; ?>/sections/<?php echo $sec['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить раздел?')">
                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Удалить раздел"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($sec['topics'])): ?>
        <?php foreach ($sec['topics'] as $topic): ?>
        <div class="mb-3 border-start ps-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><?php echo htmlspecialchars($topic['entity_title'] . ($topic['content'] ? ': ' . $topic['content'] : '')); ?></h6>
                <div>
                    <span data-bs-toggle="tooltip" title="Добавить единицу в тему">
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#add-unit-<?php echo $topic['id']; ?>"><i class="bi bi-plus-circle"></i></button>
                    </span>
                    <a href="/courses/<?php echo $course['id']; ?>/topics/<?php echo $topic['id']; ?>/edit/" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Редактировать тему"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="/courses/<?php echo $course['id']; ?>/topics/<?php echo $topic['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить тему?')">
                        <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Удалить тему"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
            <?php foreach ($topic['units'] ?? [] as $u): ?>
            <div class="ms-3 mt-1">
                <a href="#" class="text-decoration-none"><?php echo htmlspecialchars($u['title']); ?></a>
                <span class="badge bg-<?php echo $u['content_type'] === 'control' ? 'warning' : 'secondary'; ?> ms-1"><?php echo $u['content_type']; ?></span>
            </div>
            <?php endforeach; ?>
            <div class="collapse mt-2" id="add-unit-<?php echo $topic['id']; ?>">
                <form method="POST" action="/courses/<?php echo $course['id']; ?>/units/create/">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <input type="hidden" name="topic" value="<?php echo $topic['id']; ?>">
                    <input type="hidden" name="section" value="<?php echo $sec['id']; ?>">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small mb-1">Название *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Тип</label>
                            <select name="content_type" class="form-select">
                                <option value="methodical">Методическая</option>
                                <option value="lecture">Лекционная</option>
                                <option value="control">Контрольная</option>
                                <option value="step_by_step">Пошаговая</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Порядок</label>
                            <input type="number" name="order" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info w-100">Добавить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
         <?php if (!empty($sec['direct_units'])): ?>
         <?php foreach ($sec['direct_units'] as $u): ?>
         <div class="d-flex justify-content-between align-items-center mt-2">
             <div>
                 <a href="#" class="text-decoration-none"><?php echo htmlspecialchars($u['title']); ?></a>
                 <span class="badge bg-<?php echo $u['content_type'] === 'control' ? 'warning' : 'secondary'; ?> ms-1"><?php echo $u['content_type']; ?></span>
             </div>
             <div>
                 <a href="/courses/<?php echo $course['id']; ?>/units/<?php echo $u['id']; ?>/edit/" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Редактировать единицу"><i class="bi bi-pencil"></i></a>
                 <form method="POST" action="/courses/<?php echo $course['id']; ?>/units/<?php echo $u['id']; ?>/delete/" class="d-inline" onsubmit="return confirm('Удалить единицу?')">
                     <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                     <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Удалить единицу"><i class="bi bi-trash"></i></button>
                 </form>
             </div>
         </div>
         <?php endforeach; ?>
         <?php endif; ?>
        <div class="collapse mt-3" id="add-topic-<?php echo $sec['id']; ?>">
            <form method="POST" action="/courses/<?php echo $course['id']; ?>/topics/create/">
                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                <input type="hidden" name="section" value="<?php echo $sec['id']; ?>">
                <input type="hidden" name="visible" value="1">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Название сущности *</label>
                        <input type="text" name="entity_title" class="form-control" placeholder="Тема, Лекция, Параграф" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small mb-1">Содержание</label>
                        <input type="text" name="content" class="form-control" placeholder="Краткое описание">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success w-100">Добавить тему</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="collapse mt-3" id="add-unit-sec-<?php echo $sec['id']; ?>">
            <form method="POST" action="/courses/<?php echo $course['id']; ?>/units/create/">
                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                <input type="hidden" name="section" value="<?php echo $sec['id']; ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small mb-1">Название *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Тип</label>
                        <select name="content_type" class="form-select">
                            <option value="methodical">Методическая</option>
                            <option value="lecture">Лекционная</option>
                            <option value="control">Контрольная</option>
                            <option value="step_by_step">Пошаговая</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Порядок</label>
                        <input type="number" name="order" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Добавить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
<?php endforeach; ?>
