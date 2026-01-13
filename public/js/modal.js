/**
 * Custom Modal Component
 * Pure Tailwind CSS + Vanilla JS
 * Matches project theme (Dark Slate)
 */
const Modal = {
  element: null,
  options: {},
  
  init() {
    this.element = document.getElementById('customModal');
    if (!this.element) return;
    this.bindEvents();
  },
  
  /**
   * Show Modal
   * @param {Object} options 
   * @param {string} options.title - Modal Title
   * @param {string} options.message - Optional description
   * @param {string} options.total - Amount to display
   * @param {string} options.variant - 'confirm' | 'warning' | 'danger'
   * @param {Function} options.onConfirm - Callback on confirm
   * @param {Function} options.onCancel - Callback on cancel
   */
  show(options = {}) {
    if (!this.element) return;
    
    // Default options
    this.options = {
      title: 'ยืนยันรายการ?',
      message: 'กรุณาตรวจสอบความถูกต้อง',
      total: '0.00',
      variant: 'confirm',
      buttonText: 'ยืนยัน',
      ...options
    };
    
    this.renderContent();
    this.applyVariant();
    
    // Show with animation
    this.element.classList.remove('hidden');
    // Force reflow
    this.element.offsetWidth; 
    this.element.classList.add('show');
    
    // Lock body scroll
    document.body.style.overflow = 'hidden';
  },
  
  hide() {
    if (!this.element) return;
    
    this.element.classList.remove('show');
    this.element.classList.add('hide');
    
    setTimeout(() => {
      this.element.classList.add('hidden');
      this.element.classList.remove('hide');
      document.body.style.overflow = '';
      
      // Cleanup callbacks to prevent zombie closures
      this.options.onConfirm = null;
      this.options.onCancel = null;
    }, 200);
  },
  
  renderContent() {
    // Title & Message
    this.element.querySelector('#modal-title').textContent = this.options.title;
    this.element.querySelector('#modal-message').textContent = this.options.message;
    
    // Total Amount
    const totalEl = this.element.querySelector('#modal-total');
    if (totalEl) totalEl.textContent = this.options.total;
    
    // Button Text
    const confirmBtn = this.element.querySelector('.modal-confirm-btn');
    if (confirmBtn) confirmBtn.textContent = this.options.buttonText || 'ยืนยัน';
  },
  
  applyVariant() {
    const iconContainer = this.element.querySelector('#modal-icon-container');
    const icon = this.element.querySelector('#modal-icon');
    const confirmBtn = this.element.querySelector('.modal-confirm-btn');
    
    // Reset classes
    iconContainer.className = 'w-12 h-12 rounded-full flex items-center justify-center';
    confirmBtn.className = 'modal-confirm-btn flex-1 px-4 py-2.5 rounded-lg font-medium transition-all shadow-lg';
    
    // Configure based on variant
    switch(this.options.variant) {
      case 'warning':
        iconContainer.classList.add('bg-amber-500/10');
        iconContainer.innerHTML = `<i data-lucide="alert-triangle" class="w-6 h-6 text-amber-400"></i>`;
        confirmBtn.classList.add('bg-amber-600', 'hover:bg-amber-500', 'text-white', 'shadow-amber-900/30');
        break;
        
      case 'danger':
        iconContainer.classList.add('bg-red-500/10');
        iconContainer.innerHTML = `<i data-lucide="x-circle" class="w-6 h-6 text-red-500"></i>`;
        confirmBtn.classList.add('bg-red-600', 'hover:bg-red-500', 'text-white', 'shadow-red-900/30');
        break;
        
      case 'confirm':
      default:
        iconContainer.classList.add('bg-blue-500/10'); // primary-500
        iconContainer.innerHTML = `<i data-lucide="check-circle" class="w-6 h-6 text-blue-400"></i>`;
        confirmBtn.classList.add('bg-blue-600', 'hover:bg-blue-500', 'text-white', 'shadow-blue-900/30');
        break;
    }
    
    // Re-init icon
    if (window.lucide) lucide.createIcons();
  },
  
  bindEvents() {
    // Close / Cancel
    this.element.querySelectorAll('.modal-close, .modal-cancel-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        this.hide();
        if (this.options.onCancel) this.options.onCancel();
      });
    });
    
    // Confirm
    this.element.querySelector('.modal-confirm-btn')?.addEventListener('click', () => {
      if (this.options.onConfirm) {
        this.options.onConfirm();
      }
      this.hide();
    });
    
    // Backdrop Click
    this.element.querySelector('.modal-backdrop')?.addEventListener('click', (e) => {
      if (e.target.classList.contains('modal-backdrop')) {
        this.hide();
        if (this.options.onCancel) this.options.onCancel();
      }
    });
    
    // Keyboard ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !this.element.classList.contains('hidden')) {
        this.hide();
        if (this.options.onCancel) this.options.onCancel();
      }
    });
  }
};

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
  Modal.init();
});
