import argparse
import os
from pathlib import Path
import subprocess


parser = argparse.ArgumentParser(description='Serve GeoWeb locally with PHP.')
parser.add_argument('--allow-writes', action='store_true', help='Allow changes to the hosted database.')
args = parser.parse_args()

project = Path(__file__).resolve().parent
runtime = project / '.local'
php = runtime / 'php' / 'php.exe'
config = Path(os.environ.get('GEOWEB_DB_CONFIG') or project.parent / '.geoweb-db.php')
if not php.is_file() or not config.is_file():
    raise SystemExit('GeoWeb needs the local PHP runtime and private database configuration.')

options = '-c statement_timeout=10000'
if not args.allow_writes:
    options += ' -c default_transaction_read_only=on'
env = os.environ | {
    'GEOWEB_DB_CONFIG': str(config.resolve()),
    'GEOWEB_READ_ONLY': '0' if args.allow_writes else '1',
    'PGSSLMODE': 'verify-full',
    'PGSSLROOTCERT': str(runtime / 'windows-ca-bundle.pem'),
    'PGCONNECT_TIMEOUT': '8',
    'PGOPTIONS': options,
}

mode = 'writes to the hosted database enabled' if args.allow_writes else 'read-only'
print(f'GeoWeb at http://127.0.0.1:8001/ ({mode}). Press Ctrl+C to stop.', flush=True)
try:
    subprocess.run([
        str(php), '-n', '-d', f'extension_dir={php.parent / "ext"}',
        '-d', 'extension=pdo_pgsql', '-d', 'display_errors=Off', '-d', 'log_errors=On',
        '-S', '127.0.0.1:8001', '-t', str(project / 'code'),
    ], env=env, check=True)
except KeyboardInterrupt:
    pass
