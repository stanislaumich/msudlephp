
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $group ? 'Редактирование' : 'Создание'; ?> группы преподавателей</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($group['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($group['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Преподаватели</label>
                        <?php foreach ($users ?? [] as $u): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="users[]" value="<?php echo $u['id']; ?>" id="user_<?php echo $u['id']; ?>" <?php echo ($group && in_array($u['id'], $member_ids ?? [])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="user_<?php echo $u['id']; ?>"><?php echo htmlspecialchars(trim(($u['last_name'] ?? '') . ' ' . ($u['first_name'] ?? ''))); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/accounts/groups/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
