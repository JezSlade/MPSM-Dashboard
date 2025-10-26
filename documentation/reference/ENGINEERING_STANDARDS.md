# ENGINEERING STANDARDS
## MPSM Dashboard - Immutable Coding Principles
**Version**: 1.0.0
**Date**: 2025-10-26
**Status**: MANDATORY - No exceptions without explicit approval

---

## CORE PHILOSOPHY

### The Three Laws of MPSM Engineering

1. **SIMPLICITY OVER CLEVERNESS**
   - If you can't explain it in one sentence, it's too complex
   - Choose boring, proven solutions over exciting new patterns
   - Flat is better than nested

2. **VISIBLE FAILURES OVER SILENT ONES**
   - Never catch exceptions without showing the user
   - Log errors AND display them
   - Failed quietly = broken for the user

3. **WORKING OVER PERFECT**
   - Ship simple and working today beats perfect and broken tomorrow
   - Add complexity ONLY when simple solution proven insufficient
   - Optimize for maintainability, not theoretical performance

---

## ARCHITECTURE RULES

### Rule 1: No Classes Unless Absolutely Necessary
**Why**: Classes add complexity, hide state, create coupling

**ALLOWED**: Procedural functions only
```php
// ✅ GOOD
function getDevices($customerCode) {
    return fetchFromAPI('Device/List', ['customerCode' => $customerCode]);
}

// ❌ BAD
class DeviceManager {
    private $api;
    public function __construct(APIClient $api) { ... }
    public function getDevices($customerCode) { ... }
}
```

