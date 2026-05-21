# Architectural Decision Records (ADRs)

This directory contains Architectural Decision Records (ADRs) for the MPSM Dashboard project.

## What is an ADR?

An ADR is a document that captures an important architectural decision made along with its context and consequences.

## Format

Each ADR follows this format:

```markdown
# ADR-XXXX: [Title]

**Status**: [Proposed | Accepted | Deprecated | Superseded]
**Date**: YYYY-MM-DD
**Deciders**: [Names or roles]
**Related ADRs**: [Links to related ADRs]

## Context

What is the issue we're facing? What factors are driving this decision?

## Decision

What is the change we're proposing?

## Consequences

What becomes easier or more difficult as a result of this change?

### Positive
- Benefit 1
- Benefit 2

### Negative
- Trade-off 1
- Trade-off 2

### Neutral
- Impact 1

## Implementation

How will this decision be implemented?

## Alternatives Considered

What other options did we evaluate?
```

## Index of ADRs

| ADR | Title | Status | Date |
|-----|-------|--------|------|
| [0001](./0001-cms-api-separation.md) | CMS and API Separation | Accepted | 2025-10-31 |
| [0005](./0005-global-search-pagination.md) | Global Search Pagination Approach | Accepted | 2025-10-31 |

Only the ADR files listed above exist in the current repository. Historical references to untracked ADRs, including a PowerShell FTP deployment ADR, are not current source of truth. Current deployment guidance is in `docs/operations/DEPLOY-INSTRUCTIONS.md` and `context/deployment-guide.md`.

## Creating a New ADR

1. Copy the template above to a new file: `docs/adr/XXXX-title.md`
2. Use the next sequential number (check the index above)
3. Fill out all sections
4. Submit as part of your PR
5. Update this README.md index

## When to Create an ADR

Create an ADR when:
- Making a decision that affects the architecture
- Choosing between multiple technical approaches
- Establishing a new pattern or convention
- Deprecating an existing approach
- Making a decision with long-term implications

## ADR Lifecycle

1. **Proposed**: Under discussion
2. **Accepted**: Decision made and implemented
3. **Deprecated**: No longer recommended but still in use
4. **Superseded**: Replaced by another ADR
