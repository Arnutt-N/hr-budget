<?php
/**
 * Smart Budget Tracking (Multi-Tab) - Main View
 */
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-5">
        <h2 class="mb-0 text-primary"><i class="bi bi-calculator me-2"></i>Smart Budget Tracking</h2>
        <p class="text-muted mb-0">บันทึกและติดตามการใช้งบประมาณรายหมวดหมู่</p>
    </div>
    <div class="col-md-7">
        <form id="filterForm" class="row g-2 justify-content-end">
            <div class="col-md-4">
                <select class="form-select" id="organization_id" name="organization_id">
                    <option value="">-- ภาพรวม (ทุกหน่วยงาน) --</option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['name_th']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="fiscal_year" name="fiscal_year">
                    <?php foreach ($fiscalYears as $y): ?>
                        <option value="<?= $y['value'] ?>" <?= ($y['value'] == $fiscalYear) ? 'selected' : '' ?>>ปีงบ <?= $y['value'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="budgetTabs" role="tablist">
    <?php foreach ($tabs as $index => $tab): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($index === 0) ? 'active' : '' ?>" 
                    id="tab-<?= $tab['id'] ?>" 
                    data-bs-toggle="tab" 
                    data-bs-target="#content-<?= $tab['id'] ?>" 
                    type="button" 
                    role="tab" 
                    data-cat-id="<?= $tab['id'] ?>"
                    aria-controls="content-<?= $tab['id'] ?>" 
                    aria-selected="<?= ($index === 0) ? 'true' : 'false' ?>">
                <?= htmlspecialchars($tab['name_th'] ?? $tab['name']) ?>
            </button>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="budgetTabsContent">
    <?php foreach ($tabs as $index => $tab): ?>
        <div class="tab-pane fade <?= ($index === 0) ? 'show active' : '' ?>" 
             id="content-<?= $tab['id'] ?>" 
             role="tabpanel" 
             aria-labelledby="tab-<?= $tab['id'] ?>">
            
            <div class="text-center py-5 loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
            </div>
            <div class="tab-data-container"></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Sticky Footer for Actions -->
<div class="fixed-bottom bg-white border-top shadow-lg py-3" style="z-index: 1030;">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted small" id="statusMessage">พร้อมทำงาน</span>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-secondary me-2" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช
                </button>
                <button type="button" class="btn btn-success" id="saveButton" onclick="saveCurrentTab()">
                    <i class="bi bi-save me-1"></i> บันทึกข้อมูล (Current Tab)
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Spacer for fixed bottom -->
<div style="height: 80px;"></div>

<script>
const BASE_URL = '<?= \App\Core\View::url('') ?>';

document.addEventListener('DOMContentLoaded', function() {
    // Initial Load for Active Tab
    const activeTab = document.querySelector('.nav-link.active');
    if (activeTab) {
        loadTabContent(activeTab);
    }

    // Handle Tab Changes
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            const target = event.target; // newly activated tab
            loadTabContent(target);
        });
    });

    // Handle Filters
    document.getElementById('fiscal_year').addEventListener('change', reloadAllTabs);
    document.getElementById('organization_id').addEventListener('change', reloadAllTabs);
});

function reloadAllTabs() {
    // Determine active tab and reload it first
    // Mark others as needing reload (clear content)
    const activeTab = document.querySelector('.nav-link.active');
    
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.querySelector('.tab-data-container').innerHTML = '';
        pane.querySelector('.loading-spinner').style.display = 'block';
        pane.removeAttribute('data-loaded');
    });
    
    // Changing year might require page reload to update URL query param or just update state?
    // Controller uses $_GET['year']. So simple reload page is easiest for year.
    // BUT organization is just a filter.
    // Let's implement full reload for Year since it affects tabs structure potentially (though unlikely).
    // For consistency, let's just reload content via AJAX.
    
    if (activeTab) {
        loadTabContent(activeTab);
    }
}

