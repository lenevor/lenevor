const btnTheme = document.querySelector('#btnTheme');

btnTheme.addEventListener('click', () => {
    document.body.classList.toggle('light-theme');
    btnTheme.classList.toggle('active');

    /* Saved the mode in localStorage */
    if (document.body.classList.contains('light-theme')) {
        localStorage.setItem('light-mode', 'true');
    } else {
        localStorage.setItem('light-mode', 'false');
    }
});

/* Get the mode in localStorage */
if (localStorage.getItem('light-mode') === 'true') {
    document.body.classList.add('light-theme');
    btnTheme.classList.add('active');
} else {
    document.body.classList.remove('light-theme');
    btnTheme.classList.remove('active');
}