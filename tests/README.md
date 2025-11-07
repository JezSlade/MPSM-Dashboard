# Testing Documentation

This directory contains tests and validation tools for the MPSM Dashboard refactor.

## Test Files

### validation-checklist.md
Comprehensive checklist of all 47 features to validate post-refactor.

**Purpose:**
- Ensure zero breaking changes
- Validate all features work correctly
- Document test scenarios
- Track validation progress

**Usage:**
- Manual testing checklist
- User acceptance testing guide
- Regression test scenarios

---

### test-examples.php
Example PHP unit tests demonstrating how to test refactored components.

**Coverage:**
- Configuration system
- Service container
- Repositories
- Cache layer
- Queue system
- Access control (RBAC)
- Backwards compatibility

**Usage:**
```bash
php tests/test-examples.php
```

**Expected Output:**
```
=== MPSM Dashboard Refactor Tests ===

--- Configuration Tests ---
✓ PASS: Config helper function works
✓ PASS: Feature flag helper works
✓ PASS: App helper returns container

--- Service Container Tests ---
✓ PASS: PDO service registered
✓ PASS: CacheInterface service registered
✓ PASS: Singleton pattern works

... (more tests)

=== Test Summary ===
Passed: 25
Failed: 0
Total:  25

✓ All tests passed!
```

---

### api-tests.sh
Bash script to test all API endpoints via HTTP requests.

**Coverage:**
- Legacy API endpoints
- New REST API v1 endpoints
- Webhook endpoints
- Frontend pages
- Static assets

**Usage:**
```bash
chmod +x tests/api-tests.sh
./tests/api-tests.sh
```

**Or on Windows:**
```bash
bash tests/api-tests.sh
```

**Expected Output:**
```
=== MPSM Dashboard API Tests ===

--- Legacy API Endpoints ---
Testing: Get Devices... ✓ PASS (HTTP 200)
Testing: Get Dashboard Stats... ✓ PASS (HTTP 200)
Testing: Get Device Deep Dive... ✓ PASS (HTTP 200)

--- REST API v1 Endpoints ---
Testing: Health Check... ✓ PASS (HTTP 200)
Testing: List Devices (requires auth)... ✓ PASS (HTTP 401)

... (more tests)

=== Test Summary ===
Passed: 20
Failed: 0
Total:  20

✓ All tests passed!
```

---

## Test Categories

### Unit Tests
Test individual classes and methods in isolation.

**Files:**
- test-examples.php (example unit tests)

**What to test:**
- Repository methods
- Cache drivers
- Permission system
- Role hierarchy
- Service container

### Integration Tests
Test how components work together.

**Files:**
- test-examples.php (includes integration tests)
- api-tests.sh

**What to test:**
- API endpoints
- Database queries
- Cache integration
- Job queue flow
- Authentication + Authorization

### End-to-End Tests
Test complete user workflows.

**Files:**
- validation-checklist.md (manual E2E scenarios)

**What to test:**
- Login → Dashboard → Device List
- Panel Message Monitor workflow
- Cache refresh workflow
- Admin functions
- User role workflows

---

## Running Tests

### Quick Test (Automated)
```bash
# Run PHP unit tests
php tests/test-examples.php

# Run API integration tests
bash tests/api-tests.sh
```

### Full Validation (Manual + Automated)
1. Run automated tests (above)
2. Follow validation-checklist.md manually
3. Test each user role (Viewer, Analyst, Admin, Super Admin)
4. Verify all 47 features work
5. Check for regressions

---

## Writing New Tests

### PHP Unit Test Example
```php
test('My feature works', function() {
    $result = myFunction();
    assert_equals('expected', $result);
});
```

### API Test Example
```bash
test_api "My Endpoint" "GET" "${API_URL}/my-endpoint.php" "200"
```

---

## Test Data

### Test Users (create in database)
```sql
-- Viewer
INSERT INTO mpsm_users (username, password, role) VALUES
('viewer', PASSWORD_HASH, 'viewer');

-- Analyst
INSERT INTO mpsm_users (username, password, role) VALUES
('analyst', PASSWORD_HASH, 'analyst');

-- Admin
INSERT INTO mpsm_users (username, password, role) VALUES
('admin', PASSWORD_HASH, 'admin');

-- Super Admin
INSERT INTO mpsm_users (username, password, role) VALUES
('superadmin', PASSWORD_HASH, 'super_admin');
```

### Test Devices
Use existing devices in database or create test devices.

---

## Continuous Integration

### GitHub Actions (Future)
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - name: Run Tests
        run: php tests/test-examples.php
```

---

## Performance Benchmarks

### Expected Performance
- Page load: < 2 seconds
- API response: < 500ms
- Cache hit rate: > 80%
- Database queries per page: < 10
- Job processing: varies by job type

### How to Benchmark
```php
$start = microtime(true);
// ... code to benchmark
$duration = microtime(true) - $start;
echo "Duration: {$duration}s\n";
```

---

## Troubleshooting Tests

### Test Failures
1. Check error messages carefully
2. Verify database connection
3. Ensure bootstrap.php is loaded
4. Check file permissions
5. Review error logs

### Common Issues
- **Database connection fails:** Check config/app.php credentials
- **Cache tests fail:** Ensure cache table exists
- **API tests fail:** Verify URLs in api-tests.sh
- **Permission denied:** Check file/directory permissions

---

## Test Coverage Goals

- [ ] 80%+ code coverage (unit tests)
- [x] 100% API endpoint coverage
- [x] 100% feature validation (manual)
- [ ] Performance benchmarks documented
- [ ] Security tests passed

---

## Resources

- **PHPUnit Documentation:** https://phpunit.de/
- **API Testing Guide:** https://www.postman.com/api-testing/
- **Test-Driven Development:** https://en.wikipedia.org/wiki/Test-driven_development

---

**Last Updated:** 2025-01-07
**Maintained By:** Development Team
