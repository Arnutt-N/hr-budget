---
name: ui_design_system
description: Guide for UI Design System and reusable component library in the HR Budget project.
---

# UI Design System Guide

Standards for consistent and reusable UI components.

## 📑 Table of Contents

- [Design Tokens](#-design-tokens)
- [Color Palette](#-color-palette)
- [Typography](#-typography)
- [Components](#-components)

## 🎨 Design Tokens

### CSS Variables

```css
:root {
    /* Colors */
    --color-primary: #3B82F6;
    --color-primary-dark: #1D4ED8;
    --color-secondary: #64748B;
    --color-success: #22C55E;
    --color-warning: #F59E0B;
    --color-danger: #EF4444;
    
    /* Backgrounds */
    --bg-primary: #0F172A;
    --bg-secondary: #1E293B;
    --bg-card: #334155;
    
    /* Text */
    --text-primary: #F8FAFC;
    --text-secondary: #94A3B8;
    --text-muted: #64748B;
    
    /* Spacing */
    --space-1: 0.25rem;
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
    
    /* Border Radius */
    --radius-sm: 0.25rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
    
    /* Shadows */
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.3);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.3);
}
```

## 🎨 Color Palette

| Name | Hex | Usage |
|:-----|:----|:------|
| Primary | `#3B82F6` | Buttons, links, active states |
| Success | `#22C55E` | Approved, success messages |
| Warning | `#F59E0B` | Pending, warnings |
| Danger | `#EF4444` | Errors, delete actions |
| Secondary | `#64748B` | Secondary text, borders |

## 📝 Typography

```css
/* Font Family */
body {
    font-family: 'Sarabun', 'Inter', sans-serif;
}

/* Heading Sizes */
.h1 { font-size: 2rem; font-weight: 700; }
.h2 { font-size: 1.5rem; font-weight: 600; }
.h3 { font-size: 1.25rem; font-weight: 600; }
.h4 { font-size: 1rem; font-weight: 600; }

/* Body Sizes */
.text-sm { font-size: 0.875rem; }
.text-base { font-size: 1rem; }
.text-lg { font-size: 1.125rem; }
```

## 🧩 Components

### Button Variants

```css
.btn {
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-dark);
    transform: translateY(-1px);
}

.btn-success { background: var(--color-success); color: white; }
.btn-danger { background: var(--color-danger); color: white; }
.btn-outline {
    background: transparent;
    border: 1px solid var(--color-primary);
    color: var(--color-primary);
}
```

### Card Component

```css
.card {
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    padding: var(--space-6);
    box-shadow: var(--shadow-md);
}

.card-header {
    border-bottom: 1px solid var(--bg-card);
    padding-bottom: var(--space-4);
    margin-bottom: var(--space-4);
}
```

### Form Elements

```css
.form-input {
    width: 100%;
    padding: var(--space-3);
    background: var(--bg-card);
    border: 1px solid var(--bg-card);
    border-radius: var(--radius-md);
    color: var(--text-primary);
}

.form-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.form-label {
    display: block;
    margin-bottom: var(--space-2);
    color: var(--text-secondary);
    font-size: 0.875rem;
}
```

### Modal Component

```html
<div class="modal-overlay" x-show="open">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modal Title</h3>
            <button @click="open = false">&times;</button>
        </div>
        <div class="modal-body">Content here</div>
        <div class="modal-footer">
            <button class="btn-outline">Cancel</button>
            <button class="btn-primary">Confirm</button>
        </div>
    </div>
</div>
```

### Status Badges

```css
.badge {
    display: inline-flex;
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success { background: #22c55e20; color: #22C55E; }
.badge-warning { background: #f59e0b20; color: #F59E0B; }
.badge-danger { background: #ef444420; color: #EF4444; }
.badge-info { background: #3b82f620; color: #3B82F6; }
```
