    </div> <!-- main-content -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm delete actions
        document.querySelectorAll('a[data-confirm]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                if (!confirm(this.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>

