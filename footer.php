        </main>
    </div>

    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const icon = themeToggle ? themeToggle.querySelector('i') : null;

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const isDark = body.getAttribute('data-theme') === 'dark';
                body.setAttribute('data-theme', isDark ? 'light' : 'dark');
                icon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
                themeToggle.innerHTML = isDark ? '<i class="fas fa-moon"></i> Dark Mode' : '<i class="fas fa-sun"></i> Light Mode';
                localStorage.setItem('theme', isDark ? 'light' : 'dark');
            });

            // Persist Theme
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                body.setAttribute('data-theme', savedTheme);
                if (savedTheme === 'dark') {
                    icon.className = 'fas fa-sun';
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
                }
            }
        }
    </script>
</body>
</html>
