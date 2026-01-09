/**
 * Sidebar Module
 * 
 * Handles sidebar toggle and mobile responsiveness
 */

const Sidebar = {
    isOpen: true,
    sidebar: null,
    overlay: null,
    mainContent: null,
    toggleBtn: null,
    
    init() {
        this.sidebar = document.getElementById('sidebar');
        this.overlay = document.getElementById('mobile-overlay');
        this.mainContent = document.getElementById('main-content');
        this.toggleBtn = document.getElementById('sidebar-toggle');
        
        if (!this.sidebar) return;
        
        // Load saved state
        const savedState = localStorage.getItem('sidebarOpen');
        this.isOpen = savedState !== 'false';
        
        // Apply initial state
        this.updateUI();
        
        // Bind events
        this.bindEvents();
    },
    
    bindEvents() {
        // Toggle button
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
        }
        
        // Mobile overlay
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth < 1024 && this.isOpen) {
                this.close();
            }
        });
        
        // Handle ESC key on mobile
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && window.innerWidth < 1024 && this.isOpen) {
                this.close();
            }
        });
    },
    
    toggle() {
        this.isOpen = !this.isOpen;
        this.updateUI();
        localStorage.setItem('sidebarOpen', this.isOpen);
    },
    
    open() {
        this.isOpen = true;
        this.updateUI();
    },
    
    close() {
        this.isOpen = false;
        this.updateUI();
    },
    
    updateUI() {
        if (!this.sidebar) return;
        
        const isMobile = window.innerWidth < 1024;
        
        if (this.isOpen) {
            // Open state
            this.sidebar.classList.remove('w-0', 'lg:w-20', '-translate-x-full');
            this.sidebar.classList.add('w-64');
            
            if (this.mainContent) {
                this.mainContent.classList.remove('ml-0', 'lg:ml-20');
                this.mainContent.classList.add('ml-0', 'lg:ml-64');
            }
            
            // Show overlay on mobile
            if (isMobile && this.overlay) {
                this.overlay.classList.remove('hidden');
            }
            
            // Show text in nav links
            this.sidebar.querySelectorAll('.nav-text').forEach(el => {
                el.classList.remove('lg:hidden', 'hidden');
            });
        } else {
            // Closed/collapsed state
            this.sidebar.classList.remove('w-64');
            this.sidebar.classList.add('w-0', 'lg:w-20');
            
            if (isMobile) {
                this.sidebar.classList.add('-translate-x-full');
            } else {
                this.sidebar.classList.remove('-translate-x-full');
            }
            
            if (this.mainContent) {
                this.mainContent.classList.remove('lg:ml-64');
                this.mainContent.classList.add('ml-0', 'lg:ml-20');
            }
            
            // Hide overlay
            if (this.overlay) {
                this.overlay.classList.add('hidden');
            }
            
            // Hide text in nav links on desktop collapsed
            if (!isMobile) {
                this.sidebar.querySelectorAll('.nav-text').forEach(el => {
                    el.classList.add('lg:hidden');
                });
            }
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    Sidebar.init();
});

// Export to window
window.Sidebar = Sidebar;

export default Sidebar;
