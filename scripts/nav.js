document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('nav');
    const toggle = document.getElementById('nav-toggle');
    if (!toggle) 
        return;

    toggle.addEventListener('click', () => {
        nav.classList.toggle('open');
    });
});

