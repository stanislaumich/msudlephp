
            </main>
        </div>
    </div>
    <?php \Core\Flash::render(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) {
                document.querySelectorAll('form[method="POST"]').forEach(function(form) {
                    if (!form.querySelector('input[name="csrf_token"]')) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'csrf_token';
                        input.value = token;
                        form.appendChild(input);
                    }
                });
            }

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        });
    </script>
</body>
</html>
