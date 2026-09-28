<?php

declare(strict_types=1);

/**
 * Turns PHPStan JSON output into GitHub annotations and a Markdown summary line.
 *
 * Usage: php phpstan-report.php <phpstan.json> <phpstan-baseline.neon> <summary.md>
 * Exits 1 when there are errors not covered by the baseline.
 */

[, $jsonFile, $baselineFile, $summaryFile] = $argv;

$workspace = rtrim((string) getenv('GITHUB_WORKSPACE'), '/') . '/';
$report = json_decode((string) @file_get_contents($jsonFile), true);

if (!is_array($report)) {
    file_put_contents($summaryFile, "| PHPStan | ❌ | Analysis did not produce a report — see the job log |\n");
    exit(1);
}

$new = 0;
foreach ($report['files'] ?? [] as $path => $file) {
    $relative = str_starts_with($path, $workspace) ? substr($path, strlen($workspace)) : $path;
    foreach ($file['messages'] as $message) {
        $new++;
        printf("::error file=%s,line=%d,title=PHPStan::%s\n", $relative, $message['line'] ?? 1, escape($message['message']));
    }
}

// General errors, e.g. a baseline entry that no longer matches because the error was fixed.
foreach ($report['errors'] ?? [] as $error) {
    $new++;
    printf("::error title=PHPStan::%s\n", escape($error));
}

preg_match_all('/^\s*count:\s*(\d+)/m', (string) @file_get_contents($baselineFile), $counts);
$known = array_sum(array_map('intval', $counts[1]));

$status = $new === 0 ? '✅' : '❌';
$detail = $new === 0
    ? "No new errors · {$known} known in baseline"
    : "**{$new} new** · {$known} known in baseline";
file_put_contents($summaryFile, "| PHPStan | {$status} | {$detail} |\n");

exit($new === 0 ? 0 : 1);

function escape(string $text): string
{
    return str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $text);
}
