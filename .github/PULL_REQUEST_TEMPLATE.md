# Pull Request

## PR Information

**Type**: <!-- Check one -->
- [ ] Feature (new functionality)
- [ ] Bug Fix (fixes an issue)
- [ ] Enhancement (improves existing functionality)
- [ ] Refactor (code improvement, no behavior change)
- [ ] Documentation (docs only)
- [ ] Test (test additions/improvements)
- [ ] Build/CI (build system, CI configuration)

**Priority**: <!-- Check one -->
- [ ] Critical (production issue, security fix)
- [ ] High (important feature, major bug)
- [ ] Medium (enhancement, minor bug)
- [ ] Low (nice-to-have, cleanup)

**Related Issue**: Fixes #[issue number] <!-- or "N/A" -->

---

## Description

### Summary

<!-- Provide a clear, concise description of the changes (2-3 sentences) -->



### Motivation and Context

<!-- Why is this change needed? What problem does it solve? -->



### What Changed?

<!-- Detailed list of changes made -->

**Files Modified**:
- `file1.ext`: Description of changes
- `file2.ext`: Description of changes

**Key Changes**:
1. Change 1 with technical details
2. Change 2 with technical details
3. Change 3 with technical details

---

## Verification & Testing

### Testing Completed

<!-- Describe all testing performed -->

#### API Testing

```bash
# Provide actual test commands and results

# Example:
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?allCustomers=true" \
  -b /tmp/cookies.txt | python -c "import sys, json; data = json.load(sys.stdin); print(f'Devices: {len(data[\"devices\"])}')"

# Result: Devices: 100
```

**Results**:
- [ ] All API tests passed
- [ ] Response format verified
- [ ] Error handling tested
- [ ] Edge cases covered

#### UI Testing

**Browser Testing**:
- [ ] Chrome (latest) - ✅ Passed
- [ ] Firefox (latest) - ✅ Passed
- [ ] Safari (if applicable)
- [ ] Mobile (if applicable)

**Theme Testing**:
- [ ] Light theme - ✅ Verified
- [ ] Dark theme - ✅ Verified
- [ ] Theme toggle works

**Console Errors**:
- [ ] No JavaScript errors in console
- [ ] No CSS warnings
- [ ] All assets load correctly

#### Live Site Verification

- [ ] Tested on production: https://mpsm.resolutionsbydesign.us/cms/
- [ ] Feature works as expected
- [ ] No regression of existing features
- [ ] Performance acceptable

**Test Account Used**: admin / admin

**Test Results**: <!-- Describe what you tested and results -->



#### Edge Cases Tested

<!-- List edge cases and results -->

1. **Edge Case 1**: Description
   - **Test**: What you tested
   - **Result**: What happened
   - **Status**: ✅ Pass / ❌ Fail

2. **Edge Case 2**: Description
   - **Test**: ...
   - **Result**: ...
   - **Status**: ...

---

## Documentation Impact

### Documentation Updated

<!-- Check all that apply -->

- [ ] Code comments added/updated
- [ ] README.md updated
- [ ] CHANGELOG.md updated (for user-facing changes)
- [ ] ADR created: [Link to ADR if applicable]
- [ ] PAIN_POINTS.md updated (if relevant)
- [ ] API documentation updated
- [ ] Architecture diagrams updated
- [ ] Onboarding guide updated

### New Documentation Created

<!-- List any new documentation files created -->

- `docs/new-doc.md`: Description

### Documentation Links

<!-- Provide links to relevant documentation -->

