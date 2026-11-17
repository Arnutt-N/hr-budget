<?php
/**
 * Fiscal code import configuration.
 *
 * Source spreadsheets are git-ignored. Paths live here so the script has one
 * place to read them from. No credentials are stored in this file: the target
 * database is supplied by the required --database=NAME CLI flag.
 */

// Source spreadsheets are git-ignored and live only in the primary checkout.
// Worktrees share them by absolute path.
$sourceDir = 'C:/laragon/www/hr_budget/secrets/fisical-code';

return [
    'budget_file' => $sourceDir . '/16002_รหัสงบประมาณ.xlsx',
    'budget_sheet' => '16002',
    'activity_file' => $sourceDir . '/16002_กิจกรรมหลัก.xls',
    'activity_sheet' => 'report',
    'fiscal_year' => 2569,
    'dry_run' => false,
    'verbose' => false,
];
