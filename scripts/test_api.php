<?php
/**
 * API Endpoint Test Script
 * Tests the refactored API endpoints for Plan/Project/Activity hierarchy
 */
require_once __DIR__ . '/../vendor/autoload.php';

echo "=== API Endpoint Test ===\n\n";

// Test 1: Get all Plans
echo "1. Testing Plan::all()...\n";
try {
    $plans = \App\Models\Plan::all();
    echo "   OK: Found " . count($plans) . " plans\n";
    if (count($plans) > 0) {
        echo "   Sample: " . ($plans[0]['name_th'] ?? $plans[0]['code'] ?? 'N/A') . "\n";
        $testPlanId = $plans[0]['id'];
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

// Test 4: Test DisbursementController API endpoint logic
echo "\n4. Testing DisbursementController API Logic...\n";
try {
    // Simulate getOutputs query
    $sql = "SELECT id, code, name_th FROM projects WHERE plan_id = ? ORDER BY code";
    $outputs = \App\Core\Database::query($sql, [$testPlanId]);
    echo "   OK: getOutputs query works, returned " . count($outputs) . " records\n";
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
}

// Test 5: Check BudgetController activities logic
echo "\n5. Testing BudgetController Activities Tree Logic...\n";
try {
    $plansForTree = \App\Core\Database::query("SELECT id, code, name_th FROM plans WHERE fiscal_year = 2568 ORDER BY code");
    echo "   OK: Plans for tree = " . count($plansForTree) . "\n";
    
    if (count($plansForTree) > 0) {
        $projectsForTree = \App\Core\Database::query("SELECT id, plan_id, code, name_th FROM projects ORDER BY code");
        echo "   OK: Projects for tree = " . count($projectsForTree) . "\n";
        
        $activitiesForTree = \App\Core\Database::query("SELECT id, project_id, code, name_th FROM activities ORDER BY code");
        echo "   OK: Activities for tree = " . count($activitiesForTree) . "\n";
    }
} catch (Exception $e) {
    echo "   FAIL: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
