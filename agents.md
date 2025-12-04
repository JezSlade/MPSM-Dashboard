# AGENTS.md

Repository-standard rules for any AI agent operating in this workspace. Applies to Claude Code, Copilot Chat, ChatGPT extensions, and other validators.

Version: 1.1.0
Owner: Jez
Scope: Entire repository
Last Updated: 2025-11-17

---

## 1) Purpose
Establish one consistent, auditable workflow inside VS Code so multiple agents can build, validate, and ship changes without regressions or confusion.

## 2) Directory conventions
- `context/` is the persistent memory vault. Agents must read on start and write summaries on stop.
  - `context/index.md` overview of the project
  - `context/session.md` rolling session notes
  - `context/test-log.md` test outputs and links
  - `context/deploy-log.md` deploy commands, timestamps, results
  - `context/decisions.md` accepted ADRs and rationale
- `.claude/` holds Claude-specific styles, commands, and settings.
- `.github/` may hold Copilot instructions.

## 3) Roles
- Builder Agent
  - Implements approved plans. Writes full files. Keeps edits small and cohesive. Never marks fixed without user confirmation on LIVE.
- Validator Agent
  - Performs alignment, checks plans for scope creep, verifies patch diffs, runs tests, reviews logs, blocks deploy until criteria are met, and requires explicit user sign off before “done”.

## 4) Global guardrails
- Style: terse, precise. One clarifying question only if ambiguity would change the deliverable. No emojis. No em dashes.
- Outputs: full files only. For 1–4 files, emit as separate artifacts. If 5 or more are required, propose a zip and await approval.
- No placeholders or stubs. If inputs are missing, stop and ask.
- Paths: always relative. Global CSS lives at `/public/css/styles.css`.
- OOP size and complexity limits
  - File: target 200–400 LOC; split at 800 plus
  - Class: target 150–300 LOC; one public class per file by default
  - Method: average 15–25 LOC; hard ceiling 60; cyclomatic complexity target 5, max 10
- Changelogs: every modified file appends a changelog at the very end, after the last closing bracket.

## 5) Token discipline
- Prefer code over prose. Keep narration short. Move long analysis into `context/notes.md` and reference it.
- Open files selectively. List and grep before opening large trees.
- When token pressure rises, compress commentary, reduce open file count, and chunk work.

## 6) Standard workflow protocol
All agents follow these exact steps for each task:
1. Analyze context: read `context/*.md` and any task inputs.
2. Align: restate goal, constraints, acceptance criteria.
3. Root cause analysis: state hypotheses and the primary driver to address.
4. Plan patch: enumerate files to touch, minimal changes, tests, and rollback.
5. Refine plan: add regression shields and narrow scope.
6. Scope check: verify alignment with architecture and guardrails.
7. Approval gate: wait for user approval to implement.
8. Execute patch: write complete files. Keep changes small and cohesive.
9. Deploy gate: do not deploy without explicit user approval and command. Upload code via FTP per Section 8 (no git push deploys).
10. Test LIVE: run the updated scripts/endpoints directly on the production site after the FTP sync; capture outputs in `context/test-log.md`.
11. Verify: confirm no errors, no regressions, all functions intact.
12. Close: do not mark fixed until the user confirms in chat that LIVE behaves as expected.

## 7) File I O policy
- Do not echo large code back to chat. Write to disk and produce a compact verification summary: files changed, key behaviors, follow ups.
- Never read secrets without explicit instruction. Avoid `.env*`, `secrets/**`, and credentials.

## 8) Deployment policy

### Primary Method: GitHub Actions (Automatic FTP)
- **Trigger:** Push to `main` branch automatically deploys via FTP
- **Workflow:** `.github/workflows/deploy.yml`
- **Duration:** 2-5 minutes
- **Monitor:** https://github.com/JezSlade/MPSM-Dashboard/actions
- **FTP Server:** ftp.resolutionsbydesign.us
- **FTP User:** mpsm@mpsm.resolutionsbydesign.us
- **FTP Password:** Deploy123!
- **Target:** Web root `/`

### Standard Deployment Process
```bash
# 1. Commit changes (Claude Code can do this)
git add .
git commit -m "Description

🤖 Generated with Claude Code

Co-Authored-By: Claude <noreply@anthropic.com>"

# 2. Push to trigger automatic deployment (USER MUST DO THIS MANUALLY)
# ⚠️ CRITICAL: Claude Code CANNOT push due to authentication requirements
# User must execute in their terminal:
git push origin main

# 3. Monitor deployment
# Go to: https://github.com/JezSlade/MPSM-Dashboard/actions
# Wait for green checkmark (2-5 minutes)

# 4. Verify live site
# Visit: https://mpsm.resolutionsbydesign.us/cms/
# Hard refresh: Ctrl+Shift+R or Ctrl+F5
```

### Troubleshooting: Changes Not Deployed

**Symptom**: Live site doesn't reflect committed changes

**Diagnosis**:

```bash
# Check if commits are local only
git status
# Look for: "Your branch is ahead of 'origin/main' by X commits"
```