function loadTabContent(tabElement) {
    const catId = tabElement.getAttribute('data-cat-id');
    const targetId = tabElement.getAttribute('data-bs-target');
    const pane = document.querySelector(targetId);
    
    if (pane.hasAttribute('data-loaded') && pane.getAttribute('data-loaded') === 'true') {
        return; // Already loaded
    }
    
    const year = document.getElementById('fiscal_year').value;
    const orgId = document.getElementById('organization_id').value;
    
    const container = pane.querySelector('.tab-data-container');
    const spinner = pane.querySelector('.loading-spinner');
    
    spinner.style.display = 'block';
    
    fetch(`${BASE_URL}/budgets/tracking/tab?type_id=${catId}&year=${year}&org_id=${orgId}`)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            spinner.style.display = 'none';
            pane.setAttribute('data-loaded', 'true');
            // Re-bind inputs if necessary (e.g. formatters)
            bindInputEvents(container);
        })
        .catch(error => {
            console.error('Error loading tab:', error);
            container.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
            spinner.style.display = 'none';
        });
}

function bindInputEvents(container) {
    // Add auto-calculation logic here
    const inputs = container.querySelectorAll('.budget-input');
    inputs.forEach(input => {
        input.addEventListener('input', calculateRow);
    });
}

function calculateRow(e) {
    // Logic to calculate remaining = allocated + transfer - disbursed - pending - po
    const row = e.target.closest('tr');
    if (!row) return;

    const getVal = (cls) => {
        const el = row.querySelector('.' + cls);
        return el ? parseFloat(el.value.replace(/,/g, '') || 0) : 0;
    };

    const allocated = getVal('inp-allocated');
    const transfer = getVal('inp-transfer'); // pending transfer? or transfer in/out? Logic says "Transfer" in model
    const disbursed = getVal('inp-disbursed');
    const pending = getVal('inp-pending');
    const po = getVal('inp-po');

    const remaining = (allocated + transfer) - (disbursed + pending + po);
    
    const remainingEl = row.querySelector('.val-remaining');
    if (remainingEl) {
        remainingEl.textContent = remaining.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (remaining < 0) remainingEl.classList.add('text-danger');
        else remainingEl.classList.remove('text-danger');
    }
}

function saveCurrentTab() {
    const activePane = document.querySelector('.tab-pane.active');
    if (!activePane) return;
    
    const inputs = activePane.querySelectorAll('.budget-input');
    const data = {};
    
    inputs.forEach(input => {
        const itemId = input.getAttribute('data-item-id');
        const field = input.getAttribute('data-field'); // allocated, disbursed, etc.
        const value = input.value.replace(/,/g, '');
        
        if (!data[itemId]) data[itemId] = {};
        data[itemId][field] = value;
    });
    
    if (Object.keys(data).length === 0) {
        alert('ไม่มีข้อมูลให้บันทึก');
        return;
    }
    
    const year = document.getElementById('fiscal_year').value;
    const orgId = document.getElementById('organization_id').value;
    
    const statusMsg = document.getElementById('statusMessage');
    statusMsg.textContent = 'กำลังบันทึก...';
    
    fetch(`${BASE_URL}/budgets/tracking/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            fiscalYear: year,
            orgId: orgId,
            items: data
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            statusMsg.textContent = 'บันทึกสำเร็จ ' + new Date().toLocaleTimeString();
            statusMsg.classList.add('text-success');
            setTimeout(() => statusMsg.classList.remove('text-success'), 3000);
        } else {
            alert('Error: ' + res.message);
            statusMsg.textContent = 'เกิดข้อผิดพลาด';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Connection Error');
        statusMsg.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
    });
}
</script>

<style>
.table-input {
    min-width: 100px;
    text-align: right; 
    border: 1px solid transparent;
    background: transparent;
}
.table-input:hover, .table-input:focus {
    border-color: #dee2e6;
    background: #fff;
}
.loading-spinner {
    min-height: 200px;
}
</style>
