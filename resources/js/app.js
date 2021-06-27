const btnTheme = document.querySelector('.btnTheme');

btnTheme.addEventListener('click', () => {
    document.body.classList.toggle('dark-theme');
    btnTheme.classList.toggle('active');

    /* Saved the mode in localStorage */
    if (document.body.classList.contains('dark-theme')) {
        localStorage.setItem('dark-mode', 'true');
    } else {
        localStorage.setItem('dark-mode', 'false');
    }
});

/* Get the mode in localStorage */
if (localStorage.getItem('dark-mode') === 'true') {
    document.body.classList.add('dark-theme');
    btnTheme.classList.add('active');
} else {
    document.body.classList.remove('dark-theme');
    btnTheme.classList.remove('active');
}