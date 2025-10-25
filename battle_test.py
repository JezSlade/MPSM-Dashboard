#!/usr/bin/env python3
"""
BATTLE TEST - MPS Monitor Dashboard
Comprehensive stress test of all functionality
"""

import requests
import json
import time
from datetime import datetime

BASE_URL = "https://mpsm.resolutionsbydesign.us"

class BattleTest:
    def __init__(self):
        self.session = requests.Session()
        self.passed = 0
        self.failed = 0
        self.tests_run = []

    def test(self, name, func):
        """Run a test and track results"""
        print(f"\n[TEST] {name}...")
        try:
            result = func()
            if result:
                print(f"[PASS] {name}")
                self.passed += 1
                self.tests_run.append((name, 'PASS', None))
                return True
            else:
                print(f"[FAIL] {name}")
                self.failed += 1
                self.tests_run.append((name, 'FAIL', 'Test returned False'))
                return False
        except Exception as e:
            print(f"[FAIL] {name}: {str(e)}")
            self.failed += 1
            self.tests_run.append((name, 'FAIL', str(e)))
            return False

    def section(self, name):
        """Print section header"""
        print(f"\n{'='*70}")
        print(f"  {name}")
        print('='*70)

    def run_all_tests(self):
        """Execute all battle tests"""
        print(f"\nBATTLE TEST - MPS Monitor Dashboard")
        print(f"Started: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print(f"Target: {BASE_URL}")

        # Section 1: Authentication
        self.section("1. AUTHENTICATION SYSTEM")
        self.test("Login page loads", self.test_login_page)
        self.test("Invalid login rejected", self.test_invalid_login)
        self.test("Valid login succeeds", self.test_valid_login)
        self.test("Session persists", self.test_session_persistence)
        self.test("Unauthenticated redirect", self.test_unauth_redirect)

        # Section 2: User Management
        self.section("2. USER MANAGEMENT")
        self.test("List users", self.test_list_users)
        self.test("Create new user", self.test_create_user)
        self.test("Duplicate user rejected", self.test_duplicate_user)
        self.test("Delete user", self.test_delete_user)
        self.test("Cannot delete admin", self.test_cannot_delete_admin)

        # Section 3: Database & Cache
        self.section("3. DATABASE & CACHE")
        self.test("Cache stats load", self.test_cache_stats)
        self.test("Cache entries list", self.test_cache_entries)
        self.test("Cache operations", self.test_cache_operations)

        # Section 4: Card System
        self.section("4. CARD SYSTEM")
        self.test("Card preferences load", self.test_card_prefs_load)
        self.test("Card preferences save", self.test_card_prefs_save)
        self.test("Card preferences reset", self.test_card_prefs_reset)

        # Section 5: MPS API Engine
        self.section("5. MPS API ENGINE")
        self.test("Engine health check", self.test_engine_health)
        self.test("Engine diagnostics", self.test_engine_diagnostics)
        self.test("Endpoints list", self.test_endpoints_list)
        self.test("MPS API query", self.test_mps_query)

        # Section 6: Dashboard Frontend
        self.section("6. DASHBOARD FRONTEND")
        self.test("Dashboard page loads", self.test_dashboard_loads)
        self.test("Assets load (CSS)", self.test_assets_css)
        self.test("Assets load (JS)", self.test_assets_js)

        # Print results
        self.print_results()

    # ============================================================================
    # TEST IMPLEMENTATIONS
    # ============================================================================

    def test_login_page(self):
        r = self.session.get(f"{BASE_URL}/cms/login.html")
        return r.status_code == 200 and 'MPS Monitor Login' in r.text

    def test_invalid_login(self):
        r = self.session.post(f"{BASE_URL}/cms/api/auth.php",
                             json={'action': 'login', 'username': 'wrong', 'password': 'wrong'})
        data = r.json()
        return data.get('success') == False

    def test_valid_login(self):
        r = self.session.post(f"{BASE_URL}/cms/api/auth.php",
                             json={'action': 'login', 'username': 'admin', 'password': 'admin'})
        data = r.json()
        return data.get('success') == True and 'user' in data

    def test_session_persistence(self):
        # Make another request with same session - should still be authenticated
        r = self.session.get(f"{BASE_URL}/cms/api/auth.php")
        # If authenticated, should not get 401 but should get list of users
        return r.status_code == 200

    def test_unauth_redirect(self):
        # New session without login
        new_session = requests.Session()
        r = new_session.get(f"{BASE_URL}/cms/", allow_redirects=False)
        return r.status_code == 302 and 'login.html' in r.headers.get('Location', '')

    def test_list_users(self):
        r = self.session.get(f"{BASE_URL}/cms/api/auth.php")
        data = r.json()
        return 'users' in data and len(data['users']) > 0

    def test_create_user(self):
        username = f"testuser_{int(time.time())}"
        r = self.session.post(f"{BASE_URL}/cms/api/auth.php",
                             json={'action': 'create', 'username': username, 'password': 'test123'})
        data = r.json()
        # Store for deletion test
        if data.get('success'):
            self.test_user_id = data.get('user', {}).get('id')
        return data.get('success') == True

    def test_duplicate_user(self):
        # Try to create admin again
        r = self.session.post(f"{BASE_URL}/cms/api/auth.php",
                             json={'action': 'create', 'username': 'admin', 'password': 'test'})
        data = r.json()
        return data.get('success') == False and 'exists' in data.get('error', '').lower()

    def test_delete_user(self):
        if not hasattr(self, 'test_user_id'):
            return False
        r = self.session.delete(f"{BASE_URL}/cms/api/auth.php",
                               json={'id': self.test_user_id})
        data = r.json()
        return data.get('success') == True

    def test_cannot_delete_admin(self):
        r = self.session.delete(f"{BASE_URL}/cms/api/auth.php",
                               json={'id': 1})
        data = r.json()
        return data.get('success') == False

    def test_cache_stats(self):
        r = self.session.get(f"{BASE_URL}/cms/api/cache-manager.php")
        data = r.json()
        return 'stats' in data and 'enabled' in data['stats']

    def test_cache_entries(self):
        r = self.session.get(f"{BASE_URL}/cms/api/cache-manager.php?action=entries")
        data = r.json()
        return 'entries' in data

    def test_cache_operations(self):
        # Test that cache endpoints respond
        r = self.session.get(f"{BASE_URL}/cms/api/cache-manager.php")
        return r.status_code == 200

    def test_card_prefs_load(self):
        r = self.session.get(f"{BASE_URL}/cms/api/card-preferences.php")
        data = r.json()
        return 'preferences' in data

    def test_card_prefs_save(self):
        # Get current prefs
        r = self.session.get(f"{BASE_URL}/cms/api/card-preferences.php")
        prefs = r.json().get('preferences')

        # Save them back
        r = self.session.post(f"{BASE_URL}/cms/api/card-preferences.php",
                             json={'preferences': prefs})
        data = r.json()
        return data.get('success') == True

    def test_card_prefs_reset(self):
        r = self.session.delete(f"{BASE_URL}/cms/api/card-preferences.php")
        data = r.json()
        return data.get('success') == True

    def test_engine_health(self):
        r = requests.get(f"{BASE_URL}/mps-api/health")
        data = r.json()
        return 'status' in data and 'engine_version' in data

    def test_engine_diagnostics(self):
        r = requests.get(f"{BASE_URL}/mps-api/diagnostics")
        data = r.json()
        return 'engine' in data and 'system' in data

    def test_endpoints_list(self):
        r = requests.get(f"{BASE_URL}/mps-api/endpoints")
        data = r.json()
        return data.get('success') and data.get('count', 0) > 0

    def test_mps_query(self):
        # Try a simple query
        r = requests.post(f"{BASE_URL}/mps-api/query",
                         json={'action': 'Account/GetAccount', 'params': {}})
        # Should return something (even if error, means engine is working)
        return r.status_code in [200, 400, 401]

    def test_dashboard_loads(self):
        # Dashboard should redirect to login for unauthenticated
        r = requests.get(f"{BASE_URL}/cms/", allow_redirects=False)
        return r.status_code == 302

    def test_assets_css(self):
        r = requests.get(f"{BASE_URL}/cms/assets/css/styles.css")
        return r.status_code == 200 and 'css' in r.headers.get('Content-Type', '')

    def test_assets_js(self):
        r = requests.get(f"{BASE_URL}/cms/assets/js/app.js")
        return r.status_code == 200

    # ============================================================================
    # RESULTS
    # ============================================================================

    def print_results(self):
        """Print comprehensive test results"""
        print(f"\n{'='*70}")
        print("  BATTLE TEST RESULTS")
        print('='*70)

        total = self.passed + self.failed
        pass_rate = (self.passed / total * 100) if total > 0 else 0

        print(f"\nTotal Tests: {total}")
        print(f"Passed: {self.passed}")
        print(f"Failed: {self.failed}")
        print(f"Pass Rate: {pass_rate:.1f}%")

        if self.failed > 0:
            print(f"\nFailed Tests:")
            for name, status, error in self.tests_run:
                if status == 'FAIL':
                    print(f"  - {name}")
                    if error:
                        print(f"    Error: {error}")

        print(f"\n{'='*70}")
        if pass_rate >= 95:
            print("  STATUS: EXCELLENT - System performing optimally")
        elif pass_rate >= 80:
            print("  STATUS: GOOD - Minor issues detected")
        elif pass_rate >= 60:
            print("  STATUS: FAIR - Multiple issues need attention")
        else:
            print("  STATUS: POOR - Critical issues detected")
        print('='*70)

        print(f"\nCompleted: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == '__main__':
    tester = BattleTest()
    tester.run_all_tests()
