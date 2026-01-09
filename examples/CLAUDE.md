# CLAUDE.md - Next.js Project Guidelines

### 🔄 Project Awareness & Context
- **Always read `PLANNING.md`** at the start of a new conversation to understand the project's architecture, goals, style, and constraints.
- **Check `TASK.md`** before starting a new task. If the task isn't listed, add it with a brief description and today's date.
- **Use consistent naming conventions, file structure, and architecture patterns** as described in `PLANNING.md`.
- **Follow Next.js App Router conventions** for routing, layouts, and page structure.

### 🧱 Code Structure & Modularity
- **Never create a file longer than 500 lines of code.** If a file approaches this limit, refactor by splitting it into components or utility files.
- **Organize code into clearly separated modules**, grouped by feature or responsibility:
  - `/app` - App Router pages and layouts
  - `/components` - Reusable UI components
  - `/lib` - Utility functions and configurations
  - `/hooks` - Custom React hooks
  - `/types` - TypeScript type definitions
  - `/research` - Technical research and implementation blueprints
    - `data_models.md` - Database schemas and type definitions
    - `ecommerce_components.md` - Component architecture patterns
    - `implementation_blueprint.md` - Feature implementation strategies
    - `supabase_ecommerce.md` - Database integration patterns
    - `validation_strategy.md` - Testing and validation approaches
- **Use clear, consistent imports** (prefer relative imports within the same feature, absolute imports from `@/` for cross-feature).
- **Follow the component-first architecture** - break UI into small, reusable components.

### 🧪 Testing & Reliability
- **Always create Jest/React Testing Library tests for new components and utilities**.
- **After updating any logic**, check whether existing tests need to be updated. If so, do it.
- **Tests should live in `__tests__` folders** or alongside components with `.test.tsx` suffix.
  - Include at least:
    - 1 test for expected rendering/behavior
    - 1 test for user interactions
    - 1 test for edge cases or error states
- **Use Playwright or Cypress for E2E tests** when testing full user flows.

### ✅ Task Completion
- **Mark completed tasks in `TASK.md`** immediately after finishing them.
- Add new sub-tasks or TODOs discovered during development to `TASK.md` under a "Discovered During Work" section.

### 📎 Style & Conventions
- **Use TypeScript** as the primary language for all `.tsx` and `.ts` files.
- **Follow Next.js best practices** including:
  - Server Components by default, Client Components only when needed
  - Proper use of `async/await` in Server Components
  - Correct data fetching patterns (Server Components, `use` hook, SWR/TanStack Query)
- **Use ESLint and Prettier** for code formatting and linting.
- **Follow React conventions**:
  - PascalCase for component names
  - camelCase for props and variables
  - Use functional components with hooks
- **Use CSS Modules, Tailwind CSS, or styled-components** for styling (as specified in `PLANNING.md`).
- Write **JSDoc comments for complex functions and components**:
  ```typescript
  /**
   * Brief summary of component purpose.
   * 
   * @param props - Component props
   * @param props.title - The title to display
   * @returns JSX element
   */
  function ExampleComponent({ title }: { title: string }) {
    // implementation
  }
  ```

### 🚀 Next.js Specific Guidelines
- **Use Server Components for data fetching** when possible to improve performance.
- **Implement proper SEO** with metadata API and structured data.
- **Optimize images** using `next/image` component.
- **Use dynamic imports** for code splitting when appropriate.
- **Follow Next.js caching strategies** for optimal performance.
- **Implement proper error boundaries** and error handling.

### 📚 Documentation & Explainability
- **Update `README.md`** when new features are added, dependencies change, or setup steps are modified.
- **Comment non-obvious code** and ensure everything is understandable to a mid-level React/Next.js developer.
- When writing complex logic, **add an inline `// Reason:` comment** explaining the why, not just the what.
- **Document component props** with TypeScript interfaces and JSDoc when needed.
- **Update research documentation** when implementing new patterns:
  - Update `research/data_models.md` when adding new types or database schemas
  - Update `research/ecommerce_components.md` when creating new component patterns
  - Update `research/implementation_blueprint.md` when establishing new architectural decisions
  - Update `research/supabase_ecommerce.md` when adding database integration patterns
  - Update `research/validation_strategy.md` when implementing new testing approaches

### 🧠 AI Behavior Rules
- **Never assume missing context. Ask questions if uncertain.**
- **Never hallucinate libraries or packages** – only use known, verified npm packages compatible with Next.js.
- **Always confirm file paths and component names** exist before referencing them in code or tests.
- **Never delete or overwrite existing code** unless explicitly instructed to or if part of a task from `TASK.md`.
- **Check Next.js version compatibility** when suggesting features or APIs.
- **Consider both client-side and server-side implications** when writing code.
- **Always reference research documentation** when implementing features:
  - Check `research/data_models.md` for existing type patterns before creating new ones
  - Follow patterns documented in `research/ecommerce_components.md` for component architecture
  - Align with strategies in `research/implementation_blueprint.md` for feature development
  - Use database patterns from `research/supabase_ecommerce.md` when working with data
  - Follow testing approaches from `research/validation_strategy.md` for quality assurance

### 🔧 Development Workflow
- **Use `npm run dev`** for local development.
- **Run `npm run build`** before major commits to catch build issues.
- **Use `npm run lint`** and `npm run type-check`** before committing.
- **Test components in isolation** using Storybook if available.
- **Follow Git commit conventions** as specified in `PLANNING.md`.