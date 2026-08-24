<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $type ? 'Редактирование' : 'Создание'; ?> типа курса</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($type['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($type['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок</label>
                        <input type="number" name="order" class="form-control" value="<?php echo htmlspecialchars($type['order'] ?? '0'); ?>">
                    </div>

                    <hr>
                    <h5>Разделы по умолчанию</h5>
                    <p class="text-muted">Эти разделы будут автоматически созданы в курсе при его создании с данным типом.</p>

                    <div id="sections-list">
                        <?php foreach (($sections ?? []) as $i => $s): ?>
                        <div class="row g-2 section-row mb-2">
                            <div class="col-7">
                                <input type="text" name="section_name[]" class="form-control" placeholder="Название раздела" value="<?php echo htmlspecialchars($s['name'] ?? ''); ?>">
                            </div>
                            <div class="col-3">
                                <input type="number" name="section_order[]" class="form-control" placeholder="Порядок" value="<?php echo htmlspecialchars($s['order'] ?? ($i + 1)); ?>">
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-section" data-bs-toggle="tooltip" title="Удалить раздел"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn btn-outline-secondary mb-3" id="add-section"><i class="bi bi-plus-circle me-1"></i>Добавить раздел</button>

                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var list = document.getElementById('sections-list');
                        var addBtn = document.getElementById('add-section');

                        function newRow() {
                            var row = document.createElement('div');
                            row.className = 'row g-2 section-row mb-2';
                            row.innerHTML = '<div class="col-7"><input type="text" name="section_name[]" class="form-control" placeholder="Название раздела"></div>' +
                                '<div class="col-3"><input type="number" name="section_order[]" class="form-control" placeholder="Порядок"></div>' +
                                '<div class="col-2"><button type="button" class="btn btn-outline-danger w-100 remove-section" data-bs-toggle="tooltip" title="Удалить раздел"><i class="bi bi-trash"></i></button></div>';
                            list.appendChild(row);
                        }

                        addBtn.addEventListener('click', newRow);

                        list.addEventListener('click', function (e) {
                            if (e.target.closest('.remove-section')) {
                                e.target.closest('.section-row').remove();
                            }
                        });

                        if (list.children.length === 0) {
                            newRow();
                        }
                    });
                    </script>

                    <div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="/course-types/" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
