# MPSM Dashboard - Project Constitution

**Version**: 1.0.0
**Last Updated**: 2025-10-31
**Status**: Active

---

## Preamble

This Constitution serves as the supreme governing document for the MPSM Dashboard project. All agents, developers, and contributors must acknowledge and adhere to these principles, rules, and processes. This document establishes the foundational standards that ensure consistency, quality, and maintainability across the project lifecycle.

---

## Table of Contents

1. [Core Guides](#core-guides)
2. [The Agent Covenant](#the-agent-covenant)
3. [Project Architecture](#project-architecture)
4. [Development Standards](#development-standards)
5. [Amendment Process](#amendment-process)
6. [Enforcement](#enforcement)

---

## Core Guides

All contributors must read and follow these core guides:

### Essential Documentation
- [Architecture Guide](./ARCHITECTURE.md) - System design and component interactions
- [Style Guide](./STYLE_GUIDE.md) - Code formatting and naming conventions
- [API Documentation](./API.md) - Backend API endpoints and contracts
- [Onboarding Guide](./ONBOARDING.md) - Setup and getting started
- [Pain Points](./PAIN_POINTS.md) - Known issues and workarounds

### Process Documentation
- [Handoff Protocol](./HANDOFF_PROTOCOL.md) - Agent transition procedures
- [PR Process](../.github/PULL_REQUEST_TEMPLATE.md) - Pull request requirements
- [Changelog](../CHANGELOG.md) - Version history and changes

### Decision Records
- [ADR Index](./adr/README.md) - Architectural Decision Records

---

## The Agent Covenant

These rules are **non-negotiable** and must be followed by all agents and contributors:

### 1. Documentation First
**Rule**: All code changes MUST be accompanied by documentation updates.

**Requirements**:
- Update relevant .md files in `/docs` when changing architecture
- Update API documentation when adding/modifying endpoints
- Update CHANGELOG.md for all user-facing changes
- Add ADRs for significant architectural decisions

**Enforcement**: PRs without documentation updates will be rejected by CI/CD.

---

### 2. Test Before Deploy
**Rule**: All changes MUST be tested on the live site before marking complete.

**Requirements**:
- Test API endpoints with curl or test harness
- Verify UI changes in browser (both light and dark themes)
- Check browser console for errors (F12)
- Test edge cases and error scenarios
- Document test results in commit message or PR

**Enforcement**: No code review approval without test verification.

---

### 3. Never Break the Build
**Rule**: All commits MUST maintain a working state.

**Requirements**:
- Code must pass all linting checks
- No syntax errors or undefined variables
- All file references must be valid
- API responses must maintain backward compatibility
- Frontend must load without JavaScript errors

**Enforcement**: CI/CD pipeline will reject builds that fail checks.

---

### 4. Preserve Engineering Standards
**Rule**: Follow the established engineering standards documented in the codebase.

**Requirements**:
- **Rule 23**: Use CSS variables for theming (defined in style.css)
- **Rule 24**: BEM naming convention for CSS classes
- **Separation of Concerns**: CMS = presentation, mps-api = API proxy
- **No Direct API Calls**: Frontend calls CMS API, CMS calls mps-api backend
- **Error Handling**: Always include try-catch with specific error messages
- **Logging**: Use debugLog() for troubleshooting, include severity levels

**Enforcement**: Code review will verify standards compliance.

---

### 5. Backward Compatibility
**Rule**: API changes MUST maintain backward compatibility.

**Requirements**:
- Add new parameters with default values
- Never remove existing API fields
- Deprecate features gradually with warnings
- Version API endpoints if breaking changes required
- Document migration path for deprecated features

**Enforcement**: Breaking changes require explicit approval and ADR.

---

### 6. Security First
**Rule**: Never commit secrets, credentials, or sensitive data.

**Requirements**:
- Use environment variables for credentials
- Never commit .env files
- Rotate credentials if accidentally committed
- Use secure FTP/HTTPS for deployments
- Sanitize user input and escape HTML output

**Enforcement**: Pre-commit hooks scan for secrets; GitHub Dependabot monitors vulnerabilities.

---

### 7. Atomic Commits
**Rule**: Each commit MUST represent a single logical change.

**Requirements**:
- One feature/fix per commit
- Clear, descriptive commit messages
- Include "why" not just "what" in message
- Reference issue numbers when applicable
- Use conventional commit format

**Example**:
```
Fix global search to find devices across all customers

- Added 'allCustomers' parameter to API
- Implemented pagination to fetch all devices
- Extended search fields to include ExternalIdentifier

Fixes #123
```

**Enforcement**: PRs with bundled unrelated changes will be rejected.

---

### 8. Code Review Mandatory
**Rule**: No direct commits to main branch; all changes via PR.

**Requirements**:
- Create feature branch for changes
- Fill out PR template completely
- Request review from maintainer
- Address all review comments
- Squash commits before merge if requested

**Enforcement**: Branch protection rules enforce PR requirement.

---

### 9. Clean Up After Yourself
**Rule**: Remove dead code, debug statements, and temporary files.

**Requirements**:
- Delete commented-out code blocks
- Remove console.log() debug statements (use debugLog() instead)
- Delete unused functions and variables
- Remove temporary test files
- Clean up deployment scripts after use

**Enforcement**: Code review will flag violations.

---

### 10. Respect the Handoff Protocol
**Rule**: Outgoing agents MUST complete handoff checklist before disengaging.

**Requirements**:
- Create handoff issue using template
- Document current state and next actions
- List known pain points and blockers
- Push all local changes to repository
- Tag incoming agent or maintainer

**Enforcement**: Incomplete handoffs may result in rework penalties.

---

## Project Architecture

### High-Level Architecture

```
┌─────────────────┐
│   Browser       │
│   (Frontend)    │
└────────┬────────┘
         │ HTTP
         ▼
┌─────────────────┐
│   CMS           │  ← Presentation Layer
│   (PHP/JS)      │  ← Dashboard UI, Login, Session
└────────┬────────┘
         │ HTTP POST (OAuth)
         ▼
┌─────────────────┐
│   mps-api       │  ← API Proxy Layer
│   (Backend)     │  ← Device/List, Query endpoint
└────────┬────────┘
         │ OAuth Token
         ▼
┌─────────────────┐
│   HP SDS API    │  ← External Service
│   (HP Platform) │  ← Device data source
└─────────────────┘
```

### Key Principles

1. **Separation of Concerns**:
   - CMS handles presentation and user interaction
   - mps-api handles authentication and API proxying
   - No direct calls from frontend to external APIs

2. **Stateless Authentication**:
   - OAuth token cached in PHP static variables
   - Token refresh handled transparently
   - Session management via PHP sessions

3. **Progressive Enhancement**:
   - Core functionality works without JavaScript
   - JavaScript enhances UX with dynamic updates
   - Graceful degradation for older browsers

4. **Theme Support**:
   - CSS variables for light/dark themes
   - User preference saved in localStorage
   - Automatic theme detection from system

---

## Development Standards

### File Organization

```
MPSM-Dashboard/
├── cms/                    # Frontend application
│   ├── api/               # API endpoints (proxy to mps-api)
│   │   ├── login.php      # Authentication
│   │   ├── get-devices.php # Device list with pagination
│   │   └── get-supply-alerts.php
│   ├── assets/            # Static assets
│   │   ├── app.js         # Main application logic (3400+ lines)
│   │   ├── js/
│   │   │   └── card-registry.js  # Dashboard cards
│   │   └── style.css      # Styles with CSS variables
│   ├── config.php         # Configuration constants
│   ├── functions.php      # Shared utilities
│   └── index.php          # Main entry point
├── mps-api/               # Backend API (separate deployment)
│   └── query              # Query endpoint
├── docs/                  # Documentation
│   ├── CONSTITUTION.md    # This file
│   ├── ARCHITECTURE.md    # Architecture guide
│   ├── adr/              # Architectural Decision Records
│   └── ...
└── .github/              # GitHub configuration
    └── PULL_REQUEST_TEMPLATE.md
```

### Coding Standards

#### PHP
- Use strict types: `declare(strict_types=1);`
- PSR-12 coding standard
- Type hints for function parameters and returns
- Error handling with try-catch and specific exceptions
- Use constants for configuration (defined in config.php)

#### JavaScript
- ES6+ syntax (arrow functions, const/let, template literals)
- Async/await for asynchronous operations
- Debug logging with `debugLog(message, level)`
- Use `state` object for application state
- Modular functions (single responsibility principle)

#### CSS
- CSS variables for theming (`:root` and `[data-theme="dark"]`)
- BEM naming convention for classes
- Mobile-first responsive design
- Flexbox/Grid for layouts
- Smooth transitions for interactive elements

#### Naming Conventions
- **Functions**: camelCase (`fetchAllDevices`, `openDeviceModal`)
- **Variables**: camelCase (`deviceList`, `totalCount`)
- **Constants**: UPPER_SNAKE_CASE (`DEFAULT_CUSTOMER_CODE`, `API_TIMEOUT`)
- **CSS Classes**: kebab-case with BEM (`.device-list__item--active`)
- **Files**: kebab-case for new files (`device-modal.js`)

### Performance Standards

- **API Response Time**: < 5 seconds for device list
- **Page Load Time**: < 3 seconds for dashboard
- **Search Response**: < 500ms for autocomplete
- **Bundle Size**: Keep app.js under 500KB
- **API Caching**: Cache device data for 1 minute

### Accessibility Standards

- Semantic HTML5 elements
- ARIA labels for interactive elements
- Keyboard navigation support
- Focus indicators visible
- Color contrast ratio WCAG AA compliant

---

## Amendment Process

### Proposing Amendments

1. **Identify Need**: Recognize a gap or issue with current constitution
2. **Draft Amendment**: Write proposed changes in ADR format
3. **Discuss**: Present to team/maintainer for feedback
4. **Vote/Approve**: Obtain approval from project maintainer
5. **Implement**: Update CONSTITUTION.md and related docs
6. **Announce**: Notify all active agents of changes

### Amendment Criteria

Amendments must meet these criteria:
- **Necessary**: Addresses real project need
- **Clear**: Unambiguous and enforceable
- **Aligned**: Consistent with existing principles
- **Practical**: Feasible to implement and verify

### Amendment History

| Version | Date | Changes | Approved By |
|---------|------|---------|-------------|
| 1.0.0 | 2025-10-31 | Initial constitution | Project Maintainer |

---

## Enforcement

### Automated Enforcement

1. **Pre-Commit Hooks**:
   - Scan for secrets (API keys, passwords)
   - Run linters (ESLint, PHP-CS-Fixer)
   - Check file sizes

2. **CI/CD Pipeline**:
   - Build verification
   - Test execution
   - Documentation checks
   - PR template compliance

3. **Branch Protection**:
   - Require PR reviews
   - Require status checks to pass
   - Restrict direct commits to main

### Manual Enforcement

1. **Code Review**:
   - Architectural alignment verification
   - Standards compliance check
   - Documentation completeness check

2. **Handoff Review**:
   - Verify handoff checklist completion
   - Confirm documentation updates
   - Test state validation

### Violation Handling

**Severity Levels**:
- **Critical**: Security issues, data loss, breaking changes
- **High**: Standards violations, missing documentation
- **Medium**: Code quality issues, minor deviations
- **Low**: Style inconsistencies, typos

**Process**:
1. **Identify**: Violation discovered in review or CI/CD
2. **Document**: Log in Protocol Violation Log (blameless)
3. **Notify**: Inform responsible agent
4. **Remediate**: Fix violation in follow-up PR
5. **Learn**: Update documentation or tooling to prevent recurrence

---

## Appendix: Quick Reference

### Pre-Flight Checklist

Before starting work:
- [ ] Read CONSTITUTION.md (this file)
- [ ] Review ARCHITECTURE.md
- [ ] Check PAIN_POINTS.md for known issues
- [ ] Read recent ADRs in `/docs/adr/`
- [ ] Review CHANGELOG.md for recent changes

### Pre-Commit Checklist

Before committing:
- [ ] Code passes linters (no errors)
- [ ] All console.log() removed (use debugLog())
- [ ] Documentation updated
- [ ] CHANGELOG.md updated (if user-facing)
- [ ] Commit message follows convention

### Pre-PR Checklist

Before creating PR:
- [ ] All commits pushed to feature branch
- [ ] PR template filled out completely
- [ ] Tests documented in PR
- [ ] Screenshots attached (if UI changes)
- [ ] Related issues linked

### Pre-Handoff Checklist

Before disengaging:
- [ ] All local changes pushed
- [ ] Handoff issue created using template
- [ ] Current state documented
- [ ] Next actions listed with priority
- [ ] Pain points documented
- [ ] Incoming agent tagged

---

## Signature & Acknowledgment

By contributing to this project, you acknowledge that you have read, understood, and agree to abide by this Constitution and all referenced documents.

**Constitutional Authority**: Project Maintainer
**Effective Date**: 2025-10-31
**Review Cycle**: Quarterly

---

*This Constitution is a living document and will evolve with the project. Propose amendments via ADR process.*
