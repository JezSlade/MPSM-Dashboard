# Active Subagent Tasks

## Overview

This file tracks long-running or pending tasks delegated to the ollama-subagent system. Primary agents should check this file on session start and update it when delegating work.

## Background Monitors

None currently active.

## Pending Reviews

None currently pending.

## Usage Guidelines

### When to add entries

- Background processes spawned via ollama-subagent (PID required)
- Tasks generating output files that need review
- Continuous monitors that span sessions
- Iterative refinement loops

### Entry format

```markdown
## Background Monitors
- PID <process_id>: <task_description> (started YYYY-MM-DD HH:MM)
- Output: <file_path_pattern>
- Action: <what_needs_to_be_done>

## Pending Reviews
- <file_path>: <description> (generated YYYY-MM-DD HH:MM)
- Status: <ready_for_review|blocked|integrated>
```

### Session handoff protocol

**When primary agent session ends:**

1. Document active subagent tasks with PIDs
2. Record output locations
3. Note expected completion times
4. Flag items requiring review

**When new primary agent session starts:**

1. Read this file for pending work
2. Check output files for completed tasks
3. Review and integrate contributions
4. Kill or continue processes as appropriate

## Integration rules

- Always review subagent output before integration
- Test subagent patches in isolation
- Validate suggestions against project standards
- Document subagent contributions in changelogs with attribution

## Last updated

2025-12-04 - File created
