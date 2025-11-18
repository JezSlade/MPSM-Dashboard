# Decisions Log

Architectural decisions, workflow changes, and methodology choices for MPSM Dashboard project.

## Deployment Methodology

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | GitHub Actions primary deployment | Automated CI/CD reduces human error, provides audit trail, consistent process | `.github/workflows/deploy.yml` |
| 2025-11-17 | FTP deployment to GreenGeeks | Hosting provider requirement, supports both automated and manual fallback | `context/deployment-guide.md` |
| 2025-11-17 | SSH/Git pull as fallback | Emergency access when GitHub Actions unavailable, direct server control | `DEPLOY-INSTRUCTIONS.md:14-17` |
| 2025-11-17 | Never deploy without user approval | Safety guardrail prevents accidental production changes | `AGENTS.md` Section 8 |
| 2025-11-17 | Always verify live after deploy | Hard refresh + test ensures actual production state matches expected | `AGENTS.md` Section 20 |

## Workflow Protocol

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | Plan-first workflow | Prevents scope creep, enables user approval before execution | `CLAUDE.md`, `AGENTS.md` Section 6 |
| 2025-11-17 | Root cause analysis required | Fixes underlying issues not symptoms, prevents recurring bugs | `AGENTS.md` Section 6 Step 3 |
| 2025-11-17 | Regression shields mandatory | Identifies risk areas before changes, prevents breaking existing features | `AGENTS.md` Section 10 |
| 2025-11-17 | User confirmation before "done" | Ensures live production behavior matches expectations, no false completion | `AGENTS.md` Section 6 Step 12 |
| 2025-11-17 | Context vault in `context/` | Persistent memory across sessions, enables handoffs between agents | `AGENTS.md` Section 2 |

## Tool Selection Criteria

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | Task tool for exploration | Reduces context usage, handles multi-round searches autonomously | `AGENTS.md` Section 17 |
| 2025-11-17 | Direct tools for known targets | Faster response when exact path/pattern known | `AGENTS.md` Section 17 |
| 2025-11-17 | Specialized tools over bash | Better user experience, proper permissions, optimized for task | `AGENTS.md` Section 17 |
| 2025-11-17 | Parallel tool calls when independent | Maximizes performance, reduces latency for info gathering | `AGENTS.md` Section 17 |
| 2025-11-17 | Sequential when dependencies exist | Prevents errors from missing dependencies, ensures correct data flow | `AGENTS.md` Section 17 |

## Git and Version Control

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | Never commit without user request | User controls version history, prevents unauthorized commits | `AGENTS.md` Section 19 |
| 2025-11-17 | Git safety protocol | Prevents destructive operations, protects repository integrity | `AGENTS.md` Section 19 |
| 2025-11-17 | Heredoc commit messages | Ensures proper formatting with Claude Code attribution | `AGENTS.md` Section 19 |
| 2025-11-17 | Never commit secrets | Security best practice, prevents credential exposure | `AGENTS.md` Section 19 |
| 2025-11-17 | Avoid force push to main | Protects production branch from history rewriting | `AGENTS.md` Section 19 |

## Communication and Style

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | Terse, concise responses | CLI environment, reduces noise, focuses on action | `CLAUDE.md`, `AGENTS.md` Section 4 |
| 2025-11-17 | No emojis unless requested | Professional tone, not distracting in CLI context | `AGENTS.md` Section 17 |
| 2025-11-17 | Code over prose | Token efficiency, executable artifacts more valuable than explanation | `AGENTS.md` Section 5 |
| 2025-11-17 | One clarifying question only | Avoids analysis paralysis, enables progress with reasonable assumptions | `AGENTS.md` Section 4 |
| 2025-11-17 | Direct text output (no bash echo) | Proper communication channel, bash is for system commands only | `AGENTS.md` Section 17 |

## TodoWrite Usage

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | One in_progress task only | Clear focus, prevents context switching, tracks actual current work | `AGENTS.md` Section 18 |
| 2025-11-17 | Immediate completion marking | Accurate progress tracking, prevents batch updates that obscure status | `AGENTS.md` Section 18 |
| 2025-11-17 | Two-form task descriptions | Shows both goal (content) and active state (activeForm) for clarity | `AGENTS.md` Section 18 |
| 2025-11-17 | Use for 3+ step tasks | Balances planning overhead with tracking value | `AGENTS.md` Section 18 |
| 2025-11-17 | Skip for trivial tasks | Avoids unnecessary tracking overhead | `AGENTS.md` Section 18 |

## File Operations

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | Full files only, never snippets | Ensures complete context, prevents partial/broken code | `AGENTS.md` Section 4 |
| 2025-11-17 | Read before edit/write | Prevents overwrites, enables intelligent merging | `AGENTS.md` Section 17 |
| 2025-11-17 | Prefer edit over create | Maintains existing files, reduces clutter | `AGENTS.md` Section 17 |
| 2025-11-17 | Changelogs in all modified files | Audit trail, documents why changes made | `AGENTS.md` Section 11 |
| 2025-11-17 | Never read secrets without approval | Security best practice, respects sensitive data | `AGENTS.md` Section 7 |

## Testing and Verification

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | Test against live production | Validates actual deployed state, catches environment-specific issues | `AGENTS.md` Section 20 |
| 2025-11-17 | Document tests in test-log.md | Audit trail, enables verification of test coverage | `AGENTS.md` Section 9 |
| 2025-11-17 | Hard refresh after deployment | Clears browser cache, ensures seeing actual new version | `AGENTS.md` Section 20 |
| 2025-11-17 | Performance targets defined | Quantifiable success criteria (3s dashboard, 500ms modal) | `AGENTS.md` Section 20 |
| 2025-11-17 | Check error logs post-deploy | Catches silent failures, verifies no new errors introduced | `AGENTS.md` Section 20 |

## Project Architecture

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-09 | CRON hourly instead of 5-min | Prevents cache truncation loop, allows full 30-min refresh cycle | `context/deployment-guide.md:216-225` |
| 2025-11-17 | Cache-first architecture | Reduces API load, improves dashboard performance from 15s to <3s | `context/deployment-guide.md` |
| 2025-11-17 | Database indexes on cache tables | Speeds up queries 10-100x, enables fast device lookups | `database_optimizations.sql` |
| 2025-11-17 | Context vault pattern | Persistent memory across sessions, enables multi-agent collaboration | `AGENTS.md` Section 2 |
| 2025-11-17 | OOP size limits (800 LOC files, 60 LOC methods) | Maintains code readability, prevents god classes/methods | `AGENTS.md` Section 4 |

## CODEX Validator Role

| Date | Change | Rationale | Reference |
| ---- | ------ | --------- | --------- |
| 2025-11-17 | CODEX blocks completion until verified | Quality gate, ensures changes actually work in production | `AGENTS.md` Section 23 |
| 2025-11-17 | Security vulnerability checks | Prevents XSS, SQL injection, command injection from reaching prod | `AGENTS.md` Section 23 |
| 2025-11-17 | Plan alignment verification | Prevents scope creep, ensures changes match approved plan | `AGENTS.md` Section 23 |
| 2025-11-17 | Changelog verification | Ensures audit trail exists for all changes | `AGENTS.md` Section 23 |

## Version History

| Date | Version | Change | Notes |
| ---- | ------- | ------ | ----- |
| 2025-11-17 | AGENTS v1.0.1 | Core workflow updated | FTP-based deploys/tests on live server before verification |
| 2025-11-17 | AGENTS v1.1.0 | Comprehensive knowledge base | Added tool usage, git policy, testing, CODEX instructions, project knowledge |
