#!/usr/bin/env python3
"""
Main runner script for API discovery process.
Executes the full discovery workflow in correct order.
"""
import sys
import subprocess
from pathlib import Path


def run_script(script_name: str, args: list = None) -> int:
    """
    Run a Python script.

    Args:
        script_name: Name of the script to run
        args: Optional command line arguments

    Returns:
        Exit code
    """
    script_path = Path(__file__).parent / script_name
    cmd = [sys.executable, str(script_path)]

    if args:
        cmd.extend(args)

    print(f"\n{'='*60}")
    print(f"Running: {' '.join(cmd)}")
    print('='*60)

    result = subprocess.run(cmd)
    return result.returncode


def main():
    """Main entry point."""
    import argparse

    parser = argparse.ArgumentParser(
        description='Run API discovery process',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog='''
Examples:
  # Run full discovery on all endpoints
  python run_discovery.py

  # Run discovery on limited number of endpoints (for testing)
  python run_discovery.py --limit 10

  # Run discovery with more retries
  python run_discovery.py --max-attempts 20

  # Skip discovery phase (if already done) and just rebuild references
  python run_discovery.py --skip-probe
        '''
    )

    parser.add_argument(
        '--limit',
        type=int,
        default=None,
        help='Limit number of endpoints to probe (for testing)'
    )
    parser.add_argument(
        '--max-attempts',
        type=int,
        default=12,
        help='Maximum attempts per endpoint (default: 12)'
    )
    parser.add_argument(
        '--skip-discover',
        action='store_true',
        help='Skip endpoint discovery phase'
    )
    parser.add_argument(
        '--skip-probe',
        action='store_true',
        help='Skip probing phase (use existing results)'
    )

    args = parser.parse_args()

    print("API Discovery and Payload Calibration")
    print("=" * 60)

    # Step 1: Discover endpoints from Swagger
    if not args.skip_discover:
        exit_code = run_script('discover_endpoints.py')
        if exit_code != 0:
            print("\n[X] Endpoint discovery failed!")
            return exit_code
    else:
        print("\n[>>] Skipping endpoint discovery phase")

    # Step 2: Probe endpoints
    if not args.skip_probe:
        probe_args = ['--max-attempts', str(args.max_attempts)]
        if args.limit:
            probe_args.extend(['--limit', str(args.limit)])

        exit_code = run_script('probe_endpoint.py', probe_args)
        if exit_code != 0:
            print("\n[X] Endpoint probing failed!")
            return exit_code
    else:
        print("\n[>>] Skipping probing phase")

    # Step 3: Build reference files
    exit_code = run_script('build_reference.py')
    if exit_code != 0:
        print("\n[X] Reference building failed!")
        return exit_code

    # Success!
    print("\n" + "=" * 60)
    print("[OK] API Discovery Complete!")
    print("=" * 60)
    print("\nGenerated files:")
    print("  - output/endpoint_reference.yaml  (canonical endpoint reference)")
    print("  - output/curl_recipes.md          (copy-paste cURL commands)")
    print("  - output/samples/                 (request/response samples)")
    print("  - output/domain_seeds.json        (discovered IDs and codes)")
    print("  - output/coverage_report.md       (coverage statistics)")
    print("  - logs/run.ndjson                 (detailed probe logs)")
    print("\nYou can now use these files to implement the API engine.")

    return 0


if __name__ == '__main__':
    sys.exit(main())
