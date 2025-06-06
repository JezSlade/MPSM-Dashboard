# AI Development Guidelines - README

## Overview

This document establishes strict development guidelines for AI assistance with this codebase. These rules exist to prevent bugs, maintain code quality, and ensure system stability. **All rules must be followed without exception.**

## Core Development Rules

### 1. Complete File Delivery
- **Always return full, complete source files in their entirety**
- Never provide partial snippets or abbreviated code
- Include all imports, dependencies, and complete function implementations
- Ensure files are immediately usable without reconstruction

### 2. Comprehensive Documentation
- **Include line-by-line comments for every function, logic branch, and change**
- Explain the purpose and flow of each code section
- Document why decisions were made, not just what the code does
- Comment all conditional logic, loops, and complex operations
- Explain any business logic or domain-specific rules

### 3. Security and Secrets Management
- **Never hardcode secrets, API keys, passwords, or credentials**
- Use the `.env` file for all sensitive configuration values
- Reference environment variables using proper syntax for the language
- If a required secret is missing from `.env`, halt and prompt for it
- Never commit or expose sensitive data in code comments or logs

### 4. Dependency Management
- **Do not introduce or install external dependencies** (Composer packages, npm modules, etc.)
- Work only with existing dependencies in the project
- If new dependencies are required, explicitly request authorization first
- Document any dependency requirements clearly

### 5. Logic Fidelity
- **Follow the exact logic in provided "working" files** (e.g., working_auth.txt, working.php)
- Do not deviate from proven working implementations
- Never guess or assume how something should work
- If working examples exist, use them as the definitive reference
- Preserve existing algorithms and business rules exactly

### 6. API Integration Standards
- **Use schemas in AllEndpoints.json verbatim**
- Copy HTTP methods, field names, and payload structures exactly
- Never assume API behavior or modify endpoint specifications
- Validate all API calls against the provided schemas
- If schema information is missing, request it before proceeding

### 7. Code Organization
- **Modularize shared code effectively**
- Centralize reusable functions, CSS, and templates
- Eliminate code duplication across files
- Create utility functions for common operations
- Maintain consistent file organization and naming

### 8. Requirement Clarification
- **Ask concise clarifying questions when requirements are unclear**
- Never proceed with ambiguous or incomplete specifications
- Confirm understanding before implementing changes
- Request specific examples when behavior is uncertain

### 9. Styling and Theme Management
- **When styling is requested, modify only theme files**
- Leave existing layout and markup structures untouched
- Do not alter HTML structure unless specifically instructed
- Preserve responsive design and accessibility features
- Test styling changes across different screen sizes

### 10. Defensive Programming
- **Write code defensively with comprehensive error handling**
- Validate all inputs before processing
- Check for null values and handle edge cases
- Implement proper error handling and recovery
- Never allow runtime exceptions to crash the application
- Use try-catch blocks appropriately

### 11. Communication Standards
- **Never use em-dashes, emojis, or weak AI-speak**
- Keep all comments and messages plain and direct
- Use clear, professional language in documentation
- Avoid unnecessary verbosity or flowery language
- Write for clarity and maintainability

### 12. Change Impact Assessment
- **Analyze potential breaking changes before implementation**
- Run through existing test cases when available
- Document exactly why changes are safe if they appear risky
- Identify components that might be affected by modifications
- Preserve backward compatibility unless explicitly told otherwise

### 13. Debug and Logging Standards
- **Log debug information consistently and comprehensively**
- Include timestamps in all log entries
- Provide sufficient context for debugging issues
- Include error details and stack traces when appropriate
- Use existing debug panels or logging utilities
- Never leave debug code in production without proper controls

### 14. Code Style Consistency
- **Respect existing codebase conventions exactly**
- Mirror current indentation style (spaces vs tabs, width)
- Follow established naming conventions for variables, functions, and classes
- Match existing file structure and organization patterns
- Preserve comment styles and formatting

### 15. Code Delivery Format
- **Do not package or link to zip files**
- Deliver all code inline as plain text
- Ensure code is immediately copy-pasteable
- Format code properly for readability
- Include proper syntax highlighting markers when applicable

## Enforcement Guardrails

### Rule Violation Protocol
- **If any proposed edit conflicts with the above rules, reject it immediately**
- Clearly explain which specific rule was violated
- Provide the correct approach that follows the guidelines
- Do not proceed until the conflict is resolved

### Missing Dependencies Protocol
- **If secrets or schemas are missing, halt execution immediately**
- Prompt specifically for the missing file or configuration value
- Do not attempt to guess or substitute missing information
- Wait for explicit provision of required resources

### API Uncertainty Protocol
- **If unsure about API behavior, flag the uncertainty**
- Never guess API specifications or behavior
- Request clarification or additional documentation
- Prefer asking questions over making assumptions

### Critical Component Protection
- **Always confirm before touching critical layout or logic components**
- Identify components that could affect system stability
- Get explicit approval for changes to core functionality
- Document the scope and impact of proposed changes

### Code Quality Assurance
- **Any code that fails to compile or test must be rolled back**
- Annotate the failure reason clearly before resubmission
- Fix compilation errors before delivering code
- Test basic functionality when possible
- Ensure code meets language syntax requirements

## Implementation Checklist

Before delivering any code changes, verify:

- [ ] All files are complete and immediately usable
- [ ] Every function and logic branch is commented
- [ ] No secrets or credentials are hardcoded
- [ ] No unauthorized dependencies were added
- [ ] Existing working logic was preserved exactly
- [ ] API calls match AllEndpoints.json specifications
- [ ] Shared code is properly modularized
- [ ] All requirements are clearly understood
- [ ] Only theme files were modified for styling changes
- [ ] Defensive programming practices are implemented
- [ ] Language is plain and direct throughout
- [ ] Change impact has been assessed
- [ ] Debug logging is comprehensive and consistent
- [ ] Code style matches existing conventions
- [ ] Code is delivered as plain text inline

## Failure Recovery

If any guideline is violated:

1. **Stop immediately** and identify the violation
2. **Explain** which rule was broken and why
3. **Provide** the correct approach following guidelines
4. **Restart** the implementation using proper methodology
5. **Verify** compliance before final delivery

## Summary

These guidelines exist to ensure code quality, system stability, and development efficiency. They are not suggestions but mandatory requirements for all AI-assisted development work. Following these rules prevents bugs, maintains consistency, and protects the integrity of the codebase.

**Remember: When in doubt, ask for clarification rather than making assumptions.**