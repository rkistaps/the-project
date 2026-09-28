<?php

declare(strict_types=1);

/**
 * Turns PHPUnit JUnit + Clover XML into GitHub annotations for failures and a Markdown summary.
 *
 * Usage: php phpunit-report.php <junit.xml> <clover.xml> <summary.md>
 * Writes the summary only; the PHPUnit exit code decides pass/fail.
 */

[, $junitFile, $cloverFile, $summaryFile] = $argv;

$workspace = rtrim((string) getenv('GITHUB_WORKSPACE'), '/') . '/';
$relative = static fn (string $path): string => str_starts_with($path, $workspace) ? substr($path, strlen($workspace)) : $path;

$junit = @simplexml_load_file($junitFile);
if ($junit === false) {
    file_put_contents($summaryFile, "| PHPUnit | ❌ | Tests did not produce a report — see the job log |\n");
    exit(0);
}

$suite = $junit->testsuite;
$tests = (int) $suite['tests'];
$failures = (int) $suite['failures'];
$errors = (int) $suite['errors'];
$skipped = (int) $suite['skipped'];
$passed = $tests - $failures - $errors - $skipped;

foreach ($junit->xpath('//testcase[failure or error]') ?: [] as $case) {
    $problem = $case->failure ?? $case->error;
    $message = trim((string) $problem);
    printf(
        "::error file=%s,line=%d,title=%s::%s\n",
        $relative((string) $case['file']),
        (int) $case['line'],
        str_replace([',', ':'], [' ', ' '], (string) $case['class'] . '::' . (string) $case['name']),
        str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $message)
    );
}

$failedCount = $failures + $errors;
$detail = $failedCount === 0
    ? "{$passed} passed"
    : "**{$failedCount} failed** · {$passed} passed";
if ($skipped > 0) {
    $detail .= " · {$skipped} skipped";
}

$summary = sprintf("| PHPUnit | %s | %s |\n", $failedCount === 0 ? '✅' : '❌', $detail);

$clover = @simplexml_load_file($cloverFile);
if ($clover !== false) {
    // Group statement coverage by top-level directory under src/ (the architectural layers).
    $layers = [];
    foreach ($clover->xpath('//file') ?: [] as $file) {
        if (!preg_match('#/src/([^/]+)/#', (string) $file['name'], $match)) {
            continue;
        }
        $layers[$match[1]]['statements'] = ($layers[$match[1]]['statements'] ?? 0) + (int) $file->metrics['statements'];
        $layers[$match[1]]['covered'] = ($layers[$match[1]]['covered'] ?? 0) + (int) $file->metrics['coveredstatements'];
    }
    ksort($layers);

    $totalStatements = (int) $clover->project->metrics['statements'];
    $totalCovered = (int) $clover->project->metrics['coveredstatements'];
    $summary .= sprintf("| Coverage | 📊 | %s of lines (%d / %d) |\n", percent($totalCovered, $totalStatements), $totalCovered, $totalStatements);

    $summary .= "\n<details><summary>Coverage by layer</summary>\n\n| Layer | Lines | Covered |\n|---|---:|---:|\n";
    foreach ($layers as $name => $layer) {
        $summary .= sprintf("| %s | %d | %s |\n", $name, $layer['statements'], percent($layer['covered'], $layer['statements']));
    }
    $summary .= "\n</details>\n";
} else {
    $summary .= "| Coverage | ⚠️ | No coverage report produced |\n";
}

file_put_contents($summaryFile, $summary);

function percent(int $covered, int $total): string
{
    return $total === 0 ? 'n/a' : number_format($covered / $total * 100, 1) . '%';
}
