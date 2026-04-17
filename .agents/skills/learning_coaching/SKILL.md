---
name: learning_coaching
description: Guide for onboarding new developers and continuous learning in the HR Budget project.
---

# Learning & Coaching Guide

Resources for onboarding new team members and continuous skill development.

## 📑 Table of Contents

- [Onboarding Checklist](#-onboarding-checklist)
- [Project Familiarization](#-project-familiarization)
- [Learning Paths](#-learning-paths)
- [Code Review Guidelines](#-code-review-guidelines)
- [Mentoring Best Practices](#-mentoring-best-practices)

## ✅ Onboarding Checklist

### Day 1: Environment Setup

- [ ] Clone repository: `git clone <repo-url>`
- [ ] Copy `.env.example` to `.env` and configure
- [ ] Install dependencies: `composer install`
- [ ] Setup database and run migrations
- [ ] Start local server: `php -S localhost:8000 -t public`
- [ ] Verify login works with test account

### Week 1: Understand the Project

- [ ] Read `README.md` and project documentation
- [ ] Review Skills in `.agents/skills/`:
  - [ ] `hr_budget_assistant` (Main reference)
  - [ ] `frontend_assistant`
  - [ ] `database_assistant`
- [ ] Understand directory structure
- [ ] Walk through a simple CRUD flow (e.g., Budgets)

### Week 2: First Contributions

- [ ] Fix a small bug or typo
- [ ] Add a simple feature
- [ ] Create first Pull Request
- [ ] Receive and respond to code review

## 📚 Project Familiarization

### Key Concepts to Understand

| Concept | Files to Study | Notes |
|:--------|:---------------|:------|
| **Routing** | `config/routes.php` | URL → Controller mapping |
| **Controllers** | `src/Controllers/` | Request handling |
| **Models** | `src/Models/` | Data access |
| **Views** | `resources/views/` | PHP templates |
| **Authentication** | `src/Core/Auth.php` | Session-based |

### Recommended Reading Order

1. **Architecture Overview**
   ```
   .agents/skills/hr_budget_assistant/SKILL.md
   ```

2. **Database Schema**
   ```
   .agents/skills/database_assistant/SKILL.md
   docs/schema/
   ```

3. **Frontend Patterns**
   ```
   .agents/skills/frontend_assistant/SKILL.md
   resources/css/app.css
   ```

4. **Security Practices**
   ```
   .agents/skills/devsecops_assistant/SKILL.md
   .agents/skills/auth_rbac/SKILL.md
   ```

### Tracing a Request

```
User clicks "Create Budget"
    ↓
GET /budgets/create
    ↓
config/routes.php → BudgetController@create
    ↓
src/Controllers/BudgetController.php
    → public function create()
    → View::render('budgets/create', [...])
    ↓
resources/views/budgets/create.php
    → HTML form rendered
    ↓
User submits form
    ↓
POST /budgets
    ↓
BudgetController@store
    → Validate input
    → Budget::create($data)
    → Redirect with success message
```

## 🎯 Learning Paths

### Path 1: Backend Developer

| Week | Focus | Resources |
|:----:|:------|:----------|
| 1 | PHP Basics | php.net, Laracasts |
| 2 | MVC Pattern | Project code, tutorials |
| 3 | Database/SQL | MySQL docs, `database_assistant` |
| 4 | Authentication | `auth_rbac` skill |
| 5 | API Development | `api_development` skill |
| 6 | Testing | `testing_assistant` skill |

### Path 2: Frontend Developer

| Week | Focus | Resources |
|:----:|:------|:----------|
| 1 | HTML/CSS | MDN, `frontend_assistant` |
| 2 | Tailwind CSS | tailwindcss.com |
| 3 | JavaScript | MDN, project JS files |
| 4 | Accessibility | `accessibility_guidelines` |
| 5 | Vite/Build Tools | `frontend_assistant` (Vite section) |
| 6 | UI Components | Existing view components |

### Path 3: Full-Stack Developer

| Week | Focus |
|:----:|:------|
| 1-2 | Backend basics |
| 3-4 | Frontend basics |
| 5 | Integration (CRUD flows) |
| 6 | Testing & Deployment |

## 🔍 Code Review Guidelines

### For Reviewers

**DO:**
- ✅ Be constructive and specific
- ✅ Explain the "why" behind suggestions
- ✅ Acknowledge good code
- ✅ Use questions instead of commands
- ✅ Prioritize issues (critical vs nice-to-have)

**DON'T:**
- ❌ Be dismissive or condescending
- ❌ Nitpick style issues (use linters)
- ❌ Delay reviews (respond within 24h)
- ❌ Approve without reading

### Example Comments

```markdown
❌ Bad: "This is wrong."

✅ Good: "Consider using `htmlspecialchars()` here to prevent XSS. 
         See our `frontend_assistant` skill for examples."

❌ Bad: "Why did you do it this way?"

✅ Good: "I'm curious about the approach here. 
         Would using a Service class help separate concerns? 
         No blocker if there's a good reason for this structure."
```

### PR Checklist

```markdown
## Checklist
- [ ] Code follows project conventions
- [ ] No hardcoded values (use config/env)
- [ ] Input validation added
- [ ] Output escaped (XSS prevention)
- [ ] Tests added/updated (if applicable)
- [ ] Documentation updated (if applicable)
```

## 👥 Mentoring Best Practices

### For Mentors

1. **Start with Context**
   - Explain the business purpose before the code
   - Walk through user stories

2. **Pair Programming**
   - 30-60 min sessions
   - Driver (junior) / Navigator (senior) roles
   - Switch roles halfway

3. **Progressive Autonomy**
   | Stage | Mentor Role | Junior Role |
   |:------|:------------|:------------|
   | Week 1 | Demo | Watch |
   | Week 2 | Guide | Do with help |
   | Week 3 | Review | Do independently |
   | Week 4+ | Consult | Own tasks |

4. **Regular Check-ins**
   - Daily standup (5 min)
   - Weekly 1:1 (30 min)
   - Monthly retrospective

### For Juniors

1. **Ask Questions Early**
   - Don't struggle alone for hours
   - Frame questions well: "I tried X, expected Y, got Z"

2. **Take Notes**
   - Document decisions and reasons
   - Build a personal knowledge base

3. **Practice Deliberately**
   - Don't just complete tasks
   - Understand the patterns

4. **Seek Feedback**
   - Ask for specific feedback after tasks
   - "How could I have done this better?"

## 📖 Recommended Resources

### Official Documentation
- [PHP Manual](https://www.php.net/manual/en/)
- [MySQL 8.0 Reference](https://dev.mysql.com/doc/refman/8.0/en/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [MDN Web Docs](https://developer.mozilla.org/)

### Project-Specific
- All Skills in `.agents/skills/`
- All Workflows in `.agents/workflows/`
- Project logs in `project-log-md/`

### Video Courses
- Laracasts (PHP/Laravel concepts apply)
- Traversy Media (Web development)
- Kevin Powell (CSS)
