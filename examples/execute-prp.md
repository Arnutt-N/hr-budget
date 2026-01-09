# Execute Next.js PRP
Implement a Next.js feature using the PRP file with comprehensive research documentation management.

## PRP File: $ARGUMENTS

## Execution Process

### 1. **Load PRP & Research Context**
   - Read the specified PRP file thoroughly
   - **Review all referenced research documentation**:
     - `research/data_models.md` - Understand existing type patterns
     - `research/ecommerce_components.md` - Study component architecture
     - `research/implementation_blueprint.md` - Review architectural decisions
     - `research/supabase_ecommerce.md` - Check database integration patterns
     - `research/validation_strategy.md` - Understand testing approaches
   - Understand all context and requirements from both PRP and research docs
   - Follow all instructions in the PRP and extend research if needed
   - Ensure you have all needed context to implement the PRP fully
   - Do more web searches and codebase exploration as needed
   - **Verify Next.js App Router patterns and current best practices**

### 2. **ULTRATHINK & Planning**
   - **Think hard before executing the plan**
   - Create a comprehensive implementation plan addressing all requirements
   - **Consider Next.js specific requirements**:
     - Server vs Client Component decisions
     - Metadata API implementation
     - Loading and error boundary strategies
     - Performance and accessibility considerations
   - Break down complex tasks into smaller, manageable steps using your todos tools
   - Use the TodoWrite tool to create and track your implementation plan
   - **Identify implementation patterns from existing code and research docs**
   - **Plan research documentation updates** for new patterns discovered
   - **Validate architectural decisions** against existing research blueprints

### 3. **Execute Implementation Plan**
   - **Follow Next.js App Router conventions**
   - **Implement TypeScript-first approach** with proper type definitions
   - **Create components following established patterns** from research docs
   - **Implement proper error handling and loading states**
   - **Follow accessibility guidelines** and responsive design principles
   - **Use existing utility functions and design system components**
   - **Implement API routes** with proper validation and error handling
   - **Add database integration** following Supabase patterns from research docs
   - **Create comprehensive tests** following validation strategy patterns

### 4. **Update Research Documentation**
   - **Document new patterns discovered** during implementation:
     - Update `research/data_models.md` with new TypeScript interfaces and schemas
     - Update `research/ecommerce_components.md` with new component patterns
     - Update `research/implementation_blueprint.md` with architectural decisions
     - Update `research/supabase_ecommerce.md` with new database patterns
     - Update `research/validation_strategy.md` with new testing approaches
   - **Ensure documentation reflects actual implementation**
   - **Add examples and gotchas discovered** during development

### 5. **Validation Loop**
   - **Run Next.js specific validation commands**:
     ```bash
     # TypeScript & Linting
     npm run type-check
     npm run lint
     npm run lint:fix  # Auto-fix what's possible
     
     # Unit Tests
     npm run test
     npm run test:watch  # For iterative development
     
     # Build Verification
     npm run build
     
     # E2E Tests (if applicable)
     npm run e2e
     
     # Performance Analysis (if configured)
     npm run analyze
     
     # Accessibility Testing (if configured)
     npm run test:a11y
     ```
   - **Fix any failures using patterns from research docs**
   - **Re-run until all validation passes**
   - **Test manually in browser**:
     - Start dev server: `npm run dev`
     - Test all user flows and interactions
     - Verify responsive design on multiple screen sizes
     - Check accessibility with keyboard navigation
     - Verify error states and loading states work correctly

### 6. **Final Verification**
   - **Ensure all PRP checklist items are completed**:
     - [ ] All code implemented according to specifications
     - [ ] TypeScript types properly defined
     - [ ] Components follow design system patterns
     - [ ] Server/Client Components used appropriately
     - [ ] Error handling and loading states implemented
     - [ ] Tests created and passing
     - [ ] Accessibility requirements met
     - [ ] Performance considerations addressed
     - [ ] Research documentation updated
   - **Run final validation suite**
   - **Verify integration with existing codebase**
   - **Check that new feature doesn't break existing functionality**
   - **Report completion status with summary of changes**

### 7. **Documentation & Handoff**
   - **Update README.md** if new dependencies or setup steps were added
   - **Document any new environment variables** or configuration changes
   - **Create migration notes** if database changes were made
   - **Summarize research documentation updates** made during implementation
   - **Note any architectural decisions** that differ from original PRP

### 8. **Quality Assurance**
   - **Re-read the PRP** to ensure 100% implementation completeness
   - **Verify all research patterns** were followed correctly
   - **Check that implementation aligns** with existing architectural decisions
   - **Confirm all validation gates pass** without workarounds
   - **Validate that new patterns** are properly documented for future use

## Critical Next.js Considerations

### **Server vs Client Components**
- Default to Server Components for better performance
- Use Client Components only when interactivity is required
- Properly handle async operations in Server Components
- Implement proper Suspense boundaries for loading states

### **Type Safety**
- Use TypeScript interfaces for all props and data structures
- Implement Zod schemas for runtime validation
- Ensure type safety across API boundaries
- Follow existing type patterns from research documentation

### **Performance**
- Optimize images with `next/image`
- Implement proper caching strategies
- Use dynamic imports for code splitting when appropriate
- Monitor bundle size impact

### **Accessibility**
- Include proper ARIA labels and roles
- Ensure keyboard navigation works correctly
- Test with screen readers when possible
- Follow WCAG guidelines

## Error Handling Patterns

### **When Validation Fails:**
1. **Read error messages carefully** - don't just fix symptoms
2. **Check research documentation** for similar error patterns
3. **Use existing error handling patterns** from codebase
4. **Update research docs** if new error patterns are discovered
5. **Re-run full validation suite** after fixes

### **When Build Fails:**
1. **Check TypeScript errors first** - fix type issues
2. **Verify import/export statements** are correct
3. **Ensure all dependencies** are properly installed
4. **Check Next.js configuration** for compatibility issues

### **When Tests Fail:**
1. **Understand the root cause** - don't mock to pass
2. **Follow existing test patterns** from research documentation
3. **Update tests if implementation** legitimately differs from expectations
4. **Ensure tests cover edge cases** and error states

## Success Criteria
- ✅ All validation commands pass without errors
- ✅ Manual testing confirms all user flows work
- ✅ Code follows established patterns from research documentation
- ✅ Research documentation updated with new patterns
- ✅ Performance and accessibility requirements met
- ✅ Integration with existing codebase is seamless
- ✅ Implementation matches PRP specifications 100%

Remember: The goal is not just working code, but maintainable, well-documented code that follows established patterns and enhances the overall codebase architecture.