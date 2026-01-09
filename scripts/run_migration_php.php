<?php
try {
    $dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Running Migrations</h1>";
    $v = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "<p>DB Version: $v</p>";
    
    $files = [
        'database/migrations/040_complete_budget_structure.sql',
        'database/migrations/041_add_central_in_region.sql'
    ];
    
    foreach ($files as $file) {
        echo "<h2>Processing: $file</h2>";
        if (!file_exists($file)) {
            echo "<p style='color:red'>File not found: $file</p>";
            continue;
        }
        
        $content = file_get_contents($file);
        
        // rudimentary split by semicolon at end of line
        // formatting in my files is consistent: ";\r\n" or ";\n"
        $statements = preg_split('/;\s*[\r\n]+/', $content);
        
        foreach ($statements as $sql) {
            $sql = trim($sql);
            if (empty($sql) || strpos($sql, '--') === 0) continue;
            
            try {
                $pdo->exec($sql);
                echo "<div style='color:green; margin-bottom:5px'>Success: " . htmlspecialchars(substr($sql, 0, 50)) . "...</div>";
            } catch (PDOException $e) {
                // Check error codes
                // 42S01: Base table or view already exists (1050)
                // 42S21: Column already exists (1060)
                $code = $e->getCode();
                $msg = $e->getMessage();
                
                if (strpos($msg, '1050') !== false || strpos($msg, 'already exists') !== false) {
                    echo "<div style='color:orange; margin-bottom:5px'>Skipped (Table exists): " . htmlspecialchars(substr($sql, 0, 50)) . "...</div>";
                } elseif (strpos($msg, '1060') !== false || strpos($msg, 'Duplicate column') !== false) {
                    echo "<div style='color:orange; margin-bottom:5px'>Skipped (Column exists): " . htmlspecialchars(substr($sql, 0, 50)) . "...</div>";
                } else {
                    echo "<div style='color:red; margin-bottom:5px; border-left:3px solid red; padding-left:5px'>Error: " . htmlspecialchars($msg) . "<br><pre>" . htmlspecialchars($sql) . "</pre></div>";
                }
            }
        }
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
