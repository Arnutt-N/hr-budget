# Next.js PRP Template v2 - Context-Rich with Validation Loops

name: "Next.js PRP Template v2 - Context-Rich with Validation Loops"
description: |

## Purpose
Template optimized for AI agents to implement Next.js features with sufficient context and self-validation capabilities to achieve working code through iterative refinement.

## Core Principles
1. **Context is King**: Include ALL necessary documentation, examples, and caveats
2. **Validation Loops**: Provide executable tests/builds the AI can run and fix
3. **Information Dense**: Use keywords and patterns from the Next.js codebase
4. **Progressive Success**: Start simple, validate, then enhance
5. **Global rules**: Be sure to follow all rules in CLAUDE.md

---

## Goal
[What needs to be built - be specific about the end state and user experience]

## Why
- [Business value and user impact]
- [Integration with existing pages/components]
- [Problems this solves and for whom]

## What
[User-visible behavior and technical requirements]

### Success Criteria
- [ ] [Specific measurable outcomes]
- [ ] [Performance benchmarks if applicable]
- [ ] [Accessibility requirements]

## All Needed Context

### Documentation & References (list all context needed to implement the feature)
```yaml
# MUST READ - Include these in your context window
- url: [Next.js docs URL]
  why: [Specific sections/APIs you'll need]
  
- file: [app/example/page.tsx]
  why: [Pattern to follow, gotchas to avoid]
  
- doc: [React/Library documentation URL] 
  section: [Specific section about common pitfalls]
  critical: [Key insight that prevents common errors]

- docfile: [docs/components/README.md]
  why: [Component patterns and conventions]

- config: [next.config.js, tailwind.config.js, etc.]
  why: [Build configuration and styling setup]

# RESEARCH DOCUMENTATION - Reference existing research
- research: [research/data_models.md]
  why: [Existing type patterns and database schemas to follow]
  
- research: [research/ecommerce_components.md]
  why: [Component architecture patterns and conventions]
  
- research: [research/implementation_blueprint.md]
  why: [Established architectural decisions and strategies]
  
- research: [research/supabase_ecommerce.md]
  why: [Database integration patterns and best practices]
  
- research: [research/validation_strategy.md]
  why: [Testing approaches and validation patterns]
```

### Current Codebase Structure (run `tree -I 'node_modules|.next|.git'` in the root)
```bash
# Include current folder structure to understand existing patterns
```

### Desired Codebase Structure with files to be added
```bash
# Show where new files will be placed and their purpose
app/
  new-feature/
    page.tsx           # Main page component
    loading.tsx        # Loading UI
    error.tsx          # Error boundary
    layout.tsx         # Feature-specific layout (if needed)
components/
  ui/
    new-component.tsx  # Reusable UI component
  feature/
    feature-form.tsx   # Feature-specific component
lib/
  utils/
    new-utils.ts       # Utility functions
  hooks/
    use-new-feature.ts # Custom React hook
types/
  new-feature.ts       # TypeScript definitions
research/              # Updated research documentation
  data_models.md       # Update with new types/schemas
  ecommerce_components.md # Update with new component patterns
  implementation_blueprint.md # Update with architectural decisions
  supabase_ecommerce.md # Update with database patterns
  validation_strategy.md # Update with testing approaches
```

### Known Gotchas of Next.js & Library Quirks
```typescript
// CRITICAL: Next.js App Router specifics
// Example: Server Components are async by default, Client Components need 'use client'
// Example: Dynamic imports require proper loading states
// Example: Metadata API only works in Server Components
// Example: useSearchParams() requires Suspense boundary
// Example: We use Tailwind CSS with custom design system tokens
```

## Implementation Blueprint

### Type Definitions and Interfaces

Create the core TypeScript types to ensure type safety across components.
```typescript
// Example type definitions
interface User {
  id: string;
  email: string;
  name: string;
}

interface ApiResponse<T> {
  data: T;
  error?: string;
  status: 'success' | 'error';
}

// Zod schemas for validation
const userSchema = z.object({
  email: z.string().email(),
  name: z.string().min(2),
});
```

### List of tasks to be completed to fulfill the PRP in order

```yaml
Task 1: Setup Types and Interfaces
CREATE types/new-feature.ts:
  - DEFINE core interfaces and types
  - INCLUDE Zod schemas for validation
  - EXPORT all necessary types

Task 2: Create Utility Functions
CREATE lib/utils/new-feature-utils.ts:
  - MIRROR pattern from: lib/utils/existing-utils.ts
  - INCLUDE error handling
  - ADD proper TypeScript types

Task 3: Build Custom Hook (if needed)
CREATE lib/hooks/use-new-feature.ts:
  - FOLLOW existing hook patterns
  - INCLUDE loading and error states
  - USE proper dependency array

Task 4: Create UI Components
CREATE components/ui/new-component.tsx:
  - FOLLOW design system patterns
  - INCLUDE proper prop types
  - ADD accessibility attributes

Task 5: Build Feature Components
CREATE components/feature/feature-form.tsx:
  - USE custom hooks
  - IMPLEMENT form validation
  - HANDLE loading/error states

Task 6: Create Page Components
CREATE app/new-feature/page.tsx:
  - USE Server Component by default
  - IMPLEMENT proper metadata
  - INCLUDE error boundaries

Task 7: Add API Routes (if needed)
CREATE app/api/new-feature/route.ts:
  - FOLLOW RESTful conventions
  - INCLUDE proper error handling
  - ADD request validation

Task 8: Update Navigation/Links
MODIFY components/navigation.tsx:
  - ADD new route links
  - PRESERVE existing patterns
  - UPDATE mobile menu if needed

Task 9: Update Research Documentation
UPDATE research/data_models.md:
  - ADD new TypeScript interfaces
  - DOCUMENT database schema changes
  - INCLUDE validation schemas

UPDATE research/ecommerce_components.md:
  - DOCUMENT new component patterns
  - ADD reusable component guidelines
  - INCLUDE accessibility considerations

UPDATE research/implementation_blueprint.md:
  - DOCUMENT architectural decisions made
  - ADD integration patterns used
  - INCLUDE performance considerations

UPDATE research/supabase_ecommerce.md:
  - ADD new database queries/mutations
  - DOCUMENT API integration patterns
  - INCLUDE caching strategies

UPDATE research/validation_strategy.md:
  - ADD new testing patterns implemented
  - DOCUMENT validation approaches
  - INCLUDE error handling strategies
```

