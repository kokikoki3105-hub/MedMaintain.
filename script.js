function toggleTheme() {
    document.body.classList.toggle('light-theme');
    const icon = document.getElementById('themeIcon');

    if(document.body.classList.contains('light-theme')){
        icon.textContent = '🌞';
    } else {
        icon.textContent = '🌙';
    }
}
