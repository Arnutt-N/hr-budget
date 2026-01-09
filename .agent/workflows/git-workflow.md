---
description: การจัดการ Git Version Control แบบ Enterprise-Grade อย่างปลอดภัย
---

# 🔐 Git Version Control Workflow (Enterprise-Grade)

> [!IMPORTANT]
> ### 🤖 Guidelines for AI Agent (Antigravity)
> - **Turbo Mode**: ใช้ `// turbo` หรือ `// turbo-all` สำหรับคำสั่ง `git` พื้นฐานที่ปลอดภัย (เช่น `git status`, `git branch`, `git fetch`)
> - **Fallback Protocol**: หากคำสั่งอัตโนมัติเกิดข้อผิดพลาด (Error) หรือติดปัญหา Permission **ห้ามเดาสุ่ม** ให้แจ้งเตือน User ทันที พร้อมสรุปรายการคำสั่งทั้งหมดที่ต้องใช้เพื่อให้ User รันด้วยตัวเอง (Manual)
> - **Validation**: ทุกครั้งที่ทำขั้นตอน Staging หรือ Commit เสร็จ ต้องใช้ `git status` เพื่อยืนยันสถานะเสมอ

> [!CAUTION]
> **ห้ามใช้ `git add .` หากไม่แน่ใจในไฟล์ทั้งหมด** - อาจส่งไฟล์ที่เป็นความลับขึ้น repo โดยไม่ตั้งใจ

## 0. 🔍 Pre-Commit Checks (การตรวจสอบก่อนเริ่ม)
// turbo-all
```cmd
git branch              # ตรวจสอบ branch ปัจจุบัน
git fetch origin        # ดึงข้อมูลจาก remote
git pull origin main    # อัปเดต local ให้เป็นปัจจุบัน
git status              # ตรวจสอบสถานะไฟล์ที่เปลี่ยนแปลง
```

## 1. 🛡️ Security Check

> [!IMPORTANT]
> **ขั้นตอนนี้สำคัญมาก - ห้ามข้าม**

### 1.1 ตรวจสอบไฟล์ก่อน Commit
```cmd
git status
git diff
```

**ห้าม Commit ไฟล์เหล่านี้**:
- ❌ `.env` (environment variables, secrets)
- ❌ `config/database.php` (credentials)
- ❌ `*.log`, `node_modules/`, `vendor/`
- ❌ API keys, passwords, tokens
- ❌ Private keys (`.pem`, `.key`)
- ❌ Database dumps (`.sql`)

### 1.2 .gitignore Essential
```gitignore
# Environment & Secrets
.env
.env.*
!.env.example
config/database.php

# Dependencies
/vendor/
/node_modules/

# Logs & Cache
*.log
/storage/logs/
/bootstrap/cache/*

# Keys & Certificates
*.pem
*.key
*.crt
id_rsa*
id_ed25519*
```

**ตรวจสอบว่าไฟล์ถูก ignore**:
```cmd
git check-ignore -v .env
git status --ignored
```

**หยุด track ไฟล์ที่ถูก commit ไปแล้ว**:
```cmd
git rm --cached .env
echo .env >> .gitignore
git commit -m "chore: update .gitignore"
```

### 1.3 Pre-Commit Hook (Optional)
สร้างไฟล์ `.git/hooks/pre-commit`:
```bash
#!/bin/sh
FORBIDDEN=".env .env.local config/database.php"
for file in $FORBIDDEN; do
    if git diff --cached --name-only | grep -q "^$file$"; then
        echo "ERROR: Forbidden file: $file"
        exit 1
    fi
done
```

Windows: `code .git\hooks\pre-commit` และรัน `chmod +x .git/hooks/pre-commit` ใน Git Bash

## 2. 📦 Staging

> [!WARNING]
> **ระบุไฟล์ทีละตัว** แทนการใช้ `git add .`

```cmd
git add src/Controllers/UserController.php
git add resources/views/users/
git status  # ตรวจสอบ staged files
```

**ยกเลิก staging**:
```cmd
git restore --staged <filename>
```

## 3. 💬 Commit Standards

### 3.1 Conventional Commits (มาตรฐานการเขียนข้อความ)
ใช้รูปแบบ: `<type>(<scope>): <subject>`

