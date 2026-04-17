---
name: frontend_assistant
description: Guide for standardizing frontend development using Vanilla JS, Tailwind CSS, and PHP View Templates.
---

# Frontend Assistant

Guide for creating consistent and high-quality UI/UX in the HR Budget project.

## 📑 Table of Contents
- [Tech Stack](#-tech-stack)
- [Vite Build System](#-vite-build-system)
- [Tailwind Configuration](#-tailwind-configuration)
- [CSS & Tailwind](#-css--tailwind)
- [JavaScript Patterns](#-javascript-patterns)
- [View Components](#-view-components)
- [Icons (Lucide)](#-icons-lucide)
- [Common UI Elements](#-common-ui-elements)

## 🎨 Tech Stack

| Technology | Usage | Key Files |
|:-----------|:------|:----------|
| **HTML/PHP** | View Templates | `resources/views/*.php` |
| **CSS** | Tailwind Utility Classes | `resources/css/app.css` |
| **JavaScript** | Logic & Interactivity | `public/js/*.js` |
| **Icons** | Lucide Icons | `public/assets/js/lucide.min.js` |
| **Build** | Vite 5.x | `vite.config.js` |

## ⚡ Vite Build System

### Configuration Overview

Vite handles bundling and development server with Hot Module Replacement (HMR).

**Config File:** `vite.config.js`
```javascript
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [tailwindcss()],
    
    build: {
        outDir: 'public/assets',      // Output directory
        emptyOutDir: true,            // Clean before build
        manifest: true,               // Generate manifest.json
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                style: 'resources/css/app.css'
            }
        }
    },
    
    server: {
        origin: 'http://localhost:5173',
        port: 5173,
        cors: true
    }
});
```

### Development Commands
```bash
# Start dev server (HMR enabled)
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Hot Module Replacement (HMR)
- ✅ CSS changes reload instantly
- ✅ JS changes update without full page refresh
- ✅ Dev server: `http://localhost:5173`

### Production Build Output
```
public/assets/
├── css/
│   └── style-[hash].css
├── js/
│   └── app-[hash].js
└── .vite/
    └── manifest.json
```

### Loading Assets in PHP
```php
<?php
// Helper function to get Vite asset URL
function vite_asset(string $entry): string {
    $manifest = json_decode(
        file_get_contents(__DIR__ . '/../public/assets/.vite/manifest.json'),
        true
    );
    return '/assets/' . $manifest[$entry]['file'];
}
?>

<!-- In layout -->
<link rel="stylesheet" href="<?= vite_asset('resources/css/app.css') ?>">
<script src="<?= vite_asset('resources/js/app.js') ?>" defer></script>
```

## 🎨 Tailwind Configuration

### Setup (Tailwind v4 with Vite Plugin)
```bash
npm install tailwindcss @tailwindcss/vite
```

### Customization (`tailwind.config.js`)
```javascript
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.php',
        './resources/js/**/*.js'
    ],
    theme: {
        extend: {
            colors: {
                'brand': {
                    50: '#eff6ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8'
                }
            },
            fontFamily: {
                'thai': ['Sarabun', 'sans-serif']
            }
        }
    },
    plugins: []
}
```

### Base Styles (`resources/css/app.css`)
```css
@import 'tailwindcss';

/* Custom base styles */
@layer base {
    body {
        @apply font-thai text-slate-800 bg-slate-50;
    }
    
    h1, h2, h3 {
        @apply font-semibold text-slate-900;
    }
}

/* Custom components */
@layer components {
    .btn-primary {
        @apply px-4 py-2 bg-blue-600 text-white rounded-lg
               hover:bg-blue-700 transition-colors;
    }
    
    .btn-secondary {
        @apply px-4 py-2 bg-slate-100 text-slate-700 rounded-lg
               hover:bg-slate-200 transition-colors;
    }
    
    .input-field {
        @apply w-full rounded-lg border-slate-300
               focus:border-blue-500 focus:ring-1 focus:ring-blue-500;
    }
    
    .card {
        @apply bg-white rounded-lg shadow-sm border border-slate-200 p-6;
    }
}
```

### Using Custom Classes
```html
<!-- Use custom component classes -->
<button class="btn-primary">Save</button>
<button class="btn-secondary">Cancel</button>
<input type="text" class="input-field" />
<div class="card">Content here</div>

<!-- Or use utility classes directly -->
<button class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg">
    Brand Button
</button>
```

## 💅 CSS & Tailwind

### Core Styles (`resources/css/app.css`)
- **Font**: Sarabun (Thai standard) imported via Google Fonts.
- **Colors**: Slate (Backgrounds), Blue/Indigo (Primary actions), Red (Destructive).

### Common Utility Patterns

**Card Container:**
```html
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <!-- content -->
</div>
```

**Primary Button:**
```html
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
    Submit
</button>
```

**Data Table:**
```html
<table class="w-full text-sm text-left">
    <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
            <th class="px-4 py-3 font-medium text-slate-700">Column</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-200">
        <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">Data</td>
        </tr>
    </tbody>
</table>
```

## ⚡ JavaScript Patterns

### 1. Modular Functions
Keep logic separated in `public/js/`. Avoid inline scripts in PHP files if possible.

```javascript
// public/js/my-module.js
const MyModule = {
    init() {
        this.bindEvents();
    },
    bindEvents() {
        document.querySelector('#btn-save').addEventListener('click', this.saveData);
    },
    saveData() {
        // Logic
    }
};

document.addEventListener('DOMContentLoaded', () => MyModule.init());
```

### 2. Fetch API (AJAX)
Use `fetch` for backend communication. Always handle CSRF.

```javascript
const formData = new FormData();
formData.append('name', 'New P1');
formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

fetch('/api/projects', {
    method: 'POST',
    body: formData
})
.then(res => res.json())
.then(data => {
    if (data.status === 'success') {
        window.location.reload();
    } else {
        alert(data.message);
    }
});
```

## 🧩 View Components

### 1. Layouts
All views should use a layout. 

**Main Layout (`resources/views/layouts/main.php`):**
```php
<?php 
// Header & Sidebar included here 
?>
<main class="flex-1 p-6">
    <?= $content ?>
</main>
<?php 
// Footer & Scripts included here 
?>
```

### 2. Partial Views
Use for reusable components (e.g., headers, navbars).

```php
<?php require_once 'resources/views/partials/header.php'; ?>
```

### 3. Escaping Output
**ALWAYS** escape data before printing to prevent XSS.

```php
<!-- ❌ Unsafe -->
<p><?= $user['name'] ?></p>

<!-- ✅ Safe -->
<p><?= htmlspecialchars($user['name']) ?></p>
<!-- OR using helper -->
<p><?= View::escape($user['name']) ?></p>
```

## 🎭 Icons (Lucide)

Use `data-lucide` attribute. Icons render automatically on page load.

```html
<i data-lucide="user" class="w-5 h-5 text-slate-500"></i>
<i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
```

**Re-initialize icons after AJAX content load:**
```javascript
lucide.createIcons();
```

## 🧱 Common UI Elements

### Modal (Standard)

```html
<!-- Backdrop -->
<div id="modal-backdrop" class="fixed inset-0 bg-slate-900/50 hidden z-40"></div>

<!-- Modal -->
<div id="my-modal" class="fixed inset-0 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-semibold text-lg">Title</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6">
            Content...
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-2 rounded-b-lg">
            <button class="btn-secondary">Cancel</button>
            <button class="btn-primary">Save</button>
        </div>
    </div>
</div>
```

### Form Input

```html
<div class="mb-4">
    <label class="block text-sm font-medium text-slate-700 mb-1">
        Label <span class="text-red-500">*</span>
    </label>
    <input type="text" name="field_name" 
           class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
           required>
    <p class="mt-1 text-sm text-slate-500">Helper text.</p>
</div>
```
