<?php
// scripts/check_data_integrity.php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    echo "Checking Integrity of Disbursement Details against New Schema...\n";
    
    // 1. Check Projects (Outputs)
    $sqlProject = "SELECT COUNT(*) as count 
                   FROM disbursement_details d 
                   LEFT JOIN projects p ON d.output_id = p.id 
                   WHERE d.output_id IS NOT NULL AND p.id IS NULL";
    
    $orphanProjects = \App\Core\Database::queryOne($sqlProject)['count'];
    
    if ($orphanProjects > 0) {
        echo "WARNING: Found $orphanProjects records in disbursement_details referencing unknown output_id (Project).\n";
        // List sample IDs
        $samples = \App\Core\Database::query("SELECT DISTINCT output_id FROM disbursement_details d LEFT JOIN projects p ON d.output_id = p.id WHERE d.output_id IS NOT NULL AND p.id IS NULL LIMIT 5");
        echo "Sample IDs: " . implode(', ', array_column($samples, 'output_id')) . "\n";
    } else {
        echo "OK: All output_id references are valid Projects.\n";
    }

    // 2. Check Activities
    $sqlActivity = "SELECT COUNT(*) as count 
                    FROM disbursement_details d 
                    LEFT JOIN activities a ON d.activity_id = a.id 
                    WHERE d.activity_id IS NOT NULL AND a.id IS NULL";
    
    $orphanActivities = \App\Core\Database::queryOne($sqlActivity)['count'];
    
    if ($orphanActivities > 0) {
        echo "WARNING: Found $orphanActivities records in disbursement_details referencing unknown activity_id.\n";
        $samples = \App\Core\Database::query("SELECT DISTINCT activity_id FROM disbursement_details d LEFT JOIN activities a ON d.activity_id = a.id WHERE d.activity_id IS NOT NULL AND a.id IS NULL LIMIT 5");
        echo "Sample IDs: " . implode(', ', array_column($samples, 'activity_id')) . "\n";
    } else {
        echo "OK: All activity_id references are valid Activities.\n";
    }

    if ($orphanProjects == 0 && $orphanActivities == 0) {
        echo "\nSUCCESS: Data integrity is solid. No migration needed.\n";
    } else {
        echo "\nACTION REQUIRED: Data migration or cleanup needed for orphaned records.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
