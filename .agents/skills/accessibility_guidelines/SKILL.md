---
name: accessibility_guidelines
description: Guide for making the HR Budget application accessible to all users, following WCAG standards and best practices.
---

# Accessibility (a11y) Guidelines

Ensure the HR Budget application is usable by everyone, including users with disabilities.

## 📑 Table of Contents

- [WCAG Overview](#-wcag-overview)
- [Semantic HTML](#-semantic-html)
- [ARIA Attributes](#-aria-attributes)
- [Color & Contrast](#-color--contrast)
- [Keyboard Navigation](#-keyboard-navigation)
- [Forms & Inputs](#-forms--inputs)
- [Screen Reader Support](#-screen-reader-support)
- [Testing Tools](#-testing-tools)

## 📋 WCAG Overview

Follow **WCAG 2.1 Level AA** as the minimum standard.

### Core Principles (POUR)

| Principle | Description | Examples |
|:----------|:------------|:---------|
| **Perceivable** | Content can be perceived | Alt text, captions, contrast |
| **Operable** | UI is navigable | Keyboard access, focus visible |
| **Understandable** | Content is clear | Labels, error messages |
| **Robust** | Works with assistive tech | Valid HTML, ARIA |

## 🏗️ Semantic HTML

Use proper HTML elements instead of `<div>` for everything.

### ✅ Correct Usage

```html
<!-- Navigation -->
<nav aria-label="Main navigation">
    <ul>
        <li><a href="/dashboard">Dashboard</a></li>
        <li><a href="/budgets">Budgets</a></li>
    </ul>
</nav>

<!-- Main Content -->
<main id="main-content">
    <h1>Budget Overview</h1>
    <article>
        <h2>Section Title</h2>
        <p>Content here...</p>
    </article>
</main>

<!-- Buttons vs Links -->
<button type="button" onclick="openModal()">Open Modal</button>
<a href="/budgets/create">Create New Budget</a>
```

### ❌ Avoid

```html
<!-- Don't use divs for interactive elements -->
<div onclick="submit()">Submit</div>

<!-- Don't skip heading levels -->
<h1>Title</h1>
<h4>Directly to H4?</h4>
```

## 🎭 ARIA Attributes

Use ARIA sparingly—prefer native HTML semantics first.

### Common ARIA Patterns

```html
<!-- Modal Dialog -->
<div role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <h2 id="modal-title">Create Budget</h2>
    <button aria-label="Close modal">&times;</button>
</div>

<!-- Loading State -->
<button aria-busy="true" aria-live="polite">
    <span class="spinner"></span> Loading...
</button>

<!-- Expandable Section -->
<button aria-expanded="false" aria-controls="details-section">
    View Details
</button>
<div id="details-section" hidden>...</div>

<!-- Icons with Meaning -->
<button>
    <i data-lucide="trash-2" aria-hidden="true"></i>
    <span class="sr-only">Delete Budget</span>
</button>

<!-- Decorative Icons -->
<i data-lucide="calendar" aria-hidden="true"></i>

<!-- Data Tables -->
<table role="table">
    <caption class="sr-only">Budget Summary for FY 2567</caption>
    <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Amount</th>
        </tr>
    </thead>
</table>
```

### Screen Reader Only Class

```css
/* Add to app.css */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
```

## 🎨 Color & Contrast

### Minimum Contrast Ratios (WCAG AA)

| Element | Ratio | Example |
|:--------|:-----:|:--------|
| **Normal Text** | 4.5:1 | `#4a5568` on `#ffffff` ✅ |
| **Large Text (18px+ bold)** | 3:1 | `#718096` on `#ffffff` ✅ |
| **UI Components** | 3:1 | Buttons, inputs, icons |

### Don't Rely on Color Alone

```html
<!-- ❌ Bad: Color only indicates error -->
<input style="border-color: red;">

<!-- ✅ Good: Color + Icon + Text -->
<input class="border-red-500" aria-invalid="true" aria-describedby="error-msg">
<p id="error-msg" class="text-red-600">
    <i data-lucide="alert-circle" aria-hidden="true"></i>
    กรุณากรอกข้อมูลให้ครบถ้วน
</p>
```

### Focus Visibility

```css
/* Ensure visible focus ring */
:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Don't remove focus outline! */
/* ❌ Bad: :focus { outline: none; } */
```

## ⌨️ Keyboard Navigation

### Required Keyboard Support

| Key | Action |
|:----|:-------|
| `Tab` | Move focus forward |
| `Shift+Tab` | Move focus backward |
| `Enter/Space` | Activate buttons/links |
| `Escape` | Close modals/dropdowns |
| `Arrow Keys` | Navigate menus/tabs |

### Focus Management

```javascript
// Open Modal: Focus first focusable element
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    
    const firstFocusable = modal.querySelector(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    firstFocusable?.focus();
}

// Close Modal: Return focus to trigger
function closeModal(modalId, triggerElement) {
    document.getElementById(modalId).classList.add('hidden');
    triggerElement?.focus();
}

// Trap focus inside modal
function trapFocus(modal) {
    const focusable = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    modal.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
}
```

## 📝 Forms & Inputs

### Always Use Labels

```html
<!-- ✅ Best: Explicit label -->
<label for="budget-name">ชื่องบประมาณ</label>
<input type="text" id="budget-name" name="name" required>

<!-- ✅ OK: Wrapped label -->
<label>
    ชื่องบประมาณ
    <input type="text" name="name" required>
</label>

<!-- ❌ Bad: No label -->
<input type="text" placeholder="Enter name">
```

### Error Messages

```html
<div class="form-group">
    <label for="amount">จำนวนเงิน <span class="text-red-500">*</span></label>
    <input 
        type="number" 
        id="amount" 
        aria-invalid="true"
        aria-describedby="amount-error amount-hint"
        class="border-red-500"
    >
    <p id="amount-hint" class="text-slate-500 text-sm">
        กรอกตัวเลขเท่านั้น
    </p>
    <p id="amount-error" class="text-red-600 text-sm" role="alert">
        กรุณากรอกจำนวนเงิน
    </p>
</div>
```

### Required Fields

```html
<label for="title">
    หัวข้อ <span aria-hidden="true" class="text-red-500">*</span>
    <span class="sr-only">(จำเป็น)</span>
</label>
<input type="text" id="title" required aria-required="true">
```

## 🔊 Screen Reader Support

### Live Regions for Dynamic Content

```html
<!-- Announce status changes -->
<div role="status" aria-live="polite" class="sr-only" id="status-message">
    <!-- JS updates this -->
</div>

<!-- Announce errors immediately -->
<div role="alert" aria-live="assertive" id="error-alert">
    <!-- Critical errors appear here -->
</div>
```

```javascript
// Announce message to screen readers
function announce(message, isError = false) {
    const region = document.getElementById(
        isError ? 'error-alert' : 'status-message'
    );
    region.textContent = message;
    
    // Clear after announcement
    setTimeout(() => region.textContent = '', 1000);
}

// Usage
announce('บันทึกสำเร็จ');
announce('เกิดข้อผิดพลาด กรุณาลองใหม่', true);
```

### Skip Links

```html
<!-- At top of page -->
<a href="#main-content" class="sr-only focus:not-sr-only">
    Skip to main content
</a>

<!-- Main content area -->
<main id="main-content" tabindex="-1">
    ...
</main>
```

## 🧪 Testing Tools

### Automated Testing

| Tool | Type | Installation |
|:-----|:-----|:-------------|
| **axe DevTools** | Browser Extension | Chrome/Firefox |
| **WAVE** | Browser Extension | Chrome/Firefox |
| **Lighthouse** | Built-in Chrome | DevTools > Lighthouse |
| **pa11y** | CLI | `npm install -g pa11y` |

### Manual Testing Checklist

- [ ] Navigate entire page using only keyboard
- [ ] Test with screen reader (NVDA on Windows, VoiceOver on Mac)
- [ ] Verify focus is visible at all times
- [ ] Check color contrast with WebAIM Contrast Checker
- [ ] Resize text to 200% and verify layout
- [ ] Disable images and check alt text coverage
- [ ] Test form error messages are announced

### Quick Keyboard Test

```bash
# Press Tab through entire page and verify:
# 1. All interactive elements receive focus
# 2. Focus order is logical (left-to-right, top-to-bottom)
# 3. Focus is always visible
# 4. No keyboard traps (can always Tab away)
# 5. Modals trap focus inside until closed
```
