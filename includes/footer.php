        </main>
    </div>

    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const icon = themeToggle ? themeToggle.querySelector('i') : null;
        const span = themeToggle ? themeToggle.querySelector('span') : null;

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const isDark = body.getAttribute('data-theme') === 'dark';
                const nextTheme = isDark ? 'light' : 'dark';
                
                body.setAttribute('data-theme', nextTheme);
                icon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
                if (span) span.innerText = isDark ? 'Dark Mode' : 'Light Mode';
                localStorage.setItem('theme', nextTheme);
            });

            // Persist Theme
            const savedTheme = localStorage.getItem('theme') || 'dark';
            body.setAttribute('data-theme', savedTheme);
            if (savedTheme === 'dark') {
                if (icon) icon.className = 'fas fa-sun';
                if (span) span.innerText = 'Light Mode';
            } else {
                if (icon) icon.className = 'fas fa-moon';
                if (span) span.innerText = 'Dark Mode';
            }
        }
        
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.mobile-sidebar-overlay');
            const body = document.body;
            
            if (sidebar && overlay) {
                const isActive = sidebar.classList.contains('active');
                
                if (isActive) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                } else {
                    sidebar.classList.add('active');
                    overlay.classList.add('active');
                    body.classList.add('sidebar-open');
                }
            }
        }
        
        // Close mobile menu when clicking overlay
        const overlay = document.querySelector('.mobile-sidebar-overlay');
        if (overlay) {
            overlay.addEventListener('click', function() {
                toggleMobileMenu();
            });
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.mobile-sidebar-overlay');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (sidebar && overlay && sidebar.classList.contains('active')) {
                // Don't close if clicking inside sidebar or toggle button
                if (!sidebar.contains(event.target) && toggle && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });
        
        // Mobile TOC Toggle
        function toggleMobileTOC() {
            const toc = document.querySelector('.toc-sidebar');
            const overlay = document.querySelector('.mobile-toc-overlay');
            const body = document.body;
            
            if (toc && overlay) {
                const isActive = toc.classList.contains('active');
                if (isActive) {
                    toc.classList.remove('active');
                    overlay.classList.remove('active');
                    body.classList.remove('toc-open');
                } else {
                    toc.classList.add('active');
                    overlay.classList.add('active');
                    body.classList.add('toc-open');
                }
            }
        }

        // Close mobile menu on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleMobileMenu();
                }
                const toc = document.querySelector('.toc-sidebar');
                if (toc && toc.classList.contains('active')) {
                    toggleMobileTOC();
                }
            }
        });

        // Toast Notification System
        function showToast(message, type = 'success') {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div class="toast-icon"><i class="fas fa-check"></i></div>
                <div class="toast-message">${message}</div>
            `;
            container.appendChild(toast);

            // Trigger animation
            setTimeout(() => toast.classList.add('active'), 10);

            // Auto-hide
            setTimeout(() => {
                toast.classList.remove('active');
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }

        // Copy to Clipboard
        function copyToClipboard(text) {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            showToast('<?php echo t('link_copied', 'Link kopyalandı!'); ?>');
        }

        // Global Click Listener for Copyable Elements
        document.addEventListener('click', function(e) {
            // Handle Header Anchor clicks
            const headerAnchor = e.target.closest('.header-anchor');
            if (headerAnchor) {
                e.preventDefault();
                const url = window.location.origin + window.location.pathname + window.location.search + headerAnchor.getAttribute('href');
                copyToClipboard(url);
                
                // Still scroll to section
                const id = headerAnchor.getAttribute('href').substring(1);
                const target = document.getElementById(id);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    window.history.pushState(null, null, '#' + id);
                }
                return;
            }

            // Handle TOC Title clicks
            const tocTitle = e.target.closest('.toc-title');
            if (tocTitle) {
                copyToClipboard(window.location.href);
            }
        });
        // Google Translate Integration
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'tr', 
                autoDisplay: false
            }, 'google_translate_element');
            
            // Highlight active button on load
            updateLangButtons();
        }

        function setLang(langCode) {
            const combo = document.querySelector('.goog-te-combo');
            if (combo) {
                combo.value = langCode;
                combo.dispatchEvent(new Event('change'));
            }
            
            if (langCode === 'tr') {
                const domains = [window.location.hostname, "." + window.location.hostname, ""];
                const cookies = ["googtrans"];
                cookies.forEach(c => {
                    domains.forEach(d => {
                        document.cookie = c + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + d;
                        document.cookie = c + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    });
                });
                window.location.reload(); 
            }
            
            setTimeout(updateLangButtons, 500);
        }

        function updateLangButtons() {
            const combo = document.querySelector('.goog-te-combo');
            const currentLang = combo ? combo.value : 'tr';
            document.querySelectorAll('.lang-btn').forEach(btn => {
                if (btn.innerText.trim().toLowerCase() === currentLang.toLowerCase()) {
                    btn.style.color = 'var(--primary)';
                    btn.style.borderColor = 'var(--primary)';
                    btn.style.background = 'rgba(var(--primary-rgb), 0.1)';
                } else {
                    btn.style.color = 'var(--text-muted)';
                    btn.style.borderColor = 'var(--border)';
                    btn.style.background = 'var(--bg-base)';
                }
            });
        }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <div id="google_translate_element" style="display:none !important;"></div>
</body>
</html>
