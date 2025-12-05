# AGENTS.md

Repository-standard rules for any AI agent operating in this workspace. Applies to Claude Code, Copilot Chat, ChatGPT extensions, and other validators.

Version: 1.1.1
Owner: Jez
Scope: Entire repository
Last Updated: 2025-12-04

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

## 24) Ollama subagent integration

### Purpose

Offload token-heavy, repetitive, or continuous tasks to local Ollama models via the ollama-subagent hub. This optimizes primary agent token usage, enables parallel exploration, and bypasses session time limits for long-running operations.

### Available endpoints

#### CLI Interface

```bash
# Generate text from model
~/.local/bin/ollama-subagent generate --model <model> "<prompt>"
echo "<prompt>" | ~/.local/bin/ollama-subagent generate --model <model>

# List available models
~/.local/bin/ollama-subagent models

# Apply unified diff (dry-run)
cat diff.patch | ~/.local/bin/ollama-subagent apply-patch --root /path/to/repo --strip 1 --check-only

# Apply unified diff (live)
cat diff.patch | ~/.local/bin/ollama-subagent apply-patch --root /path/to/repo --strip 1
```

#### HTTP Interface (Port 11435)

```bash
# Generate text
curl -s -X POST http://127.0.0.1:11435/generate \
  -H 'Content-Type: application/json' \
  -d '{"model":"<model>","prompt":"<prompt>"}'

# Apply patch
curl -s -X POST http://127.0.0.1:11435/apply_patch \
  -H 'Content-Type: application/json' \
  -d '{"diff":"<unified diff>","root":"/path/to/repo","strip":1,"check_only":true}'
```

### Available models

- `llama3.2:1b` - Fast, lightweight for simple tasks (1.2B params, Q8_0)
- `deepseek-coder:6.7b-instruct-q4_K_M` - Code-specialized model (7B params, Q4_K_M)
- `deepseek-r1:7b` - Reasoning-focused model (7.6B params, Q4_K_M)

### When to use ollama-subagent

#### Token optimization (offload to local models)

- **Boilerplate generation**: Generate repetitive code structures, test templates, SQL schemas
- **Code documentation**: Generate docstrings, comments, API documentation
- **Draft generation**: Create initial implementations for refinement
- **Text transformation**: Reformat data, convert formats, generate variations
- **Simple analysis**: Basic code review, style checking, lint suggestions

#### Parallel exploration (run multiple queries simultaneously)

- **Multi-path search**: Explore different code patterns concurrently
- **Hypothesis testing**: Test multiple approaches in parallel
- **Coverage analysis**: Scan multiple modules simultaneously
- **Pattern discovery**: Search for similar implementations across codebase
- **Reference lookup**: Query documentation/examples from multiple sources

#### Continuous operation (bypass session limits)

- **Long-running monitors**: Watch logs, poll endpoints, track changes
- **Iterative refinement**: Generate-test-refine loops without human intervention
- **Batch processing**: Process large file sets, apply mass transformations
- **Background analysis**: Deep codebase analysis while working on other tasks
- **Persistent tasks**: Operations that span multiple primary agent sessions

#### Patch validation (safe diff application)

- **Pre-flight checks**: Validate patches before applying (`--check-only`)
- **Automated testing**: Apply patch, run tests, rollback if failed
- **Staged rollouts**: Apply to test environment first
- **Conflict detection**: Identify merge conflicts before application

### Usage patterns

#### Pattern 1: Offload boilerplate generation

```bash
# Primary agent delegates to subagent
echo "Generate a PHP function that validates email addresses with regex" | \
  ~/.local/bin/ollama-subagent generate --model deepseek-coder:6.7b-instruct-q4_K_M

# Primary agent reviews output and integrates
```

#### Pattern 2: Parallel codebase exploration

```bash
# Launch multiple searches concurrently
curl -X POST http://127.0.0.1:11435/generate \
  -d '{"model":"deepseek-coder:6.7b-instruct-q4_K_M","prompt":"List all authentication patterns in PHP"}' &
curl -X POST http://127.0.0.1:11435/generate \
  -d '{"model":"deepseek-coder:6.7b-instruct-q4_K_M","prompt":"List all database query patterns"}' &
wait
```

#### Pattern 3: Safe patch application workflow

```bash
# 1. Primary agent generates patch
cat > /tmp/fix.patch <<'EOF'
--- a/cms/api/example.php
+++ b/cms/api/example.php
@@ -10,7 +10,7 @@
-$result = mysql_query($query);
+$result = mysqli_query($conn, $query);
EOF

# 2. Dry-run validation
cat /tmp/fix.patch | ~/.local/bin/ollama-subagent apply-patch \
  --root /home/jez/projects/MPSM-Dashboard --strip 1 --check-only

# 3. If validation passes, apply live
cat /tmp/fix.patch | ~/.local/bin/ollama-subagent apply-patch \
  --root /home/jez/projects/MPSM-Dashboard --strip 1
```

