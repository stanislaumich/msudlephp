<h2>Ответы студентов: <?php echo htmlspecialchars($course['full_name']); ?></h2>
<a href="/courses/<?php echo $course['id']; ?>/" class="btn btn-secondary mb-3">Назад к курсу</a>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr><th>Студент</th><th>Единица</th><th>Ответ</th><th>Статус</th><th>Балл</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($units ?? [] as $unit): ?>
                <?php foreach ($answer_map[$unit['id']] ?? [] as $answer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($answer['student_fio'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($unit['title']); ?></td>
                    <td>
                        <?php if ($answer['answer_text']): ?>
                            <?php echo nl2br(htmlspecialchars(mb_substr($answer['answer_text'], 0, 100))); ?>
                        <?php endif; ?>
                        <?php if ($answer['answer_file']): ?>
                            <a href="/<?php echo htmlspecialchars($answer['answer_file']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Файл</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($answer['checked']): ?>
                            <span class="badge bg-<?php echo $answer['passed'] ? 'success' : 'danger'; ?>">
                                <?php echo $answer['passed'] ? 'Зачтено' : 'Не зачтено'; ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Ожидает проверки</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $answer['score'] ?? '—'; ?></td>
                    <td>
                        <a href="/courses/<?php echo $course['id']; ?>/answers/<?php echo $answer['id']; ?>/check/" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-square"></i> Проверить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (empty($answer_map)): ?>
<div class="alert alert-info">Ответов пока нет.</div>
<?php endif; ?>
