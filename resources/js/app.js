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
        const rotation = root.querySelector('[data-rotation]');
        const gallery = root.querySelector('[data-gallery]');
        const cropToggle = root.querySelector('[data-crop-toggle]');
        const cropOverlay = root.querySelector('[data-crop-overlay]');
        const cropRect = root.querySelector('[data-crop-rect]');
        const cropApply = root.querySelector('[data-crop-apply]');
        const cropCancel = root.querySelector('[data-crop-cancel]');
        const cropActions = root.querySelector('[data-crop-actions]');
        let design = { x: 50, y: 50, width: 45, height: 45, rotation: 0 };
        let drag = null;
        let cropping = false;
        let cropStart = null;
        let cropArea = null;

        const printAreaBounds = () => {
            if (!stage || !area) return { minX: 0, maxX: 100, minY: 0, maxY: 100 };
            const stageBox = stage.getBoundingClientRect();
            const areaBox = area.getBoundingClientRect();
            return {
                minX: ((areaBox.left - stageBox.left) / stageBox.width) * 100,
                maxX: ((areaBox.right - stageBox.left) / stageBox.width) * 100,
                minY: ((areaBox.top - stageBox.top) / stageBox.height) * 100,
                maxY: ((areaBox.bottom - stageBox.top) / stageBox.height) * 100,
            };
        };
        const clampToPrintArea = () => {
            const fit = (value, min, max, half) => {
                const low = min + half; const high = max - half;
                if (low > high) return (min + max) / 2;
                return Math.min(high, Math.max(low, value));
            };
            const box = printAreaBounds();
            design.x = fit(design.x, box.minX, box.maxX, design.width / 2);
            design.y = fit(design.y, box.minY, box.maxY, design.height / 2);
        };
        const render = () => {
            clampToPrintArea();
            image.style.left = `${design.x}%`; image.style.top = `${design.y}%`;
            image.style.width = `${design.width}%`; image.style.height = `${design.height}%`;
            image.style.transform = `translate(-50%, -50%) rotate(${design.rotation}deg)`;
            dataInput.value = JSON.stringify(design);
        };
        const resetDesign = () => {
            design = { x: 50, y: 50, width: 45, height: 45, rotation: 0 };
            if (scale) scale.value = '45';
            if (rotation) rotation.value = '0';
        };
        const exitCrop = () => {
            cropping = false; cropStart = null; cropArea = null;
            cropOverlay?.classList.add('hidden');
            cropRect?.classList.add('hidden');
            cropActions?.classList.add('hidden');
        };
        const upload = async (file) => {
            const body = new FormData(); body.append('image', file);
            status.textContent = 'در حال آپلود تصویر...';
            try {
                const response = await fetch(root.dataset.uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': root.dataset.csrf, 'Accept': 'application/json' }, body });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'آپلود تصویر انجام نشد.');
                uploadId.value = result.id; image.src = result.preview_url; image.classList.remove('hidden');
                root.querySelector('[data-print-area-wrap]')?.classList.remove('hidden'); status.textContent = 'تصویر آماده و قابل تنظیم است.'; render();
            } catch (error) { status.textContent = error.message; }
        };
        fileInput?.addEventListener('change', () => { exitCrop(); fileInput.files[0] && upload(fileInput.files[0]); });
        scale?.addEventListener('input', () => { design.width = Number(scale.value); design.height = Number(scale.value); render(); });
        rotation?.addEventListener('input', () => { design.rotation = Number(rotation.value); render(); });
        gallery?.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const main = root.querySelector('[data-product-image]');
                if (!main) return;
                main.src = thumb.dataset.galleryThumb;
                gallery.querySelectorAll('[data-gallery-thumb]').forEach((item) => {
                    const active = item === thumb;
                    item.classList.toggle('border-violet-600', active);
                    item.classList.toggle('border-transparent', !active);
                    item.classList.toggle('opacity-80', !active);
                });
            });
        });
        cropToggle?.addEventListener('click', () => {
            if (image.classList.contains('hidden')) return;
            cropping = true;
            design.rotation = 0; render();
            cropOverlay.style.left = `${design.x - design.width / 2}%`;
            cropOverlay.style.top = `${design.y - design.height / 2}%`;
            cropOverlay.style.width = `${design.width}%`;
            cropOverlay.style.height = `${design.height}%`;
            cropOverlay.classList.remove('hidden');
            cropRect.classList.add('hidden');
            cropActions.classList.add('hidden');
            status.textContent = 'ناحیه دلخواه طرح را با ماوس یا انگشت انتخاب کنید.';
        });
        cropOverlay?.addEventListener('pointerdown', (event) => {
            if (!cropping) return;
            try { cropOverlay.setPointerCapture(event.pointerId); } catch { /* synthetic events have no active pointer */ }
            const box = cropOverlay.getBoundingClientRect();
            cropStart = { x: Math.min(1, Math.max(0, (event.clientX - box.left) / box.width)), y: Math.min(1, Math.max(0, (event.clientY - box.top) / box.height)) };
            cropArea = null;
            cropRect.classList.remove('hidden');
            cropActions.classList.add('hidden');
        });
        cropOverlay?.addEventListener('pointermove', (event) => {
            if (!cropping || !cropStart) return;
            const box = cropOverlay.getBoundingClientRect();
            const current = { x: Math.min(1, Math.max(0, (event.clientX - box.left) / box.width)), y: Math.min(1, Math.max(0, (event.clientY - box.top) / box.height)) };
            cropArea = { x0: Math.min(cropStart.x, current.x), y0: Math.min(cropStart.y, current.y), x1: Math.max(cropStart.x, current.x), y1: Math.max(cropStart.y, current.y) };
            cropRect.style.left = `${cropArea.x0 * 100}%`;
            cropRect.style.top = `${cropArea.y0 * 100}%`;
            cropRect.style.width = `${(cropArea.x1 - cropArea.x0) * 100}%`;
            cropRect.style.height = `${(cropArea.y1 - cropArea.y0) * 100}%`;
        });
        cropOverlay?.addEventListener('pointerup', () => {
            if (!cropping || !cropStart) return;
            cropStart = null;
            const bigEnough = cropArea && (cropArea.x1 - cropArea.x0) > 0.05 && (cropArea.y1 - cropArea.y0) > 0.05;
            if (!bigEnough) { cropArea = null; cropRect.classList.add('hidden'); }
            cropActions.classList.toggle('hidden', !bigEnough);
        });
        cropCancel?.addEventListener('click', exitCrop);
        cropApply?.addEventListener('click', async () => {
            if (!cropping || !cropArea || !image.naturalWidth) return;
            const sx = Math.round(cropArea.x0 * image.naturalWidth);
            const sy = Math.round(cropArea.y0 * image.naturalHeight);
            const sw = Math.max(1, Math.round((cropArea.x1 - cropArea.x0) * image.naturalWidth));
            const sh = Math.max(1, Math.round((cropArea.y1 - cropArea.y0) * image.naturalHeight));
            if (sw < 300 || sh < 300) {
                status.textContent = 'ناحیه انتخابی کوچک است؛ حداقل ۳۰۰ در ۳۰۰ پیکسل لازم است.';
                return;
            }
            const canvas = document.createElement('canvas');
            canvas.width = sw; canvas.height = sh;
            canvas.getContext('2d').drawImage(image, sx, sy, sw, sh, 0, 0, sw, sh);
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
            exitCrop();
            resetDesign();
            render();
            await upload(new File([blob], 'cropped-design.png', { type: 'image/png' }));
        });
        stage?.addEventListener('pointerdown', (event) => {
            if (event.target !== image || image.classList.contains('hidden')) return;
            try { image.setPointerCapture(event.pointerId); } catch { /* synthetic events have no active pointer */ }
            drag = { x: event.clientX, y: event.clientY, startX: design.x, startY: design.y };
        });
        stage?.addEventListener('pointermove', (event) => {
            if (!drag) return; const box = stage.getBoundingClientRect();
            design.x = drag.startX + ((event.clientX - drag.x) / box.width) * 100;
            design.y = drag.startY + ((event.clientY - drag.y) / box.height) * 100; render();
        });
        stage?.addEventListener('pointerup', () => { drag = null; });
        form?.addEventListener('submit', (event) => { if (!uploadId.value) { event.preventDefault(); status.textContent = 'ابتدا تصویر خود را آپلود کنید.'; } });
        render();
    });
});