#### Pattern 4: Continuous monitoring task

```bash
# Run long-term log monitoring in background
while true; do
  echo "Analyze recent PHP errors and suggest fixes" | \
    ~/.local/bin/ollama-subagent generate --model deepseek-r1:7b \
    > /tmp/error-analysis-$(date +%s).txt
  sleep 3600
done &
```

#### Pattern 5: Iterative code refinement

```bash
# Generate -> Test -> Refine loop
for iteration in {1..10}; do
  echo "Optimize this SQL query: $query" | \
    ~/.local/bin/ollama-subagent generate --model deepseek-coder:6.7b-instruct-q4_K_M \
    > /tmp/optimized-query.sql

  # Test performance
  mysql < /tmp/optimized-query.sql | tee /tmp/perf-$iteration.txt

  # If performance acceptable, break
  if [ $(grep "rows in set" /tmp/perf-$iteration.txt | cut -d' ' -f1) -lt 0.5 ]; then
    break
  fi
done
```

### Model selection guide

| Task Type | Recommended Model | Rationale |
|-----------|------------------|-----------|
| Simple text generation | `llama3.2:1b` | Fastest, lowest resource usage |
| Code generation | `deepseek-coder:6.7b-instruct-q4_K_M` | Specialized for code tasks |
| Complex reasoning | `deepseek-r1:7b` | Best for multi-step logic |
| Documentation | `llama3.2:1b` | Sufficient quality, fast |
| Code review | `deepseek-coder:6.7b-instruct-q4_K_M` | Understands code patterns |
| Refactoring suggestions | `deepseek-r1:7b` | Needs architectural reasoning |
| Test generation | `deepseek-coder:6.7b-instruct-q4_K_M` | Understands test patterns |
| Error analysis | `deepseek-r1:7b` | Requires root cause reasoning |

### Integration with primary agent workflow

#### Before using subagent

1. **Assess token cost**: Would this task consume significant primary agent tokens?
2. **Check independence**: Can this task run independently without tight feedback loop?
3. **Evaluate urgency**: Can task run asynchronously or needs immediate integration?
4. **Quality threshold**: Is subagent output quality sufficient, or needs primary agent refinement?

#### Delegation workflow

1. **Primary agent**: Identify delegable subtask
2. **Primary agent**: Generate prompt for subagent with clear instructions
3. **Primary agent**: Execute subagent command via Bash tool
4. **Subagent**: Process task and return output
5. **Primary agent**: Review subagent output for quality/correctness
6. **Primary agent**: Integrate subagent output or request refinement
7. **Primary agent**: Document subagent usage in session notes

#### Quality control
- **Always review subagent output** before integration
- **Never blindly apply** subagent-generated code without inspection
- **Test subagent patches** in isolation before applying to codebase
- **Validate subagent suggestions** against project standards and architecture
- **Document subagent contributions** in changelogs with attribution

### Guardrails and limitations

#### When NOT to use subagent
- Tasks requiring deep context understanding (use primary agent)
- Security-critical operations (authentication, authorization, secrets)
- Final code review before deployment (primary agent validates)
- User-facing communication (primary agent handles all user interaction)
- Architecture decisions (primary agent reasoning required)
- Tasks requiring project-specific knowledge from `context/` files

#### Safety protocols
- **Sandbox subagent operations**: Run in isolated environment
- **Validate all diffs**: Always use `--check-only` before applying patches
- **Limit scope**: Keep subagent tasks narrowly defined and bounded
- **Monitor resource usage**: Prevent runaway processes consuming system resources
- **Log all subagent invocations**: Track what was delegated and why
- **Rollback capability**: Always have undo path for subagent changes

#### Error handling
- If subagent fails, primary agent must handle gracefully
- If subagent output is low quality, primary agent regenerates or refines
- If subagent unreachable, primary agent falls back to direct execution
- Never block primary agent workflow on subagent availability

### Performance considerations

#### Token savings estimate
- Boilerplate generation: 500-2000 tokens saved per task
- Parallel exploration: 2000-5000 tokens saved (4-5 sequential searches avoided)
- Draft generation: 1000-3000 tokens saved per iteration
- Documentation: 300-1000 tokens saved per file

#### Speed comparison
- `llama3.2:1b`: ~50-100 tokens/sec (fastest)
- `deepseek-coder:6.7b`: ~20-40 tokens/sec (moderate)
- `deepseek-r1:7b`: ~15-30 tokens/sec (slower but higher quality)
- Primary agent: Variable, but includes network latency

