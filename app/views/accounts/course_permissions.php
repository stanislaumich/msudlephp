<h2>Права доступа к курсу: <?php echo htmlspecialchars($course['title'] ?? $course['name'] ?? ''); ?></h2>

<div class="row mt-4">
    <div class="col-md-6">
        <h4>Пользователи с доступом</h4>
        <?php if (empty($user_permissions)): ?>
        <div class="alert alert-info">Нет прав пользователей.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead class="table-dark">
                    <tr><th>Пользователь</th><th>Право</th><th>Действия</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($user_permissions as $up): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(trim(($up['last_name'] ?? '') . ' ' . ($up['first_name'] ?? ''))) ?: htmlspecialchars($up['username'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($up['permission']); ?></td>
                        <td>
                            <form method="POST" action="/accounts/permissions/course/<?php echo $course['id']; ?>/user/remove/" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                                <input type="hidden" name="up_id" value="<?php echo $up['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Убрать доступ?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <h5 class="mt-4">Добавить пользователя</h5>
        <form method="POST" action="/accounts/permissions/course/<?php echo $course['id']; ?>/user/add/" class="row g-2">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <div class="col-md-7">
                <select name="user_id" class="form-select" required>
                    <option value="">Выберите пользователя</option>
                    <?php foreach ($all_users as $u): ?>
                    <?php if (!in_array($u['id'], $user_perm_user_ids)): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars(trim(($u['last_name'] ?? '') . ' ' . ($u['first_name'] ?? '')) ?: htmlspecialchars($u['username'] ?? '')); ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="permission" class="form-select">
                    <option value="view">Просмотр</option>
                    <option value="edit">Редактирование</option>
                    <option value="full_access">Полный доступ</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Добавить</button>
            </div>
        </form>
    </div>

    <div class="col-md-6">
        <h4>Группы с доступом</h4>
        <?php if (empty($group_permissions)): ?>
        <div class="alert alert-info">Нет прав групп.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead class="table-dark">
                    <tr><th>Группа</th><th>Право</th><th>Действия</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($group_permissions as $gp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($gp['name']); ?></td>
                        <td><?php echo htmlspecialchars($gp['permission']); ?></td>
                        <td>
                            <form method="POST" action="/accounts/permissions/course/<?php echo $course['id']; ?>/group/remove/" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                                <input type="hidden" name="gp_id" value="<?php echo $gp['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Убрать доступ?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <h5 class="mt-4">Добавить группу</h5>
        <form method="POST" action="/accounts/permissions/course/<?php echo $course['id']; ?>/group/add/" class="row g-2">
            <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
            <div class="col-md-7">
                <select name="group_id" class="form-select" required>
                    <option value="">Выберите группу</option>
                    <?php foreach ($all_groups as $g): ?>
                    <?php if (!in_array($g['id'], $group_perm_group_ids)): ?>
                    <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="permission" class="form-select">
                    <option value="view">Просмотр</option>
                    <option value="edit">Редактирование</option>
                    <option value="full_access">Полный доступ</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Добавить</button>
            </div>
        </form>
    </div>
</div>

<a href="/accounts/" class="btn btn-secondary mt-4">← Назад в админку</a>