**Types ที่แนะนำ**: 
- `feat`: เพิ่ม Feature ใหม่
- `fix`: แก้ไข Bug
- `refactor`: ปรับปรุงโครงสร้าง Code (ไม่เปลี่ยน Logic)
- `style`: ปรับแต่งความสวยงาม (CSS, UI)
- `docs`: แก้ไขเอกสาร
- `chore`: งานจิปาถะ (เช่น อัปเดต dependencies)

**ตัวอย่าง (Short Detail Recommend):**
- `style(requests): refine table alignment and eye icon color`
- `fix(auth): fix login session timeout issue`
- `feat(budget): add KPI tracking to activities list`

**คำสั่งที่ใช้:**
```cmd
git commit -m "style(requests): refine table alignment and icons"
```

### 3.2 Amendment
```cmd
# แก้ไข message
git commit --amend -m "updated message"

# เพิ่มไฟล์ที่ลืม
git add forgotten-file.php
git commit --amend --no-edit
```

> [!WARNING]
> **ห้าม amend commits ที่ push แล้ว**

## 4. 🚀 Pushing
```cmd
git log --oneline -5  # ตรวจสอบก่อน push
git push origin <branch-name>
```

**Force push (ระวัง)**:
```cmd
git push origin <branch-name> --force-with-lease
```

## 5. 🌿 Branch & Tag Management

**สร้าง branch**:
```cmd
git checkout -b feature/new-feature
```

**Branch naming**:
- `feature/` - features ใหม่
- `fix/` - bug fixes
- `hotfix/` - ด่วน
- `refactor/` - ปรับปรุง code

**ลบ branch**:
```cmd
git branch -d feature/completed
git push origin --delete feature/completed
```

### 5.1 Tag Management (การจัดรุ่นเวอร์ชัน)
**Semantic Versioning**: `vMAJOR.MINOR.PATCH`
- **MAJOR**: เมื่อมีการเปลี่ยนแปลงครั้งใหญ่ (Breaking changes)
- **MINOR**: เมื่อเพิ่ม Feature ใหม่ (Backwards compatible)
- **PATCH**: เมื่อแก้ไข Bug หรือปรับแต่งเล็กน้อย (Backwards compatible)

**คำสั่งที่แนะนำ:**
```cmd
# สร้าง tag (Annotated tag)
git tag -a v1.1.0 -m "Release version 1.1.0: UI refinements and alignment fixes"

# Push tag ขึ้น Server
git push origin v1.1.0
git push origin --tags  # Push tag ทั้งหมดที่มี
```

**ตัวอย่างการเลือกใช้:**
- ปรับสีกึ่งกลางตาราง -> `v1.0.1` (Patch)
- เพิ่มระบบคำขออนุมัติใหม่ -> `v1.1.0` (Minor)
- เปลี่ยนโครงสร้างฐานข้อมูลทั้งหมด -> `v2.0.0` (Major)

## 6. 🔄 Common Operations

**Stash**:
```cmd
git stash
git stash pop
```

**Revert**:
```cmd
git revert <commit-hash>
```

**Reset (ระวัง)**:
```cmd
git reset HEAD <file>           # ยกเลิก stage
git reset --hard <commit-hash>  # อันตราย - ใช้ระวัง
```

## 7. 🛡️ Emergency: Push ไฟล์ Sensitive

> [!CAUTION]
> **ลบออกทันที และ rotate credentials**

```cmd
git rm --cached .env
git commit -m "chore: remove sensitive file"
git push origin main
```

**Incident Response Checklist**:
1. 🔴 **Notify** - แจ้ง Security Team/Lead ทันที
2. 🔄 **Rotate** - เปลี่ยน passwords, API keys, tokens ทั้งหมด
3. 🧹 **Clean History** - ใช้ BFG Repo-Cleaner: https://rtyley.github.io/bfg-repo-cleaner/
4. 📝 **Document** - บันทึก incident report
5. 🔍 **Audit** - ตรวจสอบ access logs

## ✅ Checklist
- [ ] อัปเดต local repo และตรวจสอบ branch
- [ ] Security Check: ไม่มีไฟล์ sensitive
- [ ] Review: `git diff`
- [ ] Selective Staging: ระบุไฟล์ทีละตัว
- [ ] Verify: `git status`
- [ ] Meaningful commit message
- [ ] Pre-push: `git log` ตรวจสอบ
- [ ] Push
- [ ] Verify remote

## 📚 Resources
- Conventional Commits: https://www.conventionalcommits.org/
- Semantic Versioning: https://semver.org/