- [Related ADR](./docs/adr/XXXX-title.md)
- [Pain Points Entry](./docs/PAIN_POINTS.md#section)
- [Changelog Entry](./CHANGELOG.md#version)

---

## Architectural Alignment

### Alignment Verification

<!-- Verify compliance with standards -->

- [ ] Follows [CONSTITUTION.md](./docs/CONSTITUTION.md) Agent Covenant rules
- [ ] Adheres to established architecture (CMS → mps-api pattern)
- [ ] Maintains backward compatibility
- [ ] Follows coding standards (PHP PSR-12, ES6+ JavaScript, BEM CSS)
- [ ] Uses CSS variables for theming (no hardcoded colors)
- [ ] Error handling implemented (try-catch with specific messages)
- [ ] Logging uses debugLog() not console.log()

### Architecture Impact

**Does this PR change the architecture?**
- [ ] No (implementation detail only)
- [ ] Yes (architectural change) - **ADR Required**

**If Yes, describe architectural impact**:



### Backward Compatibility

**Is this a breaking change?**
- [ ] No (fully backward compatible)
- [ ] Yes (breaking change) - **Requires version bump and migration guide**

**If Yes, describe breaking changes**:



---

## Deployment

### Deployment Checklist

- [ ] All changes pushed to feature branch
- [ ] Feature branch up-to-date with main
- [ ] No merge conflicts
- [ ] Deployment script created (if applicable)
- [ ] Deployment tested successfully

### Deployment Steps

<!-- Provide deployment instructions if applicable -->

```bash
# Example deployment commands
powershell -ExecutionPolicy Bypass -File deploy-my-feature.ps1
```

### Files to Deploy

<!-- List files that need deployment -->

- `cms/assets/app.js`
- `cms/api/new-endpoint.php`
- `cms/assets/style.css`

### Rollback Plan

<!-- How to revert if deployment fails -->

```bash
# Rollback commands
git revert <commit-hash>
# Or restore specific files from previous commit
```

---

## Performance Impact

### Performance Considerations

**Does this change affect performance?**
- [ ] No performance impact
- [ ] Improves performance
- [ ] May impact performance - **Details below**

**If applicable, describe performance impact**:



### Metrics

<!-- Provide before/after metrics if applicable -->

| Metric | Before | After | Change |
|--------|--------|-------|--------|
| API Response Time | X ms | Y ms | +/- Z ms |
| Page Load Time | X ms | Y ms | +/- Z ms |
| Bundle Size | X KB | Y KB | +/- Z KB |

---

## Security Considerations

### Security Impact

- [ ] No security impact
- [ ] Improves security
- [ ] Potential security concern - **Details below**

**Security Checklist**:
- [ ] No secrets or credentials committed
- [ ] Input sanitization implemented (if user input)
- [ ] Output escaped (if rendering user data)
- [ ] SQL injection prevention (if database queries)
- [ ] XSS prevention (if HTML rendering)
- [ ] CSRF protection (if state-changing operations)

**If security concerns exist, describe them**:



---

## Dependencies

### New Dependencies

**Does this PR add new dependencies?**
- [ ] No new dependencies
- [ ] Yes - **List below**

**If yes, list dependencies**:

| Dependency | Version | Purpose | License |
|------------|---------|---------|---------|
| library-name | 1.2.3 | Purpose | MIT |

### Breaking Dependency Changes

**Does this PR update or remove dependencies?**
- [ ] No dependency changes
- [ ] Updates dependencies (non-breaking)
- [ ] Breaking dependency changes - **Requires testing**

---

## Code Quality

### Code Review Checklist

**Before requesting review, verify**:
- [ ] Code follows project style guide
- [ ] Functions are single-purpose and well-named
- [ ] No commented-out code blocks
- [ ] No debug statements (console.log, var_dump, etc.)
- [ ] Meaningful variable names (no `foo`, `bar`, `temp`)
- [ ] Complex logic has explanatory comments
- [ ] No magic numbers (use named constants)
- [ ] Error messages are helpful and specific
- [ ] No duplicate code (DRY principle)

### Technical Debt

**Does this PR introduce technical debt?**
- [ ] No technical debt
- [ ] Yes - **Justified and documented below**

**If yes, describe debt and justification**:



**Remediation Plan** (how to fix properly later):



---

## Screenshots / Recordings

### Visual Changes

**Does this PR include UI changes?**
- [ ] No UI changes
- [ ] Yes - **Screenshots below**

<!-- Attach screenshots or recordings -->

**Before**:
<!-- Screenshot of old behavior -->

**After**:
<!-- Screenshot of new behavior -->

**Mobile** (if applicable):
<!-- Mobile screenshots -->

---

## Reviewer Guidance

### Areas of Focus

<!-- Guide reviewers to areas needing special attention -->

**Please pay special attention to**:
1. Area 1: Why this needs careful review
2. Area 2: Specific concerns or questions
3. Area 3: Complex logic or edge cases

### Known Limitations

<!-- Be transparent about limitations -->

- Limitation 1: Description and why it exists
- Limitation 2: Description and future work needed

### Questions for Reviewers

<!-- Ask specific questions -->

1. Question 1?
2. Question 2?

---

## Checklist

### Pre-Submit Checklist

**I verify that**:
- [ ] All commits follow conventional commit format
- [ ] Commit messages are clear and descriptive
- [ ] All tests pass (or test plan documented if no automated tests)
- [ ] Documentation updated
- [ ] CHANGELOG.md updated (for user-facing changes)
- [ ] No merge conflicts with main branch
- [ ] Code reviewed by myself first (self-review)
- [ ] Screenshots attached (if UI changes)
- [ ] Performance impact assessed
- [ ] Security impact assessed
- [ ] Deployment plan documented
- [ ] Rollback plan documented

### Constitutional Compliance

**I confirm compliance with**:
- [ ] Agent Covenant Rule 1: Documentation First
- [ ] Agent Covenant Rule 2: Test Before Deploy
- [ ] Agent Covenant Rule 3: Never Break the Build
- [ ] Agent Covenant Rule 4: Preserve Engineering Standards
- [ ] Agent Covenant Rule 5: Backward Compatibility
- [ ] Agent Covenant Rule 6: Security First
- [ ] Agent Covenant Rule 7: Atomic Commits
- [ ] Agent Covenant Rule 9: Clean Up After Yourself

---

## Additional Notes

<!-- Any other information reviewers should know -->



---

## Post-Merge Tasks

<!-- Tasks to complete after merge -->

- [ ] Monitor production for errors (24 hours)
- [ ] Update related documentation (if not already done)
- [ ] Notify stakeholders of changes
- [ ] Close related issues
- [ ] Delete feature branch

---

**Template Version**: 1.0.0
**PR Created**: <!-- Auto-filled by GitHub -->
**Last Updated**: <!-- Date of last update to PR -->
