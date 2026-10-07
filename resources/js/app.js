import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-mobile-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (toggle && menu) toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!open)); menu.hidden = open;
    });

    document.querySelectorAll('[data-customizer]').forEach((root) => {
        const fileInput = root.querySelector('[data-image-input]');
        const form = root.querySelector('[data-cart-form]') ?? document.querySelector('[data-cart-form]');
        const stage = root.querySelector('[data-design-stage]');
        const image = root.querySelector('[data-design-image]');
        const area = root.querySelector('[data-print-area]');
        const uploadId = root.querySelector('[data-upload-id]') ?? form?.querySelector('[data-upload-id]');
        const dataInput = root.querySelector('[data-customization-data]') ?? form?.querySelector('[data-customization-data]');
        const status = root.querySelector('[data-upload-status]');
        const scale = root.querySelector('[data-scale]');
        let design = { x: 50, y: 50, width: 45, height: 45, rotation: 0 };
        let drag = null;

        const render = () => {
            image.style.left = `${design.x}%`; image.style.top = `${design.y}%`;
            image.style.width = `${design.width}%`; image.style.height = `${design.height}%`;
            image.style.transform = `translate(-50%, -50%) rotate(${design.rotation}deg)`;
            dataInput.value = JSON.stringify(design);
        };
        const upload = async (file) => {
            const body = new FormData(); body.append('image', file);
            status.textContent = 'در حال آپلود تصویر...';
            try {
                const response = await fetch(root.dataset.uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': root.dataset.csrf }, body });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'آپلود تصویر انجام نشد.');
                uploadId.value = result.id; image.src = result.preview_url; image.classList.remove('hidden');
                root.querySelector('[data-print-area-wrap]')?.classList.remove('hidden'); status.textContent = 'تصویر آماده و قابل تنظیم است.'; render();
            } catch (error) { status.textContent = error.message; }
        };
        fileInput?.addEventListener('change', () => fileInput.files[0] && upload(fileInput.files[0]));
        scale?.addEventListener('input', () => { design.width = Number(scale.value); design.height = Number(scale.value); render(); });
        stage?.addEventListener('pointerdown', (event) => {
            if (event.target !== image || image.classList.contains('hidden')) return;
            image.setPointerCapture(event.pointerId); drag = { x: event.clientX, y: event.clientY, startX: design.x, startY: design.y };
        });
        stage?.addEventListener('pointermove', (event) => {
            if (!drag) return; const box = stage.getBoundingClientRect();
            design.x = Math.max(0, Math.min(100, drag.startX + ((event.clientX - drag.x) / box.width) * 100));
            design.y = Math.max(0, Math.min(100, drag.startY + ((event.clientY - drag.y) / box.height) * 100)); render();
        });
        stage?.addEventListener('pointerup', () => { drag = null; });
        form?.addEventListener('submit', (event) => { if (!uploadId.value) { event.preventDefault(); status.textContent = 'ابتدا تصویر خود را آپلود کنید.'; } });
        render();
    });
});
