<?php
/**
 * Web-based API Endpoint Test
 * Access via browser: http://hr_budget.test/test_api_web.php
 */
require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== API Endpoint Test ===\n\n";

// Test 1: Get all Plans
echo "1. Testing Plan::all()...\n";
try {
    $plans = \App\Models\Plan::all();
    echo "   OK: Found " . count($plans) . " plans\n";
    if (count($plans) > 0) {
        echo "   Sample: " . ($plans[0]['name_th'] ?? $plans[0]['code'] ?? 'N/A') . "\n";
        $testPlanId = $plans[0]['id'];
    } else {
        $testPlanId = null;
    }
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
    $testPlanId = null;
}

// Test 2: Get Projects by Plan ID
echo "\n2. Testing Project::where('plan_id', ...)...\n";
if ($testPlanId) {
    try {
        $projects = \App\Models\Project::where('plan_id', $testPlanId)->get();
        echo "   OK: Found " . count($projects) . " projects for plan_id=$testPlanId\n";
        if (count($projects) > 0) {
            echo "   Sample: " . ($projects[0]['name_th'] ?? $projects[0]['code'] ?? 'N/A') . "\n";
            $testProjectId = $projects[0]['id'];
        } else {
            $testProjectId = null;
        }
    } catch (Exception $e) {
        echo "   FAIL: " . $e->getMessage() . "\n";
        $testProjectId = null;
    }
} else {
    echo "   SKIPPED: No plan ID available\n";
    $testProjectId = null;
}

// Test 3: Get Activities by Project ID  
echo "\n3. Testing Activity::where('project_id', ...)...\n";
if ($testProjectId) {
    try {
        $activities = \App\Models\Activity::where('project_id', $testProjectId)->get();
        echo "   OK: Found " . count($activities) . " activities for project_id=$testProjectId\n";
        if (count($activities) > 0) {
            echo "   Sample: " . ($activities[0]['name_th'] ?? $activities[0]['code'] ?? 'N/A') . "\n";
        }
    } catch (Exception $e) {
        echo "   FAIL: " . $e->getMessage() . "\n";
    }
} else {
    echo "   SKIPPED: No project ID available\n";
}

// Test 4: Direct SQL queries (like controllers use)
echo "\n4. Testing Direct SQL Queries...\n";
try {
    $outputs = \App\Core\Database::query("SELECT id, code, name_th FROM projects LIMIT 5");
    echo "   OK: Projects query returned " . count($outputs) . " records\n";
    
    $activities = \App\Core\Database::query("SELECT id, code, name_th FROM activities LIMIT 5");
    echo "   OK: Activities query returned " . count($activities) . " records\n";
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
}

// Test 5: Data integrity check
echo "\n5. Data Integrity Check...\n";
try {
    $orphanProjects = \App\Core\Database::queryOne(
        "SELECT COUNT(*) as c FROM disbursement_details d 
         LEFT JOIN projects p ON d.output_id = p.id 
         WHERE d.output_id IS NOT NULL AND p.id IS NULL"
    )['c'];
    
    $orphanActivities = \App\Core\Database::queryOne(
        "SELECT COUNT(*) as c FROM disbursement_details d 
         LEFT JOIN activities a ON d.activity_id = a.id 
         WHERE d.activity_id IS NOT NULL AND a.id IS NULL"
    )['c'];
    
    echo "   Orphan output_ids: $orphanProjects\n";
    echo "   Orphan activity_ids: $orphanActivities\n";
    
    if ($orphanProjects == 0 && $orphanActivities == 0) {
        echo "   STATUS: Data integrity OK!\n";
    } else {
        echo "   STATUS: WARNING - Orphan records found!\n";
    }
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
