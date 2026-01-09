/**
 * HR Budget System - Main JavaScript
 */

import './modules/toast.js';
import './modules/charts.js';
import './modules/sidebar.js';

// Global App State
window.HRBudget = {
    formatCurrency: (value) => {
        return new Intl.NumberFormat('th-TH', { 
            style: 'currency', 
            currency: 'THB' 
        }).format(value);
    },
    
    formatNumber: (value, decimals = 0) => {
        return new Intl.NumberFormat('th-TH', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(value);
    },
    
    formatPercent: (value) => {
        return new Intl.NumberFormat('th-TH', {
            style: 'percent',
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }).format(value / 100);
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('HR Budget System initialized');
    
    // Show any flash messages as toast
    showFlashMessages();
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize form validation
    initFormValidation();
});

/**
 * Show flash messages from session
 */
function showFlashMessages() {
    const flashSuccess = document.getElementById('flash-success');
    const flashError = document.getElementById('flash-error');
    
    if (flashSuccess && flashSuccess.dataset.message) {
        window.Toast.success(flashSuccess.dataset.message);
    }
    
    if (flashError && flashError.dataset.message) {
        window.Toast.error(flashError.dataset.message);
    }
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    // Tooltip implementation for collapsed sidebar
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', (e) => {
            const text = e.target.dataset.tooltip;
            if (!text) return;
            
            const tooltip = document.createElement('div');
            tooltip.className = 'fixed z-50 bg-slate-800 text-white text-xs px-2 py-1 rounded pointer-events-none opacity-0 transition-opacity';
            tooltip.textContent = text;
            tooltip.id = 'active-tooltip';
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.left = `${rect.right + 8}px`;
            tooltip.style.top = `${rect.top + (rect.height / 2) - (tooltip.offsetHeight / 2)}px`;
            
            requestAnimationFrame(() => {
                tooltip.classList.add('opacity-100');
            });
        });
        
        el.addEventListener('mouseleave', () => {
            const tooltip = document.getElementById('active-tooltip');
            if (tooltip) tooltip.remove();
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            
            // Clear previous errors
            form.querySelectorAll('.error-message').forEach(el => el.remove());
            form.querySelectorAll('.input-error').forEach(el => {
                el.classList.remove('input-error', 'border-red-500');
            });
            
            // Validate required fields
            form.querySelectorAll('[required]').forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    showFieldError(input, 'กรุณากรอกข้อมูล');
                }
            });
            
            // Validate email fields
            form.querySelectorAll('[type="email"]').forEach(input => {
                if (input.value && !isValidEmail(input.value)) {
                    isValid = false;
                    showFieldError(input, 'รูปแบบอีเมลไม่ถูกต้อง');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Show field error
 */
function showFieldError(input, message) {
    input.classList.add('input-error', 'border-red-500');
    
    const error = document.createElement('p');
    error.className = 'error-message text-red-400 text-xs mt-1';
    error.textContent = message;
    
    input.parentNode.appendChild(error);
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Confirm delete action
 */
window.confirmDelete = async function(message = 'คุณต้องการลบรายการนี้หรือไม่?') {
    const result = await Swal.fire({
        title: 'ยืนยันการลบ',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#334155',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
        background: '#1e293b',
        color: '#f1f5f9'
    });
    
    return result.isConfirmed;
};

/**
 * Confirm action
 */
window.confirmAction = async function(title, message) {
    const result = await Swal.fire({
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0284c7',
        cancelButtonColor: '#334155',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        background: '#1e293b',
        color: '#f1f5f9'
    });
    
    return result.isConfirmed;
};
