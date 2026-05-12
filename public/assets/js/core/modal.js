/**
 * Modal System (CADA Custom Modal)
 * Ligero, sin dependencias, diseñado para mantener la UI consistente.
 */
const CadaModal = {
    init() {
        if (document.getElementById('cada-modal')) return;
        
        const html = `
            <div id="cada-modal" class="cada-modal-overlay">
                <div class="cada-modal-box">
                    <div class="cada-modal-icon" id="cada-modal-icon"></div>
                    <h3 id="cada-modal-title"></h3>
                    <p id="cada-modal-text"></p>
                    <div class="cada-modal-actions" id="cada-modal-actions">
                        <button class="btn btn-ghost" id="cada-modal-btn-cancel">Cancelar</button>
                        <button class="btn btn-primary" id="cada-modal-btn-confirm">Confirmar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', html);
    },

    /**
     * Muestra una alerta (success, error, info)
     * @param {Object} options {title, text, type, confirmText}
     */
    alert(options) {
        this.init();
        return new Promise((resolve) => {
            const overlay = document.getElementById('cada-modal');
            const box = overlay.querySelector('.cada-modal-box');
            const titleEl = document.getElementById('cada-modal-title');
            const textEl = document.getElementById('cada-modal-text');
            const iconEl = document.getElementById('cada-modal-icon');
            const actionsEl = document.getElementById('cada-modal-actions');
            const btnConfirm = document.getElementById('cada-modal-btn-confirm');
            const btnCancel = document.getElementById('cada-modal-btn-cancel');

            // Reset and configure
            btnCancel.style.display = 'none'; // No cancel in simple alert
            btnConfirm.style.width = '100%';
            
            titleEl.textContent = options.title || 'Información';
            textEl.textContent = options.text || '';
            
            const type = options.type || 'info';
            box.className = 'cada-modal-box ' + type;
            
            if (type === 'success') {
                iconEl.innerHTML = '<i class="ph ph-check-circle" style="color: var(--color-success); font-size: 64px;"></i>';
                btnConfirm.style.backgroundColor = 'var(--color-success)';
                btnConfirm.style.borderColor = 'var(--color-success)';
            } else if (type === 'error' || type === 'danger') {
                iconEl.innerHTML = '<i class="ph ph-x-circle" style="color: var(--color-danger); font-size: 64px;"></i>';
                btnConfirm.style.backgroundColor = 'var(--color-danger)';
                btnConfirm.style.borderColor = 'var(--color-danger)';
            } else if (type === 'warning') {
                iconEl.innerHTML = '<i class="ph ph-warning" style="color: var(--color-warning); font-size: 64px;"></i>';
                btnConfirm.style.backgroundColor = 'var(--color-warning)';
                btnConfirm.style.borderColor = 'var(--color-warning)';
            } else {
                iconEl.innerHTML = '<i class="ph ph-info" style="color: var(--color-info); font-size: 64px;"></i>';
                btnConfirm.style.backgroundColor = 'var(--color-primary)';
                btnConfirm.style.borderColor = 'var(--color-primary)';
            }

            btnConfirm.textContent = options.confirmText || 'Entendido';

            const handleClose = () => {
                this.close();
                resolve(true);
            };

            const newBtnConfirm = btnConfirm.cloneNode(true);
            btnConfirm.parentNode.replaceChild(newBtnConfirm, btnConfirm);
            newBtnConfirm.addEventListener('click', handleClose);

            overlay.classList.add('show');
            setTimeout(() => box.classList.add('show'), 10);
        });
    },

    confirm(options) {
        this.init();
        return new Promise((resolve) => {
            const overlay = document.getElementById('cada-modal');
            const box = overlay.querySelector('.cada-modal-box');
            const titleEl = document.getElementById('cada-modal-title');
            const textEl = document.getElementById('cada-modal-text');
            const iconEl = document.getElementById('cada-modal-icon');
            const btnConfirm = document.getElementById('cada-modal-btn-confirm');
            const btnCancel = document.getElementById('cada-modal-btn-cancel');

            btnCancel.style.display = 'inline-flex';
            btnConfirm.style.width = 'auto';
            
            titleEl.textContent = options.title || '¿Estás seguro?';
            textEl.textContent = options.text || '';
            
            const type = options.type || 'warning';
            box.className = 'cada-modal-box ' + type;
            
            if (type === 'danger') {
                iconEl.innerHTML = '<i class="ph ph-warning-circle" style="color: var(--color-danger); font-size: 56px;"></i>';
                btnConfirm.style.backgroundColor = 'var(--color-danger)';
                btnConfirm.style.borderColor = 'var(--color-danger)';
            } else {
                iconEl.innerHTML = '<i class="ph ph-question" style="color: var(--color-warning); font-size: 56px;"></i>';
                btnConfirm.style.backgroundColor = 'var(--color-warning)';
                btnConfirm.style.borderColor = 'var(--color-warning)';
            }

            btnConfirm.textContent = options.confirmText || 'Confirmar';
            btnCancel.textContent = options.cancelText || 'Cancelar';

            const handleConfirm = () => { this.close(); resolve(true); };
            const handleCancel = () => { this.close(); resolve(false); };

            const newBtnConfirm = btnConfirm.cloneNode(true);
            btnConfirm.parentNode.replaceChild(newBtnConfirm, btnConfirm);
            newBtnConfirm.addEventListener('click', handleConfirm);
            
            const newBtnCancel = btnCancel.cloneNode(true);
            btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);
            newBtnCancel.addEventListener('click', handleCancel);

            overlay.classList.add('show');
            setTimeout(() => box.classList.add('show'), 10);
        });
    },
    
    close() {
        const overlay = document.getElementById('cada-modal');
        const box = overlay.querySelector('.cada-modal-box');
        if (box) box.classList.remove('show');
        setTimeout(() => overlay.classList.remove('show'), 200);
    }
};

document.addEventListener('DOMContentLoaded', () => CadaModal.init());
