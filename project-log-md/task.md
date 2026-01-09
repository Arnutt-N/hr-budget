# HR Budget System - Task Checklist

> **Last Updated**: 2025-12-17 08:05:00

## Phase 1: Foundation ✅
- [x] Project structure setup (Vite + Tailwind 4)
- [x] Database migrations (new tables)
- [x] Basic authentication (Email moj.go.th)
- [x] User model & session management
- [x] Layout & navigation (dark theme)

## Phase 2: Budget Management & Dimensions ✅
- [x] Budget CRUD & Dashboard (Legacy)
- [x] **New**: Dimensional Database Schema (`dim_org`, `dim_structure`, `fact_execution`)
- [x] **New**: Budget Execution Dashboard (Dimensional View)
- [x] Charts & KPI Cards
- [x] Excel Export & Filters

## Phase 3: Budget Requests ✅
- [x] Request CRUD (Header + Items)
- [x] Approval Workflow (Status: Draft -> Pending -> Approved)
- [x] **Refactor**: Link to `dim_organization`
- [x] **New**: "Sync on Save" Logic (Map Category Tree -> Flat Dimension)
- [x] UI Updates: Org Selector, Auto-save endpoints

## Phase 3.5: File Management 🔨
- [x] **Database Setup**
    - [x] Create `folders` table
    - [x] Create `files` table
    - [x] Create `file_attachments` table (Removed)
- [x] **Models & Controller**
    - [x] Folder Model (Tree structure)
    - [x] File Model (Upload/Download)
    - [x] FileController
- [x] **UI**
    - [x] File Manager View
    - [x] Folder Tree Navigation
    - [x] Upload/Download/Delete Functions
- [x] **Navigation**
    - [x] Add "จัดการไฟล์" to Sidebar

## Phase 3.9: Dashboard Consolidation ✅
- [x] **Refactor**
    - [x] Merge `BudgetExecution` features into Main Dashboard
    - [x] Update `BudgetExecution` Model (Add filters)
    - [x] Add Organization Charts & Filters
    - [x] Implement Excel Export
    - [x] Redirect `/budgets` to `/budgets/list`
    - [x] Remove duplicate menu items

## Phase 4: Personnel Management 🔨
- [ ] **Database Setup**
    - [ ] Create `personnel_types` table
    - [ ] Create `personnel` table
    - [ ] Create `personnel_salary` table
- [ ] **Models & Logic**
    - [ ] Personnel Model
    - [ ] Salary Calculation Service
- [ ] **UI Implementation**
    - [ ] Personnel List View
    - [ ] Add/Edit Personnel Form
    - [ ] Salary Import (Excel)
    - [ ] Personnel Dashboard

## Phase 5: Reports (Planned)
- [ ] Disbursement report page
- [ ] Request report page
- [ ] PDF/Excel export implementation

## Phase 6: Admin & Settings (Planned)
- [ ] User Management
- [ ] Role Management
- [ ] System Logs
