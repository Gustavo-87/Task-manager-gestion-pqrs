const menuButton = document.querySelector('#menuButton');
const sidebar = document.querySelector('#sidebar');

menuButton?.addEventListener('click', () => {
    const isOpen = sidebar?.classList.toggle('open');
    menuButton.setAttribute('aria-expanded', String(Boolean(isOpen)));
});

document.addEventListener('click', (event) => {
    if (window.innerWidth > 760 || !sidebar?.classList.contains('open')) return;
    if (!sidebar.contains(event.target) && event.target !== menuButton) {
        sidebar.classList.remove('open');
        menuButton?.setAttribute('aria-expanded', 'false');
    }
});

const description = document.querySelector('#descripcion');
const counter = document.querySelector('#charCount');
const updateCounter = () => { if (description && counter) counter.textContent = `${description.value.length} caracteres`; };
description?.addEventListener('input', updateCounter);
updateCounter();

document.querySelectorAll('[data-password-toggle]').forEach((button) => button.addEventListener('click', (event) => {
    const password = document.querySelector(`#${button.dataset.passwordToggle || 'password'}`);
    if (!password) return;
    const visible = password.type === 'text';
    password.type = visible ? 'password' : 'text';
    event.currentTarget.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    event.currentTarget.setAttribute('aria-pressed', String(!visible));
}));

const fileInput = document.querySelector('#adjuntos');
const fileList = document.querySelector('#fileList');
fileInput?.addEventListener('change', () => {
    if (!fileList) return;
    fileList.innerHTML = '';
    [...fileInput.files].forEach((file) => {
        const item = document.createElement('span');
        item.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB`;
        fileList.appendChild(item);
    });
});

const colorPicker = document.querySelector('#color_principal_picker');
const colorText = document.querySelector('#color_principal');
colorPicker?.addEventListener('input', () => { colorText.value = colorPicker.value; });
colorText?.addEventListener('input', () => {
    if (/^#[0-9a-f]{6}$/i.test(colorText.value)) {
        colorPicker.value = colorText.value;
        document.body.style.setProperty('--forest', colorText.value);
    }
});
colorPicker?.addEventListener('input', () => document.body.style.setProperty('--forest', colorPicker.value));

const applyTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    document.querySelector('#themeToggle')?.setAttribute('aria-label', theme === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro');
};
applyTheme(localStorage.getItem('theme') || 'light');
document.querySelector('#themeToggle')?.addEventListener('click', () => {
    const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', theme);
    applyTheme(theme);
});

const setSidebar = (collapsed) => document.body.classList.toggle('sidebar-collapsed', collapsed);
setSidebar(localStorage.getItem('sidebar-collapsed') === 'true');
document.querySelector('#sidebarToggle')?.addEventListener('click', () => {
    const collapsed = !document.body.classList.contains('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', String(collapsed));
    setSidebar(collapsed);
});

document.querySelector('#logo')?.addEventListener('change', (event) => {
    const file = event.currentTarget.files?.[0];
    const preview = document.querySelector('#brandPreview img');
    if (file && preview) preview.src = URL.createObjectURL(file);
});

const confirmDialog = document.querySelector('#confirmDialog');
let pendingConfirmation = null;
const openConfirmation = (title, message, callback) => {
    if (!confirmDialog) return callback();
    document.querySelector('#confirmTitle').textContent = title;
    document.querySelector('#confirmMessage').textContent = message;
    pendingConfirmation = callback;
    confirmDialog.showModal();
};
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form.dataset.confirm || form.dataset.confirmed === 'true') return;
    event.preventDefault();
    openConfirmation(form.dataset.confirm, form.dataset.confirmMessage || '¿Deseas continuar?', () => {
        form.dataset.confirmed = 'true';
        form.requestSubmit();
    });
});
document.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-confirm]');
    if (!button) return;
    event.preventDefault();
    openConfirmation(button.dataset.confirm, button.dataset.confirmMessage || '¿Deseas continuar?', () => button.form?.requestSubmit(button));
});
document.querySelector('#confirmCancel')?.addEventListener('click', () => { pendingConfirmation = null; confirmDialog.close(); });
document.querySelector('#confirmAccept')?.addEventListener('click', () => { const callback = pendingConfirmation; pendingConfirmation = null; confirmDialog.close(); callback?.(); });
document.querySelector('#responseTemplate')?.addEventListener('change', (event) => {
    const option = event.currentTarget.selectedOptions[0];
    if (option?.dataset.body) document.querySelector('#body').value = option.dataset.body;
});
