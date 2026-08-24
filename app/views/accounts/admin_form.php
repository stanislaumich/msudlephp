
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?php echo $admin ? 'Редактирование' : 'Создание'; ?> администратора</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo \Core\Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Логин *</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>" <?php echo $admin ? 'disabled' : 'required'; ?>>
                        <?php if ($admin): ?><input type="hidden" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>"><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль <?php echo $admin ? '(оставьте пустым, чтобы не менять)' : '*'; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo $admin ? '' : 'required'; ?>>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Фамилия</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($admin['last_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($admin['first_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="/accounts/admins/" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
