const btnTheme = document.querySelector('.btnTheme');

btnTheme.addEventListener('click', () => {
    let value = document.body.classList.toggle('dark-theme');
    localStorage.setItem('dark-mode', value);        
});

if (localStorage.getItem('dark-mode') === 'true') {
    document.body.classList.add('dark-theme');
    btnTheme.classList.add('active');
} else {
    document.body.classList.remove('dark-theme');
    btnTheme.classList.remove('active');
}