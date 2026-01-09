/**
 * Thai Buddhist Era Date Input (Manual Only)
 * Replaces Datepicker with simple text input + validation
 */
const ThaiDatepicker = {
    init: function() {
        const elements = document.querySelectorAll('.thai-datepicker');
        elements.forEach(el => {
            if (el.dataset.tdpInitialized) return;
            this.setupElement(el);
        });
        console.log('ThaiDatepicker (Manual) initialized');
    },

    setupElement: function(el) {
        // 1. Setup Attributes
        const originalName = el.getAttribute('name');
        const initialValue = el.value; // Expected: YYYY-MM-DD
        
        // Change type to text to prevent browser picker
        el.type = 'text';
        el.removeAttribute('name'); // Don't submit the display value directly
        el.placeholder = 'วว/ดด/ปปปป';
        el.setAttribute('autocomplete', 'off');
        el.setAttribute('maxlength', '10');
        
        // Visual Adjustment: Fit content width (~10 chars + padding)
        el.style.width = '140px'; // Fixed width sufficient for DD/MM/YYYY
        el.style.textAlign = 'center'; // Center align looks better for dates

        // 2. Create Hidden Input for ISO Value (AD)
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = originalName;
        hiddenInput.value = initialValue;
        el.parentNode.insertBefore(hiddenInput, el.nextSibling);

        // 3. Set Initial Display Value (Convert AD -> BE)
        if (initialValue && /^\d{4}-\d{2}-\d{2}$/.test(initialValue)) {
            el.value = this.toThaiDate(initialValue);
        }

        // 4. Event Listeners
        
        // Input Masking: Allow only numbers and slash
        el.addEventListener('input', (e) => {
            let val = e.target.value;
            e.target.value = val.replace(/[^0-9\/]/g, '');
        });

        // Validation on Blur
        el.addEventListener('blur', (e) => {
            this.handleValidation(e.target, hiddenInput);
        });

        // Optional: Auto-add slashes (Simple logic)
        el.addEventListener('keyup', (e) => {
            if (e.key === 'Backspace') return;
            let val = e.target.value;
            if (val.length === 2 || val.length === 5) {
                e.target.value = val + '/';
            }
        });

        el.dataset.tdpInitialized = 'true';
    },

    handleValidation: function(el, hiddenInput) {
        const val = el.value.trim();
        
        // Empty is allowed if not required (handle 'required' attribute if needed)
        if (!val) {
            hiddenInput.value = '';
            el.style.borderColor = ''; // Reset
            return;
        }

        const valid = this.parseAndValidate(val);
        
        if (valid) {
            // Valid Date
            el.style.borderColor = '#10b981'; // Green
            el.value = valid.formatted; // Normalize display
            hiddenInput.value = valid.iso; // YYYY-MM-DD (AD)
        } else {
            // Invalid Date
            el.style.borderColor = '#ef4444'; // Red
            // Optional: User feedback
            // alert('กรุณากรอกวันที่ให้ถูกต้อง รูปแบบ วว/ดด/ปปปป (พ.ศ.)');
            hiddenInput.value = ''; // Clean invalid data
        }
    },

    // Convert YYYY-MM-DD -> DD/MM/YYYY (BE)
    toThaiDate: function(isoDate) {
        const [y, m, d] = isoDate.split('-');
        const beYear = parseInt(y, 10) + 543;
        return `${d}/${m}/${beYear}`;
    },

    parseAndValidate: function(dateStr) {
        // Supported formats: DD/MM/YYYY or DDMMYYYY
        let d, m, y;
        
        const clean = dateStr.replace(/\//g, '');
        if (clean.length === 8) {
            d = parseInt(clean.substring(0, 2), 10);
            m = parseInt(clean.substring(2, 4), 10);
            y = parseInt(clean.substring(4, 8), 10);
        } else {
            return null;
        }

        // Basic Check
        if (isNaN(d) || isNaN(m) || isNaN(y)) return null;
        if (m < 1 || m > 12) return null;
        if (d < 1 || d > 31) return null;

        // Check days in month
        const daysInMonth = new Date(y, m, 0).getDate(); // rough check using JS Date
        if (d > daysInMonth) return null;

        // Year Logic: Strict Buddhist Era (BE) Validation
        // User explicitly requested validation for 'Por Sor' (B.E.) only.
        // Reasonable range for this system: 2400 - 2800 (BE)
        // (2400 BE = 1857 AD, 2800 BE = 2257 AD)
        
        if (y < 2400 || y > 2800) {
            // Reject A.D. years (e.g. 2025) or unrealistic B.E. years
            return null;
        }

        const beYear = y;
        const adYear = beYear - 543;

        // Validate resulting AD year range just in case
        if (adYear < 1900 || adYear > 2300) return null;

        const pad = (n) => String(n).padStart(2, '0');
        
        return {
            formatted: `${pad(d)}/${pad(m)}/${beYear}`,
            iso: `${adYear}-${pad(m)}-${pad(d)}`
        };
    }
};