#### Resource usage
- Local models run on host GPU/CPU (no API costs)
- Subagent hub runs on port 11435 (separate from Ollama port 11434)
- Multiple concurrent subagent requests supported
- Background tasks do not block primary agent sessions

### Example workflows

#### Workflow A: Generate test suite for new feature
```bash
# 1. Primary agent identifies need for tests
# 2. Primary agent delegates to subagent
echo "Generate PHPUnit tests for function validateDeviceSerial() that checks manufacturer prefix and validates serial format" | \
  ~/.local/bin/ollama-subagent generate --model deepseek-coder:6.7b-instruct-q4_K_M \
  > /tmp/generated-tests.php

# 3. Primary agent reviews generated tests
# 4. Primary agent integrates tests into test suite with refinements
# 5. Primary agent runs tests to validate
```

#### Workflow B: Explore caching patterns across codebase
```bash
# 1. Primary agent needs to understand all caching approaches
# 2. Launch parallel subagent explorations
curl -X POST http://127.0.0.1:11435/generate \
  -d '{"model":"deepseek-coder:6.7b-instruct-q4_K_M","prompt":"Find all Redis caching patterns in PHP files"}' > /tmp/redis-patterns.txt &
curl -X POST http://127.0.0.1:11435/generate \
  -d '{"model":"deepseek-coder:6.7b-instruct-q4_K_M","prompt":"Find all file-based caching patterns in PHP files"}' > /tmp/file-patterns.txt &
curl -X POST http://127.0.0.1:11435/generate \
  -d '{"model":"deepseek-coder:6.7b-instruct-q4_K_M","prompt":"Find all database result caching patterns"}' > /tmp/db-patterns.txt &
wait

# 3. Primary agent synthesizes findings from all parallel searches
# 4. Primary agent presents unified caching strategy to user
```

#### Workflow C: Monitor and auto-fix common errors
```bash
# 1. Setup continuous monitoring (runs in background)
#!/bin/bash
while true; do
  # Check for errors
  if grep -q "Fatal error" cms/logs/php_errors.log; then
    # Extract recent errors
    tail -100 cms/logs/php_errors.log > /tmp/recent-errors.txt

    # Ask subagent for analysis and fix suggestions
    cat /tmp/recent-errors.txt | \
      ~/.local/bin/ollama-subagent generate --model deepseek-r1:7b \
      --prompt "Analyze these PHP errors and suggest fixes" \
      > /tmp/error-fixes-$(date +%Y%m%d-%H%M%S).txt

    # Alert user (primary agent can review suggestions later)
    echo "Error analysis available: /tmp/error-fixes-*.txt"
  fi

  sleep 600  # Check every 10 minutes
done &

# 2. Primary agent periodically reviews subagent suggestions
# 3. Primary agent applies approved fixes with proper testing
```

### Session continuity

#### Handoff protocol
When primary agent session ends:
1. Document active subagent tasks in `context/session.md`
2. Record subagent PIDs and output locations
3. Note expected completion times
4. Flag any subagent output requiring review

When new primary agent session starts:
1. Read `context/session.md` for pending subagent tasks
2. Check output files for completed subagent work
3. Review and integrate subagent contributions
4. Kill or continue long-running subagent processes as appropriate

#### Persistent task registry
Maintain `context/subagent-tasks.md`:
```markdown
# Active Subagent Tasks

## Background Monitors
- PID 12345: Error log monitor (started 2025-11-17 14:30)
- Output: /tmp/error-fixes-*.txt
- Action: Review suggestions hourly

## Pending Reviews
- /tmp/generated-tests-feature-x.php (generated 2025-11-17 15:45)
- /tmp/cache-analysis.txt (completed 2025-11-17 16:00)
```

## Ollama subagent mandate
- Always delegate heavy analysis, mass code generation, cache-driving loops, and log sweeps to the local Ollama hub (`~/.local/bin/ollama-subagent`).
- Default models: `deepseek-coder:6.7b-instruct-q4_K_M` (code), `deepseek-r1:7b` (reasoning), `llama3.2:1b` (fast drafts).
- Standard call: `ollama-subagent generate --model <model> "<prompt>"` and capture outputs to `/tmp/...` for review.
- Track every long-running subagent invocation (PID, output path, start time) in `context/subagent-tasks.md`; summarize outcomes in `context/session.md`.
- Use subagent scripts for repetitive fetch loops (e.g., cache refresh auto calls) while the primary agent focuses on orchestration and verification.

<!--
CHANGELOG
2025-12-04 Codex
- Added mandatory Ollama subagent policy, defaults, and tracking rules.
-->
