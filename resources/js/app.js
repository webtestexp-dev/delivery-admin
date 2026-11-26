import 'bootstrap';

const sidebar = document.querySelector('[data-sidebar]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');
const desktopLayout = window.matchMedia('(min-width: 992px)');

if (sidebar && sidebarToggle && sidebarClose && sidebarBackdrop) {
    const setSidebarOpen = (isOpen) => {
        const isDesktop = desktopLayout.matches;
        const shouldShow = isDesktop || isOpen;

        sidebar.classList.toggle('is-open', shouldShow);
        sidebarBackdrop.hidden = isDesktop || !isOpen;
        sidebarToggle.setAttribute('aria-expanded', String(!isDesktop && isOpen));
        sidebar.setAttribute('aria-hidden', String(!shouldShow));
        sidebar.inert = !shouldShow;
        document.body.classList.toggle('sidebar-open', !isDesktop && isOpen);
    };

    sidebarToggle.addEventListener('click', () => {
        setSidebarOpen(!sidebar.classList.contains('is-open'));
    });

    sidebarClose.addEventListener('click', () => {
        setSidebarOpen(false);
    });

    sidebarBackdrop.addEventListener('click', () => {
        setSidebarOpen(false);
    });

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (!desktopLayout.matches) {
                setSidebarOpen(false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !desktopLayout.matches) {
            setSidebarOpen(false);
        }
    });

    desktopLayout.addEventListener('change', () => {
        setSidebarOpen(false);
    });

    setSidebarOpen(false);
}