**Root Cause**: Commits exist locally but weren't pushed to GitHub

**Fix**: User must manually push from terminal

```bash
cd /home/jez/projects/MPSM-Dashboard
git push origin main
```

**Why Claude Code Can't Push**:

- Requires GitHub authentication (username/password or SSH key)
- Claude Code runs in sandboxed environment without credentials
- Security design: prevents automated unauthorized pushes

### Alternative Methods (Fallback Only)
1. **SSH/Git Pull** (manual):
   ```bash
   ssh resolut7@mpsm.resolutionsbydesign.us
   cd public_html
   git pull origin main
   ```

2. **PowerShell FTP Scripts** (hotfix):
   ```powershell
   .\deploy-critical-fix.ps1
   .\deploy-all.ps1
   ```

3. **HTTP Deploy Endpoint** (if configured):
   ```bash
   curl "https://mpsm.resolutionsbydesign.us/deploy.php?secret=..."
   ```

### Deployment Rules
- All deploys require explicit user approval
- Record exact command, timestamp, result in `context/deploy-log.md`
- Never deploy without user confirmation
- Never push to main without user request
- Always verify live site after deployment (hard refresh)
- Check error logs after deployment: `cms/logs/php_errors.log`

## 9) Testing policy
- Builders provide a minimal test plan with the patch. Validators execute tests and attach logs to `context/test-log.md`.
- If a failure occurs, stop, capture artifacts, and propose a rollback.
- All verification happens against the live server. After each FTP deploy, invoke the relevant production endpoints/scripts (curl, browser, CLI) to validate behavior; document those live checks in `context/test-log.md`.

## 10) Regression policy
- Identify adjacent risk areas before edits. After patch, run smoke checks on those areas. If risk is high, propose a split and staged rollout.

## 11) Output contract
Every modified source file must end with a changelog block. Example:
```
/*
CHANGELOG
2025-11-10 Jez
- Implemented X
- Refactored Y into Z
- Added input validation and unit tests
*/
```

## 12) Permissions policy
- Honor repository settings under `.claude/settings.json` and equivalent tool settings. If a command is not explicitly allowed, ask.
- Dangerous operations like `git push`, deploy, or database migrations require user consent and a recorded log entry.

## 13) Tool mappings
- Claude Code: obey `CLAUDE.md`, `.claude/output-styles/`, `.claude/commands/`, and `.claude/settings.json`.
- Copilot Chat: if present, mirror these rules in `.github/copilot-instructions.md` and reference this file.
- ChatGPT extensions: set this file as the system message or pinned context where supported.

## 14) Checklists
- Builder pre-commit
  - [ ] Plan approved
  - [ ] Files listed and scoped
  - [ ] Tests defined
  - [ ] Risk areas noted
  - [ ] Full files written
  - [ ] Changelogs appended
- Validator gate
  - [ ] Plan matches scope and guardrails
  - [ ] Tests executed and attached
  - [ ] No errors or regressions in logs
  - [ ] User has confirmed LIVE works

## 15) Incident protocol
- On failure: stop, capture logs, write a brief RCA in `context/session.md`, propose fix plan and rollback.

## 16) Versioning
- Bump this file's version when rules change. Keep an entry in `context/decisions.md` linking the commit.

## 17) Tool usage patterns

### When to use Task tool vs direct tools
- **Use Task tool (Explore agent)** for:
  - Open-ended codebase exploration ("where are errors handled?")
  - Understanding architecture or data flows
  - Finding patterns across multiple files
  - Questions that may require multiple search rounds
- **Use direct tools (Glob/Grep/Read)** for:
  - Specific file paths you know exist
  - Searching for exact class/function names
  - Reading 1-3 known files
  - Simple pattern matching with known target

### Parallel vs sequential tool calls
- **Parallel** (single message, multiple tool calls):
  - Independent git commands: `git status` + `git diff` + `git log`
  - Reading multiple unrelated files
  - Multiple independent searches
  - Gathering info that doesn't depend on other results
- **Sequential** (wait for results between calls):
  - Commands with dependencies: `mkdir` then `cp`
  - Reading file before editing/writing it
  - Git operations with side effects: `git add` then `git commit`
  - When one result informs next action

### File operations
- **NEVER** use bash for file operations:
  - Use Read tool (not `cat`, `head`, `tail`)
  - Use Edit tool (not `sed`, `awk`)
  - Use Write tool (not `echo >`, `cat <<EOF`)
  - Use Glob tool (not `find`, `ls`)
  - Use Grep tool (not `grep`, `rg`)
- **ALWAYS** read files before editing or writing
- **PREFER** editing existing files over creating new ones
- **AVOID** creating documentation files unless explicitly requested

### Communication
- Output text directly to user (never use bash `echo`)
- Keep responses short and concise
- No emojis unless user explicitly requests
- No em dashes, use hyphens
- Prioritize code over prose
- Put long explanations in `context/notes.md`

## 18) TodoWrite usage patterns

### When to use TodoWrite
- Complex multi-step tasks (3+ steps)
- Non-trivial tasks requiring planning
- User provides multiple tasks (numbered or comma-separated)
- After receiving new instructions
- Before starting work (mark as in_progress)
- After completing work (mark as completed)

