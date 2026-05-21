# MPSM Dashboard - Onboarding Guide

**Version**: 1.1.0
**Last Updated**: 2026-05-20
**Estimated Time**: 30-45 minutes

---

## Welcome!

This guide will get you up to speed on the MPSM Dashboard project. Follow these steps in order.

---

## Prerequisites

### Required Knowledge
- PHP 7.4+ (OOP, sessions, HTTP requests)
- JavaScript ES6+ (async/await, arrow functions, modules)
- CSS3 (variables, flexbox, grid)
- Git (branching, commits, PR workflow)
- REST APIs and OAuth 2.0 concepts

### Required Software
- **Git**: Version control
- **Text Editor**: VS Code recommended
- **Web Browser**: Chrome/Firefox with DevTools
- **Terminal**: Git Bash (Windows) or native terminal (Mac/Linux)
- **Python**: 3.7+ (for testing scripts)
- **curl**: For API testing

### Optional Tools
- **PHP**: 7.4+ (for local testing; this workspace uses `flatpak-spawn --host php`)
- **Postman**: API testing (alternative to curl)
- **Beyond Compare**: Diff tool for large files

---

## Step 1: Get Access (5 minutes)

### GitHub Repository
1. Request access to: https://github.com/JezSlade/MPSM-Dashboard
2. Clone the repository:
   ```bash
   git clone https://github.com/JezSlade/MPSM-Dashboard.git
   cd MPSM-Dashboard
   ```

### Live Site Access
- **URL**: https://mpsm.resolutionsbydesign.us/cms/
- **Test Credentials**: admin / admin
- **Purpose**: Testing and verification

### FTP Deployment Access (if deploying)
- **Server**: ftp.resolutionsbydesign.us
- **Username**: `<FTP_USER>`
- **Password**: stored outside the repo in environment variables or ignored `.runtime/ftp.env`
- **Purpose**: Deploying files to production

---

## Step 2: Read Core Documentation (15 minutes)

Read these documents in order:

### 1. Constitution (Required)
**File**: [docs/CONSTITUTION.md](./CONSTITUTION.md)

**Why**: Establishes non-negotiable rules and standards

**Key Takeaways**:
- 10 rules of the Agent Covenant
- Code must be documented and tested
- No direct commits to main branch
- Backward compatibility required

