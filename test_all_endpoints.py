#!/usr/bin/env python3
"""
Test All Endpoints - Comprehensive API Test
Tests every endpoint with actual requests
"""

import requests
import json

BASE_URL = "https://mpsm.resolutionsbydesign.us"

class EndpointTester:
    def __init__(self):
        self.session = requests.Session()
        self.results = []

    def test_endpoint(self, name, method, url, **kwargs):
        """Test a single endpoint"""
        try:
            if method == 'GET':
                r = self.session.get(url, **kwargs)
            elif method == 'POST':
                r = self.session.post(url, **kwargs)
            elif method == 'DELETE':
                r = self.session.delete(url, **kwargs)
            else:
                raise ValueError(f"Unknown method: {method}")

            status = "PASS" if r.status_code in [200, 302, 401] else "FAIL"
            self.results.append({
                'name': name,
                'method': method,
                'url': url,
                'status': status,
                'code': r.status_code,
                'response_size': len(r.content)
            })
            print(f"[{status}] {method} {name} - Status: {r.status_code}")
            return r

        except Exception as e:
            self.results.append({
                'name': name,
                'method': method,
                'url': url,
                'status': 'FAIL',
                'error': str(e)
            })
            print(f"[FAIL] {method} {name} - Error: {str(e)}")
            return None

    def run_all_tests(self):
        """Run comprehensive endpoint tests"""
        print("="*70)
        print("  ENDPOINT COMPREHENSIVE TEST")
        print("="*70)

        # === FRONTEND ENDPOINTS ===
        print("\n[SECTION] Frontend Pages")
        self.test_endpoint("Login Page", "GET", f"{BASE_URL}/cms/login.html")
        self.test_endpoint("Dashboard Page", "GET", f"{BASE_URL}/cms/", allow_redirects=False)
        self.test_endpoint("CSS Styles", "GET", f"{BASE_URL}/cms/assets/css/styles.css")
        self.test_endpoint("CSS Card Management", "GET", f"{BASE_URL}/cms/assets/css/card-management.css")
        self.test_endpoint("JS App", "GET", f"{BASE_URL}/cms/assets/js/app.js")
        self.test_endpoint("JS API", "GET", f"{BASE_URL}/cms/assets/js/api.js")
        self.test_endpoint("JS Card Manager", "GET", f"{BASE_URL}/cms/assets/js/card-manager.js")
        self.test_endpoint("JS Card Registry", "GET", f"{BASE_URL}/cms/assets/js/card-registry.js")
        self.test_endpoint("JS Table Utils", "GET", f"{BASE_URL}/cms/assets/js/table-utils.js")

        # === AUTH ENDPOINTS ===
        print("\n[SECTION] Authentication API")
        self.test_endpoint("Auth API (no auth)", "GET", f"{BASE_URL}/cms/api/auth.php")

        # Login first
        r = self.test_endpoint("Login", "POST", f"{BASE_URL}/cms/api/auth.php",
                               json={'action': 'login', 'username': 'admin', 'password': 'admin'})

        if r and r.status_code == 200:
            print("  [INFO] Logged in successfully")

            # Test authenticated endpoints
            self.test_endpoint("List Users", "GET", f"{BASE_URL}/cms/api/auth.php")

        # === CACHE ENDPOINTS ===
        print("\n[SECTION] Cache Management API")
        self.test_endpoint("Cache Stats", "GET", f"{BASE_URL}/cms/api/cache-manager.php")
        self.test_endpoint("Cache Entries", "GET", f"{BASE_URL}/cms/api/cache-manager.php?action=entries")

        # === CARD PREFERENCES ===
        print("\n[SECTION] Card Preferences API")
        self.test_endpoint("Get Preferences", "GET", f"{BASE_URL}/cms/api/card-preferences.php")

        # === SYSTEM STATUS ===
        print("\n[SECTION] System Status")
        self.test_endpoint("System Status", "GET", f"{BASE_URL}/cms/api/system-status.php")

        # === MPS API ENGINE ===
        print("\n[SECTION] MPS API Engine")
        self.test_endpoint("Engine Health", "GET", f"{BASE_URL}/mps-api/health")
        self.test_endpoint("Engine Diagnostics", "GET", f"{BASE_URL}/mps-api/diagnostics")
        self.test_endpoint("Engine Endpoints", "GET", f"{BASE_URL}/mps-api/endpoints")
        self.test_endpoint("Swagger Docs", "GET", f"{BASE_URL}/mps-api/swagger.json")

        # Print Results
        self.print_results()

    def print_results(self):
        """Print test results summary"""
        print("\n" + "="*70)
        print("  TEST RESULTS")
        print("="*70)

        passed = sum(1 for r in self.results if r['status'] == 'PASS')
        failed = sum(1 for r in self.results if r['status'] == 'FAIL')
        total = len(self.results)

        print(f"\nTotal Endpoints Tested: {total}")
        print(f"Passed: {passed}")
        print(f"Failed: {failed}")
        print(f"Success Rate: {(passed/total*100):.1f}%")

        if failed > 0:
            print("\nFailed Endpoints:")
            for r in self.results:
                if r['status'] == 'FAIL':
                    print(f"  - {r['method']} {r['name']}")
                    if 'error' in r:
                        print(f"    Error: {r['error']}")
                    elif 'code' in r:
                        print(f"    HTTP {r['code']}")

        print("\n" + "="*70)

if __name__ == '__main__':
    tester = EndpointTester()
    tester.run_all_tests()