### When NOT to use TodoWrite
- Single, straightforward tasks
- Trivial tasks (< 3 steps)
- Purely conversational requests

### TodoWrite rules
- Exactly ONE task in_progress at any time
- Mark completed IMMEDIATELY after finishing (no batching)
- Tasks have two forms:
  - `content`: Imperative ("Run tests")
  - `activeForm`: Present continuous ("Running tests")
- ONLY mark completed when FULLY done
- If blocked/errors, keep in_progress and create new task for blocker

## 19) Git commit policy

### When to create commits
- ONLY when user explicitly requests
- Never commit proactively
- When unclear, ask first

### Git safety protocol
- NEVER update git config
- NEVER run destructive commands (force push, hard reset) unless user requests
- NEVER skip hooks (--no-verify, --no-gpg-sign) unless user requests
- NEVER force push to main/master (warn user if requested)
- Avoid `git commit --amend` unless user requests or adding pre-commit hook edits
- Before amending: check authorship `git log -1 --format='%an %ae'`

### Commit workflow
1. Run in parallel: `git status`, `git diff`, `git log` (understand changes)
2. Draft commit message (1-2 sentences, focus on "why" not "what")
3. Add files and commit with heredoc format:
```bash
git add <files> && git commit -m "$(cat <<'EOF'
Message here.

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>
EOF
)"
```
4. Run `git status` after commit to verify success
5. If pre-commit hook modifies files and authorship is safe: amend; else create new commit

### Never commit
- Files with secrets (.env, credentials.json)
- Without user request
- Empty commits (no changes)

## 20) Verification and testing

### Post-deployment verification
1. Hard refresh browser: `Ctrl+Shift+R`
2. Test login
3. Test dashboard load (< 3 seconds target)
4. Test core features (search, modals, panel messages)
5. Check browser console for errors (F12)
6. Check PHP error logs: `cms/logs/php_errors.log`
7. Verify no regressions in existing features

### Live testing protocol
- All tests run against live production server after FTP deploy
- Document test results in `context/test-log.md`
- Capture curl outputs, API responses, error messages
- Test relevant endpoints directly: `curl https://mpsm.resolutionsbydesign.us/cms/api/...`

### Performance targets
- Dashboard load: < 3 seconds
- Device modal: < 500ms
- API responses: < 200ms
- Cache refresh: < 10 minutes for full cycle

## 21) Error handling

### When errors occur
1. Stop immediately
2. Capture full error message and stack trace
3. Write brief RCA in `context/session.md`
4. Identify root cause (not symptoms)
5. Propose fix plan and rollback option
6. Request user approval before proceeding
7. Document fix in changelog

### Common error patterns
- **Function not found:** Check spelling, imports, namespace
- **Cache stuck:** Run `refresh-cache-enhanced.php` manually
- **Deployment old version:** Hard refresh browser, verify GitHub Actions completed
- **Database errors:** Check table names, column names, indexes
- **Timeout:** Check CRON jobs, API endpoint availability

## 22) Project-specific knowledge

### Key URLs
- **Production Dashboard:** https://mpsm.resolutionsbydesign.us/cms/
- **Production API:** https://mpsm.resolutionsbydesign.us/mps-api/
- **Command Center (Panel Stream):** https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=panel
- **GitHub Actions:** https://github.com/JezSlade/MPSM-Dashboard/actions
- **cPanel:** https://mpsm.resolutionsbydesign.us:2083

### Key files
- **Deployment:** `.github/workflows/deploy.yml`, `deploy-*.ps1`, `DEPLOY-INSTRUCTIONS.md`
- **Context:** `context/*.md` (read on start, update on changes)
- **Documentation:** `context/deployment-guide.md`, `context/operations-playbook.md`
- **Database:** `cms/config.php` (database credentials), `database_optimizations.sql`
- **Cache:** `cms/api/refresh-cache-enhanced.php`, `cms/api/cache-status-report.php`

### Database details
- **Prefix:** `mpsm_`
- **Key tables:** `mpsm_cache_devices`, `mpsm_cache_device_drilldown`, `mpsm_panel_messages`
- **Indexes:** Applied via `database_optimizations.sql`

### Cache system
- **Refresh endpoint:** `/cms/api/refresh-cache-enhanced.php`
- **Status endpoint:** `/cms/api/cache-status-report.php`
- **Expected devices:** ~52,800 total
- **CRON schedule:** Hourly refresh (changed from 5-min to prevent loop)

## 23) CODEX-specific instructions

When acting as CODEX (validator agent):
1. Review plan for scope alignment with guardrails
2. Check all TODOs are completed or properly tracked
3. Verify tests are documented in `context/test-log.md`
4. Confirm deployment logged in `context/deploy-log.md`
5. Ensure live site verification completed
6. Block completion until user confirms live behavior
7. Review changelogs in modified files
8. Check for security vulnerabilities (XSS, SQL injection, command injection)
9. Verify no secrets leaked in code or commits
10. Confirm regression shields tested

End of file.