### 2. Architecture Overview (Required)
**Section**: [docs/CONSTITUTION.md#project-architecture](./CONSTITUTION.md#project-architecture)

**Why**: Understand system design

**Key Takeaways**:
- 3-tier architecture: Browser → CMS → mps-api → HP API
- CMS handles presentation, mps-api handles authentication
- No direct frontend-to-backend calls

### 3. Pain Points (Strongly Recommended)
**File**: [docs/PAIN_POINTS.md](./PAIN_POINTS.md)

**Why**: Avoid known pitfalls

**Key Takeaways**:
- PowerShell deployment/test scripts were retired; use portable Python scripts in `scripts/`
- Global search paginates automatically, modal search doesn't
- FTP deployment may require retries and must preserve server-managed `.env`, `cms/config.php`, cache, logs, and locks
- Browser caching delays seeing deployed changes

### 4. Recent ADRs (Optional, but helpful)
**File**: [docs/adr/README.md](./adr/README.md)

**Focus on**:
- ADR-0001: CMS and API Separation
- ADR-0005: Global Search Pagination

---

## Step 3: Explore the Codebase (10 minutes)

### Key Files to Review

#### Frontend Entry Point
**File**: `cms/index.php`
- Main HTML structure
- Header with global search bar
- Dashboard container
- Theme toggle, refresh buttons

#### Main Application Logic
**File**: `cms/assets/app.js` (3,400+ lines)
- State management (lines 1-30)
- Device loading (lines 2051-2143)
- Global search (lines 3267-3365)
- Modal management
- Card initialization

#### Dashboard Cards
**File**: `cms/assets/js/card-registry.js` (1,166 lines)
- Card definitions (devices, alerts, metrics)
- Table renderers using TableUtils
- Modal layouts
- Export functionality

#### Styling
**File**: `cms/assets/style.css` (1,630 lines)
- CSS variables for theming (lines 8-45)
- Responsive layouts
- Dark mode support

#### API Proxy Endpoints
**Files**: `cms/api/*.php`
- `login.php`: User authentication
- `get-devices.php`: Device list with pagination
- `get-supply-alerts.php`: Supply alert data

#### Configuration
**File**: `cms/config.php`
- Constants (API URLs, defaults)
- Environment-specific settings

#### Utilities
**File**: `cms/functions.php`
- `getMPSToken()`: OAuth token management
- `requireAuth()`: Session checking
- Shared helper functions

### Directory Structure
```
MPSM-Dashboard/
├── cms/                         # Frontend application
│   ├── api/                    # API endpoints
│   ├── assets/                 # Static assets
│   │   ├── app.js             # Main logic (3400 lines)
│   │   ├── js/card-registry.js # Cards (1166 lines)
│   │   └── style.css          # Styles (1630 lines)
│   ├── config.php             # Configuration
│   ├── functions.php          # Utilities
│   └── index.php              # Entry point
├── docs/                       # Documentation
├── .github/                    # GitHub templates
└── *.md                       # Project documentation
```

---

## Step 4: Test Your Environment (5 minutes)

### Verify Git Setup
```bash
# Check Git configuration
git config --get user.name
git config --get user.email

# If not set, configure:
git config user.name "Your Name"
git config user.email "your.email@example.com"
```

### Test API Access
```bash
# 1. Login and save session
curl -s -X POST "https://mpsm.resolutionsbydesign.us/cms/api/login.php" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}' \
  -c /tmp/cookies.txt

# Expected: {"success":true,"message":"Login successful"}

# 2. Test device API
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=5" \
  -b /tmp/cookies.txt | python -c "import sys, json; data = json.load(sys.stdin); print(f\"Success: {data['success']}, Devices: {len(data.get('devices', []))}\")"

# Expected: Success: True, Devices: 5
```

### Test Live Site
1. Open: https://mpsm.resolutionsbydesign.us/cms/
2. Login: admin / admin
3. Verify dashboard loads
4. Try global search: Type "HP" in header
5. Open browser console (F12), check for errors

---

## Step 5: Make a Test Change (5 minutes)

### Create Feature Branch
```bash
git checkout -b test/your-name-onboarding
```

### Make Small Change
Edit `docs/ONBOARDING.md` (this file):
```markdown
## I Was Here

[Your Name] completed onboarding on [Date].
```

### Commit and Push
```bash
git add docs/ONBOARDING.md
git commit -m "docs: Complete onboarding - [Your Name]

Verified environment setup and made test commit.

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"

git push -u origin test/your-name-onboarding
```

### (Optional) Create Test PR
1. Go to GitHub repository
2. Click "Pull requests" → "New pull request"
3. Select your branch
4. Fill out PR template
5. Submit (don't merge)

This confirms your Git/GitHub access works.

---

## Step 6: Development Workflow

### Typical Task Flow

1. **Receive Task**: GitHub issue or user request

2. **Read Documentation**:
   - Check PAIN_POINTS.md for known issues
   - Review related ADRs
   - Check CHANGELOG.md for recent changes

3. **Create Feature Branch**:
   ```bash
   git checkout main
   git pull
   git checkout -b feature/your-feature-name
   ```

4. **Make Changes**:
   - Follow coding standards in CONSTITUTION.md
   - Update documentation as you go
   - Use `debugLog()` for troubleshooting, not `console.log()`

5. **Test Locally** (if possible):
   - Test API changes with curl
   - Test UI changes in browser
   - Check browser console for errors

6. **Deploy to Production**:
   ```bash
   python3 scripts/run_checks.py
   python3 scripts/ftp_backup.py
   python3 scripts/ftp_deploy.py --delete
   python3 scripts/live_smoke.py
   ```

7. **Test on Live Site**:
   - Open https://mpsm.resolutionsbydesign.us/cms/
   - Verify changes work
   - Test edge cases
   - Document test results

8. **Commit Changes**:
   ```bash
   git add -A
   git commit -m "feat: Your feature description

   - Detailed change 1
   - Detailed change 2

   🤖 Generated with [Claude Code](https://claude.com/claude-code)

   Co-Authored-By: Claude <noreply@anthropic.com>"
   ```

9. **Push and Create PR**:
   ```bash
   git push -u origin feature/your-feature-name
   ```
   - Go to GitHub and create PR
   - Fill out PR template completely

10. **Code Review**:
    - Address feedback
    - Make requested changes
    - Push updates to same branch

11. **Merge**:
    - Maintainer approves and merges
    - Delete feature branch

---

## Common Tasks

### Task: Add a New Dashboard Card

1. **Open**: `cms/assets/js/card-registry.js`
2. **Add card definition** to `cards` array:
   ```javascript
   {
       id: 'my-new-card',
       title: 'My New Card',
       icon: 'fa-icon-name',
       category: 'metrics',
       description: 'Card description',
       renderFn: renderMyNewCard
   }
   ```
3. **Implement render function**:
   ```javascript
   function renderMyNewCard(container) {
       container.innerHTML = `<div>Card content</div>`;
   }
   ```
4. **Register card**: It's automatically registered via `cards` array
5. **Test**: Reload dashboard, find card in "Manage Dashboard"

### Task: Add a New API Endpoint

1. **Create file**: `cms/api/my-endpoint.php`
2. **Add authentication**: `requireAuth();`
3. **Implement logic**:
   ```php
   <?php
   require '../config.php';
   require '../functions.php';

   requireAuth();

   try {
       // Your logic here
       echo json_encode(['success' => true, 'data' => $result]);
   } catch (Exception $e) {
       http_response_code(500);
       echo json_encode(['success' => false, 'error' => $e->getMessage()]);
   }
   ```
4. **Test**:
   ```bash
   curl -s "https://mpsm.resolutionsbydesign.us/cms/api/my-endpoint.php" \
     -b /tmp/cookies.txt
   ```

### Task: Fix a Bug

1. **Reproduce bug**: Test on live site, capture error messages
2. **Check PAIN_POINTS.md**: May already be documented
3. **Locate code**: Use grep/search to find relevant code
4. **Fix code**: Make minimal necessary changes
5. **Test fix**: Verify bug resolved, no new bugs introduced
6. **Document**: Update PAIN_POINTS.md if applicable
7. **Commit**: Use "fix:" prefix in commit message

---

## Tools and Commands

### Essential Commands

```bash
# Git
git status                    # Check current state
git log --oneline -10        # View recent commits
git diff                     # See unstaged changes
git diff --staged            # See staged changes

# Deployment
python3 scripts/run_checks.py
python3 scripts/ftp_backup.py
python3 scripts/ftp_deploy.py --delete
python3 scripts/live_smoke.py

# API Testing
curl -s URL | python -c "import sys, json; print(json.load(sys.stdin))"

# Search codebase
grep -r "searchTerm" cms/    # Search all files
grep -n "function name" file.js  # Show line numbers
```

### Useful Aliases (Optional)

Add to `~/.bashrc` or `~/.bash_profile`:

```bash
# Git shortcuts
alias gs='git status'
alias gl='git log --oneline -10'
alias gd='git diff'

# Project shortcuts
alias cdmpsm='cd /c/Users/jez.slade/Desktop/Projects/MPSM-Dashboard'
alias mpsm-login='curl -s -X POST "https://mpsm.resolutionsbydesign.us/cms/api/login.php" -H "Content-Type: application/json" -d "{\"username\":\"admin\",\"password\":\"admin\"}" -c /tmp/cookies.txt'
```

---

## Troubleshooting

### Problem: Can't push to GitHub
**Solution**:
```bash
# Check remote
git remote -v

# If SSH, switch to HTTPS:
git remote set-url origin https://github.com/JezSlade/MPSM-Dashboard.git

# Configure credentials
git config credential.helper store
```

### Problem: Deployed code not showing
**Solution**:
- Hard refresh: Ctrl+Shift+R
- Clear cache: DevTools → Network → Disable cache
- Test in incognito window
- Wait 2-5 minutes for cache expiry

### Problem: API returns 401 Unauthorized
**Solution**:
```bash
# Re-login
curl -s -X POST "https://mpsm.resolutionsbydesign.us/cms/api/login.php" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}' \
  -c /tmp/cookies.txt
```

### Problem: FTP deploy script fails
**Solution**:
- Run `python3 scripts/run_checks.py` first and fix any local failures.
- Verify FTP credentials are available through environment variables or `.runtime/ftp.env`.
- Run `python3 scripts/ftp_deploy.py --dry-run --delete` to inspect the planned sync before uploading.

---

## Next Steps

### Immediate (First Day)
- [ ] Complete all steps in this guide
- [ ] Read CONSTITUTION.md thoroughly
- [ ] Review PAIN_POINTS.md
- [ ] Browse recent commits on GitHub
- [ ] Test environment setup

### Short Term (First Week)
- [ ] Pick a small "good first issue" from GitHub
- [ ] Make your first real contribution
- [ ] Get familiar with deployment process
- [ ] Review all ADRs
- [ ] Read CHANGELOG.md

### Long Term (First Month)
- [ ] Understand entire architecture
- [ ] Contribute to documentation
- [ ] Propose process improvements
- [ ] Help with code reviews

---

## Getting Help

### Resources
- **Documentation**: `/docs` directory
- **Code Comments**: Look for `//` and `/* */` comments
- **Git History**: `git log` and commit messages
- **GitHub Issues**: https://github.com/JezSlade/MPSM-Dashboard/issues

### Questions to Ask
When stuck, ask:
1. Is this documented in PAIN_POINTS.md?
2. Is there an ADR for this decision?
3. What does the code comment say?
4. What does git blame show? (who wrote this and why?)

### Asking for Help
When asking for help, provide:
1. What you're trying to do
2. What you tried
3. What happened (error messages, screenshots)
4. What you expected
5. Relevant code snippets

---

## Congratulations!

You've completed the onboarding process. You should now be able to:

- ✅ Navigate the codebase
- ✅ Understand the architecture
- ✅ Make changes safely
- ✅ Test on the live site
- ✅ Deploy code to production
- ✅ Follow the development workflow
- ✅ Find answers in documentation

Welcome to the team!

---

## I Was Here

<!-- Add your completion entry below -->

---

*Last Updated: 2025-10-31 | Maintainer: Project Team*
