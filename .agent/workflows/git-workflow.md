---
description: การจัดการ Git Version Control แบบ Enterprise-Grade อย่างปลอดภัย
---

# 🔐 Git Version Control Workflow (Enterprise-Grade)

> [!CAUTION]
> **ห้ามใช้ `git add .` หากไม่แน่ใจในไฟล์ทั้งหมด** - อาจส่งไฟล์ที่เป็นความลับขึ้น repo โดยไม่ตั้งใจ

## 0. 🔍 Pre-Commit Checks
```cmd
git branch              # ตรวจสอบ branch ปัจจุบัน
git fetch origin        # ดึงข้อมูลจาก remote
git pull origin main    # อัปเดต local
git status              # ตรวจสอบสถานะ
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

### 1.2 .gitignore Template
```gitignore
# Environment & Secrets
.env
.env.*
!.env.example
config/database.php

# Dependencies
/vendor/
/node_modules/

# IDE
.vscode/
.idea/

# Logs
*.log
/storage/logs/

# OS
.DS_Store
Thumbs.db

# Build & Cache
/bootstrap/cache/*
.phpunit.result.cache

# Backups
*.bak
*.sql
*.dump

# Keys & Certificates
*.pem
*.key
*.crt
*.p12
*.pfx
*.keystore
*.jks
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
git rm --cached -r vendor/
echo .env >> .gitignore
git commit -m "chore: update .gitignore"
```

### 1.3 Pre-Commit Hook
สร้างไฟล์ `.git/hooks/pre-commit`:
```bash
#!/bin/sh
RED='\033[0;31m'
NC='\033[0m'

FORBIDDEN=".env .env.local .env.production config/database.php"
for file in $FORBIDDEN; do
    if git diff --cached --name-only | grep -q "^$file$"; then
        echo "${RED}ERROR: Forbidden file: $file${NC}"
        exit 1
    fi
done

# Extended secret patterns
SECRET_PATTERNS="password|secret|api_key|apikey|aws_access|aws_secret|private_key|token|bearer|authorization"
if git diff --cached | grep -iE "$SECRET_PATTERNS" > /dev/null; then
    echo "${RED}WARNING: Potential secrets detected!${NC}"
    exit 1
fi
exit 0
```

Windows setup:
```cmd
code .git\hooks\pre-commit
chmod +x .git/hooks/pre-commit  # ใน Git Bash
```

### 1.4 Secret Scanning

**gitleaks**:
```cmd
choco install gitleaks
gitleaks detect --source . --verbose           # Scan current files
gitleaks detect --source . --log-opts="--all"  # Scan entire history
```

**Manual search**:
```cmd
findstr /S /I /M "password\|secret\|api_key\|token" *
git log -p | findstr /I "password secret api_key token"
git log --all --full-history -- .env
git log --all --full-history -- "*.key" "*.pem"
```

### 1.5 Dependency Security Audit
```cmd
composer audit           # PHP
npm audit               # Node.js
```

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

### 3.1 Conventional Commits
```
<type>(<scope>): <subject>
```

**Types**: `feat`, `fix`, `refactor`, `docs`, `style`, `test`, `chore`, `perf`

**ตัวอย่าง**:
```cmd
git commit -m "feat(budget): add KPI tracking system"
git commit -m "fix(auth): resolve session timeout"
git commit -m "refactor(controllers): extract budget logic"
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

### 3.3 Signed Commits (GPG)
```cmd
gpg --full-generate-key  # สร้าง key (4096 bits)
gpg --list-secret-keys --keyid-format=LONG
git config --global user.signingkey <KEY-ID>
git config --global commit.gpgsign true
git config --global tag.gpgSign true

# Export และเพิ่มใน GitHub Settings
gpg --armor --export <KEY-ID>
```

### 3.4 Commit Message Linting
**ตรวจสอบ commit message ด้วย commitlint (optional)**:
```cmd
npm install -g @commitlint/cli @commitlint/config-conventional
echo "module.exports = {extends: ['@commitlint/config-conventional']}" > commitlint.config.js
```

## 4. 🚀 Pushing
```cmd
git log --oneline -5  # ตรวจสอบก่อน push
git push origin <branch-name>
```

**Force push (ระวัง)**:
```cmd
git push origin <branch-name> --force-with-lease
```

## 5. 🌿 Branch Management

**สร้าง branch**:
```cmd
git checkout -b feature/new-feature
```

**Branch naming**:
- `feature/` - features ใหม่
- `fix/` - bug fixes
- `hotfix/` - ด่วน
- `refactor/` - ปรับปรุง code

**Merge conflicts**:
```cmd
git merge main  # พบ conflict
git status      # ดูไฟล์ที่ conflict
# แก้ไข markers (<<<<<<<, =======, >>>>>>>)
git add <resolved-file>
git commit -m "merge: resolve conflicts"
```

**ลบ branch**:
```cmd
git branch -d feature/completed
git push origin --delete feature/completed
```

### 5.1 Tag Management
```cmd
# สร้าง tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
git push origin --tags  # push ทั้งหมด

# ลบ tag
git tag -d v1.0.0
git push origin --delete v1.0.0
```

**Semantic Versioning**: `vMAJOR.MINOR.PATCH`

## 6. 🔄 Advanced Operations

**Stash**:
```cmd
git stash
git stash list
git stash pop
```

**Revert**:
```cmd
git revert <commit-hash>
```

**Reset (ระวัง)**:
```cmd
git reset HEAD <file>           # ยกเลิก stage
git reset --hard <commit-hash>  # อันตราย
```

**Interactive Rebase**:
```cmd
git rebase -i HEAD~3
# pick, squash, reword, drop commits
git push origin <branch> --force-with-lease
```

## 7. 📋 Pull Request Workflow

1. สร้าง feature branch
2. พัฒนาและ commit
3. Push และสร้าง PR
4. Code Review
5. Merge

**Update branch**:
```cmd
git checkout main
git pull origin main
git checkout feature/your-feature
git merge main  # หรือ git rebase main
```

### 7.1 Branch Protection (GitHub/GitLab)
สำหรับ `main` branch:
- ✅ Require PR before merging
- ✅ Require approvals (1-2 คน)
- ✅ Require status checks (CI/CD)
- ✅ Require signed commits
- ✅ Restrict pushes
- ✅ Include administrators
- ✅ Dismiss stale reviews
- ✅ Require linear history
- ✅ Require conversation resolution

### 7.2 CODEOWNERS (Two-Person Rule)
สร้างไฟล์ `.github/CODEOWNERS` เพื่อบังคับ review:
```
# Critical files require security team review
.env.example @security-team
config/database.php.example @security-team @lead-dev
/src/Controllers/Auth* @security-team
*.php @dev-team
```

## 8. 🛡️ Backup & Safety

**Backup tag**:
```cmd
git tag backup-$(date +%Y%m%d-%H%M%S)
```

**Reflog (กู้คืน)**:
```cmd
git reflog
git reset --hard HEAD@{1}
```

**Temporary branch**:
```cmd
git branch backup-current-work
# ทำงานเสี่ยง
git reset --hard backup-current-work  # ถ้าผิดพลาด
```

### 8.1 Credential Management
```cmd
git config --global credential.helper manager
```

ลบ credentials: **Windows Credential Manager** > `git:https://github.com`

**SSH Key Best Practices**:
```cmd
# สร้าง SSH key (Ed25519 - แนะนำ)
ssh-keygen -t ed25519 -C "your_email@example.com"

# หรือ RSA 4096-bit
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

# เพิ่ม public key ใน GitHub/GitLab Settings
cat ~/.ssh/id_ed25519.pub
```

### 8.2 Maintenance
```cmd
git fetch --prune                      # ลบ dead branches
git config --global fetch.prune true   # auto-prune
git gc --aggressive --prune=now        # optimize repo
git fsck --full                        # ตรวจสอบความเสียหาย
git config --global init.defaultBranch main  # default branch
```

### 8.3 Audit Trail & Forensics
```cmd
# Audit commits by author
git log --all --author="<name>" --pretty=fuller

# ดูการเปลี่ยนแปลงในไฟล์เฉพาะ
git log --follow -p -- <file>

# ดูใครแก้แต่ละบรรทัด (blame)
git blame <file>

# ดูทุก commits ที่กระทบไฟล์นี้
git log --all --full-history -- <file>
```

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

## 🚨 Emergency: Push ไฟล์ Sensitive

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
4. 📝 **Document** - บันทึก incident report (เวลา, ไฟล์, ผลกระทบ)
5. 🔍 **Audit** - ตรวจสอบ access logs ว่ามีการเข้าถึงหรือไม่

## 📚 Resources
- Conventional Commits: https://www.conventionalcommits.org/
- Git Documentation: https://git-scm.com/doc
- Semantic Versioning: https://semver.org/
