/**
 * Toast Notification Module
 */

const Toast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                document.body.appendChild(this.container);
            }
        }
    },
    
    show(message, type = 'info', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        let icon = '<i class="ph ph-info text-lg"></i>';
        if (type === 'success') icon = '<i class="ph ph-check-circle text-lg"></i>';
        if (type === 'error') icon = '<i class="ph ph-warning text-lg"></i>';
        
        toast.innerHTML = `
            ${icon}
            <span class="text-sm font-medium">${message}</span>
        `;
        
        this.container.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Auto dismiss
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    success(message, duration) {
        this.show(message, 'success', duration);
    },
    
    error(message, duration) {
        this.show(message, 'error', duration);
    },
    
    info(message, duration) {
        this.show(message, 'info', duration);
    }
};

// Export to window
window.Toast = Toast;

export default Toast;