**EXCEPTION**: External libraries only (e.g., PDO is allowed because it's built-in)

### Rule 2: One Database Access Pattern
**Pattern**: Direct PDO with constants

```php
// ✅ GOOD - Direct PDO
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

**FORBIDDEN**:
- ❌ Database wrapper classes
- ❌ Singleton patterns
- ❌ ORM libraries
- ❌ Query builders

### Rule 3: No Caching Until Proven Necessary
**Requirement**: Must demonstrate actual performance problem first

**Process**:
1. Build feature without cache
2. Measure performance with real usage
3. If >2 second load time, THEN consider cache
4. Document why cache is needed

**FORBIDDEN**: "Premature optimization" caching

### Rule 4: Configuration Must Use Constants
**Why**: Constants are globally available, arrays require passing

```php
// ✅ GOOD - config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'resolut7_mpsm');
define('DB_USER', 'resolut7_mpsm_agent');
define('DB_PASS', '!C@S@lcd6McFceb8');

// ❌ BAD - Returns array
return ['host' => 'localhost', 'database' => 'resolut7_mpsm'];
```

### Rule 5: Flat File Structure
**Maximum nesting**: 2 levels

```
cms/
  index.php           ← Entry point
  config.php          ← Constants only
  functions.php       ← Utility functions
  api/                ← API endpoints (1 level deep)
    get-data.php
    save-prefs.php
  assets/             ← Static files
    js/
    css/
```

**FORBIDDEN**:
- ❌ `classes/` directory
- ❌ `includes/` directory
- ❌ `lib/` directory
- ❌ Nested subdirectories >2 deep

---

## ERROR HANDLING

### Rule 6: Always Show Errors to User
**Why**: Silent failures waste hours of debugging

```php
// ✅ GOOD
try {
    $result = dangerousOperation();
} catch (Exception $e) {
    error_log($e->getMessage());  // Log it
    echo json_encode([            // AND show it
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}

// ❌ BAD
try {
    $result = dangerousOperation();
} catch (Exception $e) {
    error_log($e->getMessage());
    return null;  // User has no idea it failed
}
```

### Rule 7: No Silent Returns
**FORBIDDEN**: Returning `null`, `false`, `[]` on error

**REQUIRED**: Throw exception OR return error array

```php
// ✅ GOOD
function getUser($id) {
    $user = queryDatabase($id);
    if (!$user) {
        throw new Exception("User $id not found");
    }
    return $user;
}

// ❌ BAD
function getUser($id) {
    $user = queryDatabase($id);
    return $user ?: null;  // Caller has no idea why it failed
}
```

---

## CODE STYLE

### Rule 8: Variable Naming - No Abbreviations
**Why**: Clarity over brevity

```php
// ✅ GOOD
$customerCode = 'ABC123';
$deviceList = getDevices();
$databaseConnection = connectToDatabase();

// ❌ BAD
$custCd = 'ABC123';
$devs = getDevices();
$dbConn = connectToDatabase();
```

**EXCEPTION**: Loop counters (`$i`, `$j`), widely known abbreviations (`$id`, `$url`, `$sql`)

### Rule 9: Function Naming - Verb + Noun
**Pattern**: `verbNoun()` or `verb_noun()`

```php
// ✅ GOOD
function getCustomerDevices($customerId) { }
function saveUserPreferences($userId, $prefs) { }
function deleteOldCacheEntries() { }

// ❌ BAD
function customer($id) { }              // What does this do?
function preferences($userId, $data) { } // Get or set?
function cache() { }                    // Too vague
```

### Rule 10: Functions Must Be Short
**Maximum**: 50 lines
**Ideal**: 20 lines

**If longer**: Extract sub-functions

```php
// ✅ GOOD
function processOrder($orderId) {
    $order = getOrder($orderId);
    validateOrder($order);
    calculateTotals($order);
    saveOrder($order);
}

// ❌ BAD
function processOrder($orderId) {
    // 200 lines of code doing everything
}
```

---

## DEPENDENCIES

### Rule 11: No External Dependencies Without Justification
**Allowed without approval**:
- PHP built-ins (PDO, cURL, JSON)
- Browser-native JavaScript
- CSS (no preprocessors)

**Requires justification**:
- npm packages
- Composer packages
- JavaScript frameworks
- CSS frameworks

**FORBIDDEN**:
- React, Vue, Angular (overkill for this project)
- Laravel, Symfony (too heavy)
- jQuery (use vanilla JS)
- Bootstrap (use custom CSS)

### Rule 12: No Build Tools
**Why**: Adds complexity, breaks simplicity

**FORBIDDEN**:
- ❌ Webpack
- ❌ Babel
- ❌ TypeScript
- ❌ SASS/LESS
- ❌ npm scripts

**REQUIRED**: Plain files that run directly

---

## FILE ORGANIZATION

### Rule 13: One Responsibility Per File
**Maximum**: 200 lines per file

```php
// ✅ GOOD
// api/get-devices.php - Does ONE thing
session_start();
require 'config.php';
require 'functions.php';

$devices = fetchDevices($_GET['customerCode']);
echo json_encode(['success' => true, 'devices' => $devices]);

// ❌ BAD
// api/everything.php - Does EVERYTHING
// 1000 lines handling all API endpoints
```

### Rule 14: No Include Hell
**Maximum includes per file**: 3

```php
// ✅ GOOD
require 'config.php';      // 1
require 'functions.php';   // 2
require 'api-client.php';  // 3

// ❌ BAD
require 'config.php';
require 'database.php';
require 'classes/User.php';
require 'classes/Device.php';
require 'helpers/array.php';
require 'helpers/string.php';
require 'lib/vendor/autoload.php';
```

---

## COMMENTS

### Rule 15: Comments Explain WHY, Not WHAT
**Why**: Code shows WHAT it does, comments explain WHY

```php
// ✅ GOOD
// Use 5 minute TTL because API data changes frequently
$cacheTTL = 300;

// Retry 3 times because API occasionally returns 503
$maxRetries = 3;

// ❌ BAD
// Set cache TTL to 300
$cacheTTL = 300;

// Loop through devices
foreach ($devices as $device) {
```

### Rule 16: Comment Complex Logic Only
**Don't comment**: Obvious operations
**Do comment**: Non-obvious decisions

```php
// ❌ BAD - Obvious
// Get the user ID
$userId = $_SESSION['user_id'];

// ✅ GOOD - Non-obvious
// Skip deleted devices (IsDeleted flag isn't in API response, must check LastPingUtc)
if (strtotime($device['LastPingUtc']) < strtotime('-90 days')) {
    continue;
}
```

---

## DATABASE

### Rule 17: Always Use Prepared Statements
**No exceptions**

```php
// ✅ GOOD
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// ❌ BAD - SQL injection vulnerability
$result = $pdo->query("SELECT * FROM users WHERE id = $userId");
```

### Rule 18: Table Naming - Singular Nouns
**Pattern**: `prefix_singular_noun`

```
✅ GOOD:
mpsm_user
mpsm_device
mpsm_preference

❌ BAD:
users
Devices
card_preferences
```

### Rule 19: Column Naming - snake_case
```sql
✅ GOOD:
user_id
created_at
last_login_time

❌ BAD:
userId
CreatedAt
lastLoginTime
```

---

## JAVASCRIPT

### Rule 20: Vanilla JavaScript Only
**No jQuery, no frameworks**

```javascript
// ✅ GOOD
document.getElementById('myButton').addEventListener('click', handleClick);

// ❌ BAD
$('#myButton').click(handleClick);
```

### Rule 21: One Global Namespace
**Pattern**: `const AppName = (function() { ... })()`

```javascript
// ✅ GOOD
const MPSM = (function() {
    'use strict';

    function loadDashboard() { }
    function savePreferences() { }

    return {
        loadDashboard,
        savePreferences
    };
})();

// ❌ BAD
function loadDashboard() { }  // Pollutes global scope
function savePreferences() { }
```

### Rule 22: No Callback Hell
**Maximum nesting**: 2 levels
**Solution**: Use async/await

```javascript
// ✅ GOOD
async function loadData() {
    try {
        const devices = await fetchDevices();
        const metrics = await fetchMetrics(devices[0].id);
        displayData(metrics);
    } catch (error) {
        showError(error.message);
    }
}

// ❌ BAD
fetchDevices(function(devices) {
    fetchMetrics(devices[0].id, function(metrics) {
        processMetrics(metrics, function(result) {
            displayData(result);
        });
    });
});
```

---

## CSS

### Rule 23: Use CSS Variables for Theme
**Why**: Easy theme switching, no preprocessor needed

```css
/* ✅ GOOD */
:root {
    --bg-primary: #ffffff;
    --text-primary: #333333;
}

[data-theme="dark"] {
    --bg-primary: #1a1a1a;
    --text-primary: #eeeeee;
}

.card {
    background: var(--bg-primary);
    color: var(--text-primary);
}

/* ❌ BAD */
.card {
    background: #ffffff;
    color: #333333;
}

.dark-theme .card {
    background: #1a1a1a;
    color: #eeeeee;
}
```

### Rule 24: BEM Naming Convention
**Pattern**: `block__element--modifier`

```css
/* ✅ GOOD */
.card { }
.card__header { }
.card__body { }
.card--highlighted { }

/* ❌ BAD */
.card { }
.cardHeader { }
.card-body { }
.card.highlighted { }
```

---

## SECURITY

### Rule 25: Session-Based Auth Only
**Why**: Simple, secure enough for single-user CMS

```php
// ✅ GOOD
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.html');
    exit;
}

// ❌ BAD
// JWT tokens, OAuth, complex auth systems - overkill
```

### Rule 26: Never Store Credentials in Code
**Required**: Use constants in config.php (not in repo)

```php
// ✅ GOOD - config.php (in .gitignore)
define('DB_PASS', 'actual_password_here');

// ❌ BAD - Hardcoded in files
$password = 'actual_password_here';
```

---

## TESTING

### Rule 27: Manual Testing Required Before Commit
**Checklist**:
1. Does it load without errors?
2. Does it work in both themes?
3. Does it handle errors gracefully?
4. Did you test on live site?

**No automated tests** (too complex for this project)

### Rule 28: Test Error States
**Must test**:
- What happens when API is down?
- What happens when database is unreachable?
- What happens when user enters invalid data?

---

## GIT WORKFLOW

### Rule 29: Descriptive Commit Messages
**Format**:
```
[Action] Brief description

Detailed explanation of:
- What changed
- Why it changed
- What problem it solves

Result: What works now
```

**Example**:
```
Rebuild CMS with simplified architecture

Removed:
- All classes (Database, MySQLCache)
- Complex cache system
- Multiple config patterns

Added:
- Simple functions.php with utilities
- Direct PDO with constants
- Visible error handling

Result: Dashboard loads correctly, errors are visible
```

### Rule 30: Never Commit Credentials
**Always in .gitignore**:
- config.php
- .env
- database.php (if has credentials)

**Use examples instead**:
- config.php.example
- .env.example

---

## PERFORMANCE

### Rule 31: Measure Before Optimizing
**Process**:
1. Build simple version
2. Deploy to production
3. Measure with real usage
4. If slow (>2s), THEN optimize
5. Measure again to confirm improvement

**FORBIDDEN**: Optimizing based on theory

### Rule 32: Simplicity IS Optimization
**Why**: Less code = faster, more reliable

```php
// ✅ GOOD - Simple, fast enough
$devices = $pdo->query("SELECT * FROM devices")->fetchAll();

// ❌ BAD - "Optimized" but complex
$devices = DeviceRepository::getInstance()
    ->with(['customer', 'product'])
    ->cached(300)
    ->get();
```

---

## DOCUMENTATION

### Rule 33: README Must Answer 3 Questions
1. **What** does this do?
2. **How** do I run it?
3. **Where** do I find credentials?

**No more, no less**

### Rule 34: Code Is The Documentation
**Why**: Comments go stale, code doesn't

**If code needs explanation**: Refactor to be clearer
**Don't write**: Separate documentation that duplicates code

---

## WHEN TO BREAK THESE RULES

### Rule 35: Never Break Rules Silently
**Process**:
1. Document why rule doesn't apply
2. Propose alternative approach
3. Get explicit approval
4. Document decision in commit message

**Example**:
```
Break Rule 11: Add Composer dependency for PDF generation

Why: Native PHP PDF libraries are unmaintained
Alternative: Using FPDF (lightweight, stable)
Approval: User explicitly requested PDF export
Decision: Benefits outweigh complexity cost
```

---

## ENFORCEMENT

### How to Follow These Standards

1. **Before writing code**: Read relevant rules
2. **While writing code**: Keep this file open
3. **Before committing**: Review checklist
4. **If uncertain**: Choose simpler approach

### Red Flags You're Breaking Standards

- ❌ "I'll add a class to make this cleaner"
- ❌ "Let me abstract this into a pattern"
- ❌ "We should use a framework for this"
- ❌ "This needs a cache for performance"
- ❌ "Let me add this npm package"

### Green Lights You're Following Standards

- ✅ "Can I solve this with a simple function?"
- ✅ "How do I show this error to the user?"
- ✅ "Is this the simplest way that works?"
- ✅ "Can I explain this in one sentence?"
- ✅ "Did I test the error case?"

---

## SUMMARY

**The Golden Rule**: When in doubt, choose simplicity.

**The Test**: If you can't explain it to the user in plain English, it's too complex.

**The Goal**: Code that works, code you understand, code you can maintain.

---

**END OF STANDARDS**

*These standards are MANDATORY for all MPSM Dashboard development.*
*Violations must be explicitly justified and approved.*
*When uncertain, err on the side of simplicity.*
