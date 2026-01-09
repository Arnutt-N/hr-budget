# Generate PRP for Next.js Projects

## Feature file: $ARGUMENTS

Generate a complete PRP for Next.js feature implementation with thorough research and documentation updates. Ensure context is passed to the AI agent to enable self-validation and iterative refinement. Read the feature file first to understand what needs to be created, how the examples provided help, and any other considerations.

The AI agent only gets the context you are appending to the PRP and training data. Assume the AI agent has access to the codebase and the same knowledge cutoff as you, so it's important that your research findings are included or referenced in the PRP. The Agent has web search capabilities, so pass URLs to documentation and examples.

## Research Process

### 1. **Codebase Analysis**
   - Search for similar features/patterns in the codebase
   - Identify files to reference in PRP
   - Note existing conventions to follow
   - Check test patterns for validation approach
   - **Review existing research documentation**:
     - Check `research/data_models.md` for existing type patterns
     - Review `research/ecommerce_components.md` for component architecture
     - Analyze `research/implementation_blueprint.md` for architectural decisions
     - Study `research/supabase_ecommerce.md` for database patterns
     - Examine `research/validation_strategy.md` for testing approaches

### 2. **External Research**
   - Search for similar features/patterns online
   - Next.js and React documentation (include specific URLs)
   - Implementation examples (GitHub/StackOverflow/blogs)
   - Best practices and common pitfalls for Next.js
   - Library-specific documentation (Supabase, Tailwind, etc.)

### 3. **Research Documentation Strategy**
   - Plan what research files need to be updated
   - Identify new patterns that should be documented
   - Consider architectural decisions that need recording
   - Plan validation strategies for the new feature

### 4. **User Clarification** (if needed)
   - Specific patterns to mirror and where to find them?
   - Integration requirements and where to find them?
   - Which research documents need updating?

## PRP Generation

Using `PRPs/templates/prp_base.md` as template:

### Critical Context to Include and pass to the AI agent as part of the PRP
- **Documentation**: URLs with specific sections
- **Code Examples**: Real snippets from codebase
- **Gotchas**: Next.js App Router quirks, library version issues
- **Patterns**: Existing approaches to follow from research docs
- **Research References**: Specific sections from research files to follow

### Implementation Blueprint
- Start with TypeScript interfaces and types
- Reference real files for patterns
- Include Server/Client Component strategy
- Include error handling and loading states
- List tasks to be completed to fulfill the PRP in order they should be completed
- **Include research documentation update tasks**

### Research Documentation Updates
Must include tasks to update relevant research files:

```yaml
Research Updates Required:
- research/data_models.md: [What new types/schemas to add]
- research/ecommerce_components.md: [What component patterns to document]  
- research/implementation_blueprint.md: [What architectural decisions to record]
- research/supabase_ecommerce.md: [What database patterns to add]
- research/validation_strategy.md: [What testing approaches to document]
```

### Validation Gates (Must be Executable for Next.js)
```bash
# TypeScript & Linting
npm run type-check && npm run lint

# Unit Tests  
npm run test

# Build Verification
npm run build

# E2E Tests (if applicable)
npm run e2e
```

## Critical Requirements

### **BEFORE WRITING THE PRP:**
1. **Review ALL existing research documentation**
2. **Identify patterns to follow from research files**
3. **Plan which research files need updates**
4. **ULTRA-THINK about the PRP approach and architecture**
5. **Consider Next.js App Router specific requirements**

### **DURING PRP WRITING:**
- Reference specific research documents and sections
- Include research update tasks in implementation plan
- Ensure validation gates cover all Next.js concerns
- Include Server/Client Component considerations
- Add performance and accessibility requirements

### **PRP STRUCTURE REQUIREMENTS:**
- Must reference existing research documentation
- Must include research documentation update tasks
- Must include Next.js specific validation (build, type-check)
- Must consider App Router patterns and conventions
- Must include accessibility and performance validation

## Output
Save as: `PRPs/{feature-name}.md`

## Quality Checklist
- [ ] All necessary context included
- [ ] Validation gates are executable by AI
- [ ] References existing patterns from research docs
- [ ] Clear implementation path with research updates
- [ ] Error handling documented
- [ ] Next.js App Router patterns considered
- [ ] Research documentation updates planned
- [ ] TypeScript types and interfaces planned
- [ ] Server/Client Component strategy defined
- [ ] Performance considerations included
- [ ] Accessibility requirements included

## Success Metrics
Score the PRP on a scale of 1-10 (confidence level to succeed in one-pass implementation using Claude):

**Scoring Criteria:**
- **10**: Complete context, all research patterns referenced, executable validation, clear Next.js patterns
- **8-9**: Strong context with minor gaps, most research referenced, mostly executable validation
- **6-7**: Good context but missing some research patterns or validation gaps
- **4-5**: Adequate context but significant research or validation missing
- **1-3**: Insufficient context, missing research patterns, non-executable validation

**Target Score: 8-10** for reliable one-pass implementation success.

Remember: The goal is one-pass implementation success through comprehensive context and proper research documentation management.