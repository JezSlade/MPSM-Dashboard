# AI AUDIT INSTRUCTIONS

## HOW TO PERFORM THE AUDIT
#### Perform linted and smoke-test on asset references
- In all files, scan for any references to external libraries or classes (e.g. Dotenv\Dotenv) that aren’t actually installed, and either remove them or wrap them in class_exists()/file_exists() guards.
- Scan all files for any constants they reference and ensure each is defined in config.php, or flag missing ones.
- When auditing, grep all PHP view and include files for bare constant names (e.g. anything matching /[A-Z_]{2,}/) and ensure each one is defined in config.php. Flag any missing constants.
- Identify and flag any instances of redundant code or unnecessary operations, such as redundant sanitization on internally generated strings or minor logical inefficiencies, even if they do not cause functional errors or security vulnerabilities.
- 
### Step 1: Repository Structure Analysis
- Examine the overall project structure and organization
- Identify main application entry points and core modules
- Map out the data flow between components

### Step 2: Code Quality Review
- Identify code duplication and opportunities for refactoring
- Check for proper error handling and exception management
- Look for memory leaks and resource management issues
- Assess variable naming conventions and code readability
- Check for dead code and unused imports/dependencies
- Evaluate function and class complexity
- Review commenting and documentation quality

### Step 3: Architecture Review
- Evaluate component separation and modularity
- Check for proper separation of concerns
- Assess dependency injection and coupling
- Review API design and REST compliance
- Examine database schema design and normalization
- Check for proper configuration management
- Assess scalability considerations

### Step 4: Dependency Management
- Check for outdated or vulnerable dependencies
- Look for unused dependencies that can be removed
- Assess dependency security and licensing
- Check for circular dependencies
- Review package lock file consistency

### Step 7: Testing Coverage
- Identify areas lacking unit tests
- Check for integration test coverage
- Look for edge cases that aren't tested
- Assess test quality and maintainability
- Check for proper mocking and test isolation

## WHAT TO LOOK FOR

### Security Vulnerabilities
- **Hardcoded Credentials**: API keys, passwords, tokens in source code
- **SQL Injection**: Unparameterized queries, string concatenation in SQL
- **XSS Vulnerabilities**: Unescaped user input in HTML output
- **Authentication Flaws**: Weak password policies, session fixation
- **Authorization Issues**: Missing access controls, privilege escalation
- **CSRF Vulnerabilities**: Missing CSRF tokens or validation
- **File Upload Issues**: Unrestricted file types, path traversal
- **Insecure Cryptography**: Weak algorithms, hardcoded keys
- **Information Disclosure**: Verbose error messages, debug info exposure
- **Insecure Dependencies**: Known CVEs in third-party packages

### Code Quality Issues
- **Code Duplication**: Repeated logic that should be extracted
- **Long Functions**: Methods exceeding 50 lines that need breaking down
- **Deep Nesting**: Excessive if/else or loop nesting levels
- **Magic Numbers**: Hardcoded values that should be constants
- **Poor Naming**: Unclear variable, function, or class names
- **Missing Error Handling**: Functions without try-catch or error checks
- **Inconsistent Formatting**: Mixed indentation, spacing, or style
- **Commented Code**: Dead code left in comments
- **TODO Comments**: Unfinished work or technical debt markers
- **Complex Conditionals**: Boolean logic that could be simplified

### Performance Problems
- **N+1 Database Queries**: Queries inside loops
- **Missing Database Indexes**: Slow queries on unindexed columns
- **Inefficient Algorithms**: O(n²) where O(n) would work
- **Memory Leaks**: Objects not properly disposed or cleared
- **Synchronous Operations**: Blocking calls that could be async
- **Large Payloads**: Oversized API responses or data transfers
- **Missing Caching**: Repeated expensive operations
- **Resource Waste**: Unnecessary object creation or processing
- **Inefficient Loops**: Unnecessary work inside iterations
- **Database Connection Issues**: Not closing connections properly

### Architecture Concerns
- **Tight Coupling**: Components too dependent on each other
- **Missing Abstractions**: Repeated patterns without interfaces
- **Monolithic Structure**: Large files or classes doing too much
- **Circular Dependencies**: Components referencing each other
- **Configuration Issues**: Hardcoded environment-specific values
- **API Design Flaws**: Non-RESTful endpoints, inconsistent responses
- **Database Design Issues**: Denormalization, missing foreign keys
- **Scalability Bottlenecks**: Single points of failure or contention
- **Missing Logging**: Insufficient debugging and monitoring
- **Poor Error Propagation**: Errors not properly bubbled up

## AUDIT REPORT FORMAT

### Findings Structure
Present each finding as a numbered item with:

```
## FINDINGS

### Security Issues
1. **Hardcoded API Key in config.php** (CRITICAL)
   - File: `config/api.php` line 15
   - Issue: Database password hardcoded in source
   - Impact: Credential exposure in version control
   - Fix: Move to environment variable

2. **SQL Injection in user search** (HIGH)
   - File: `models/User.php` line 45
   - Issue: Unparameterized query construction
   - Impact: Database compromise possible
   - Fix: Use prepared statements with parameters

### Code Quality Issues
3. **Duplicate authentication logic** (MEDIUM)
   - Files: `controllers/AuthController.php`, `middleware/Auth.php`
   - Issue: Same validation code repeated in multiple places
   - Impact: Maintenance burden, inconsistency risk
   - Fix: Extract to shared AuthService class

4. **Missing error handling** (MEDIUM)
   - File: `services/PaymentService.php` line 23
   - Issue: API call without try-catch
   - Impact: Unhandled exceptions crash application
   - Fix: Add proper exception handling

### Performance Issues
5. **N+1 query problem** (HIGH)
   - File: `controllers/OrderController.php` line 67
   - Issue: Loading users individually in loop
   - Impact: Slow page load, database overload
   - Fix: Use eager loading or single query with joins

6. **Missing database index** (MEDIUM)
   - Table: `orders`
   - Issue: No index on frequently queried `user_id` column
   - Impact: Slow query performance
   - Fix: Add index on `user_id` column
```

### Priority Levels
- **CRITICAL**: Security vulnerabilities requiring immediate attention
- **HIGH**: Significant issues affecting functionality or performance
- **MEDIUM**: Important improvements for maintainability
- **LOW**: Minor optimizations or best practices

### Summary Format
End with a numbered summary list:

```
## PRIORITY RECOMMENDATIONS
1. Fix hardcoded API key in config.php
2. Implement SQL injection protection in user search
3. Extract duplicate authentication logic
4. Add error handling to PaymentService
5. Resolve N+1 query in OrderController
6. Add database index on orders.user_id
```

This format allows you to say "Fix 1, 3, 4" and developers know exactly what to address.


### File Definitions:
Refined Endpoint Groups JSON.json    = Organized JSON of All Endpoints
Swagger.json                         = Full Swagger JSON
collectdata.sh                       = Script to Collect all project data into a single file
collect_files.sh                     = Script to Collect all project files into a single file
backup.deploy.yml                    = A backup of my deploy.yml (my original got trashed somehow so now I keep a spare)
AllEndpoints.json                    = A list of all Endpoints and their expected payloads.