### Per Task Implementation Details

```typescript
// Task 1: Type Definitions
// CRITICAL: Use proper TypeScript patterns
interface NewFeatureProps {
  id: string;
  onSubmit: (data: FormData) => Promise<void>;
  initialData?: Partial<FormData>;
}

// Task 4: UI Component Pattern
'use client'; // Only if client-side interactivity needed

import { cn } from '@/lib/utils';

interface ComponentProps {
  className?: string;
  children: React.ReactNode;
}

export function NewComponent({ className, children }: ComponentProps) {
  // PATTERN: Use existing utility functions
  // GOTCHA: Tailwind classes need proper merging
  return (
    <div className={cn('base-styles', className)}>
      {children}
    </div>
  );
}

// Task 6: Page Component Pattern
import { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Feature Name',
  description: 'Feature description for SEO',
};

export default async function FeaturePage() {
  // PATTERN: Server Component for data fetching
  // CRITICAL: Handle errors gracefully
  try {
    const data = await fetchData();
    return <FeatureContent data={data} />;
  } catch (error) {
    return <ErrorComponent error={error} />;
  }
}
```

### Integration Points
```yaml
ROUTING:
  - add to: app/new-feature/page.tsx
  - pattern: "App Router file-based routing"
  
NAVIGATION:
  - update: components/navigation.tsx
  - pattern: "Add to existing nav items array"
  
STYLING:
  - extend: tailwind.config.js (if new utilities needed)
  - pattern: "Follow existing design tokens"
  
API:
  - add to: app/api/new-feature/route.ts
  - pattern: "RESTful API with proper HTTP methods"

DATABASE (if using):
  - schema: prisma/schema.prisma or lib/db/schema.ts
  - migration: "Add new tables/columns as needed"
```

## Validation Loop

### Level 1: TypeScript & Linting
```bash
# Run these FIRST - fix any errors before proceeding
npm run type-check                    # TypeScript compilation
npm run lint                         # ESLint checking
npm run lint:fix                     # Auto-fix what's possible

# Expected: No errors. If errors, READ the error and fix.
```

### Level 2: Unit Tests for each new component/function
```typescript
// CREATE __tests__/new-component.test.tsx
import { render, screen } from '@testing-library/react';
import { NewComponent } from '@/components/ui/new-component';

describe('NewComponent', () => {
  it('renders children correctly', () => {
    render(<NewComponent>Test content</NewComponent>);
    expect(screen.getByText('Test content')).toBeInTheDocument();
  });

  it('applies custom className', () => {
    render(<NewComponent className="custom-class">Content</NewComponent>);
    const element = screen.getByText('Content').parentElement;
    expect(element).toHaveClass('custom-class');
  });

  it('handles user interactions', async () => {
    const mockHandler = jest.fn();
    render(<InteractiveComponent onSubmit={mockHandler} />);
    
    // Test user interactions
    await userEvent.click(screen.getByRole('button'));
    expect(mockHandler).toHaveBeenCalled();
  });
});
```

```bash
# Run and iterate until passing:
npm run test                         # Run all tests
npm run test new-component          # Run specific test

# If failing: Read error, understand root cause, fix code, re-run
```

### Level 3: Build & Integration Test
```bash
# Build the application
npm run build

# Expected: Successful build with no errors
# If build fails: Check the error output and fix issues

# Start development server
npm run dev

# Test the feature manually:
# 1. Navigate to /new-feature
# 2. Test all interactive elements
# 3. Check responsive design
# 4. Verify accessibility with screen reader
```

### Level 4: Performance & Accessibility
```bash
# Check bundle size impact
npm run analyze                      # If webpack-bundle-analyzer is setup

# Test accessibility
npm run test:a11y                   # If accessibility tests are setup

# Lighthouse CI (if configured)
npm run lighthouse
```

## Final Validation Checklist
- [ ] All tests pass: `npm run test`
- [ ] No TypeScript errors: `npm run type-check`
- [ ] No linting errors: `npm run lint`
- [ ] Build succeeds: `npm run build`
- [ ] Manual testing complete: All user flows work
- [ ] Responsive design verified: Mobile, tablet, desktop
- [ ] Accessibility checked: Keyboard navigation, screen reader
- [ ] Performance acceptable: No significant bundle size increase
- [ ] Error states handled gracefully
- [ ] Loading states implemented properly
- [ ] Research documentation updated: All relevant research files reflect new patterns and decisions

---

## Anti-Patterns to Avoid
- ❌ Don't use Client Components when Server Components suffice
- ❌ Don't skip TypeScript types - use proper interfaces
- ❌ Don't ignore build errors or warnings
- ❌ Don't use `any` type - be specific with types
- ❌ Don't forget Suspense boundaries for async components
- ❌ Don't hardcode values - use environment variables
- ❌ Don't skip accessibility attributes (ARIA labels, roles)
- ❌ Don't ignore responsive design - test on all screen sizes
- ❌ Don't use inline styles - follow Tailwind/CSS conventions
- ❌ Don't fetch data in Client Components when Server Components can do it