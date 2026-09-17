<?php

declare(strict_types=1);

// CLI-only password hash generator for the staging admin seed (human runbook step).
// No database access. Usage: php scripts/gen-admin-hash.php <password>

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script may only run from the CLI.\n");
    exit(1);
}

if (!isset($argv[1]) || $argv[1] === '') {
    fwrite(STDERR, "Usage: php scripts/gen-admin-hash.php <password>\n");
    exit(1);
}

echo password_hash($argv[1], PASSWORD_DEFAULT), PHP_EOL;
