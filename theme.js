        function openModal() { document.getElementById('themeModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('themeModal').style.display = 'none'; }
        function setTheme(theme) {
            localStorage.setItem('profilePageTheme', theme);
            document.body.classList.remove('light-theme', 'dark-theme');
            document.body.classList.add(theme + '-theme');
        }
        window.onload = function () {
            let savedTheme = localStorage.getItem('profilePageTheme') || 'dark';
            setTheme(savedTheme);
            document.querySelector(`input[value="${savedTheme}"]`).checked = true;
        };