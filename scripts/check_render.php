<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Models\ExpenseGroup;

// Bootstrap
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$typeId = 1; 
$organizationId = 3; 

$groups = ExpenseGroup::getAllWithItemsByType($typeId, $organizationId);

$trackings = []; // Dummy
$iconClass = "text-blue-400";
$isReadOnly = false;

$html = "";

$renderItems = function($items, $level) use (&$renderItems, $trackings, $iconClass, $isReadOnly, &$html) {
    foreach ($items as $item): 
        $hasChildren = !empty($item['children']);
        $itemId = $item['id'];
        $paddingLeft = ($level * 16) + 16 . 'px'; 
        
        $html .= "TR [ID: $itemId] Level: $level, Children: " . ($hasChildren ? 'YES' : 'NO') . " Name: {$item['name_th']}\n";
        $html .= "   -> Padding: $paddingLeft, HasButton: " . ($hasChildren ? 'YES' : 'NO') . "\n";
        
        if ($hasChildren) {
            $renderItems($item['children'], $level + 1);
        }
    endforeach;
};

foreach ($groups as $group) {
    $html .= "GROUP: {$group['name_th']}\n";
    $renderItems($group['items'] ?? [], 0);
}

file_put_contents('scripts/render_debug.txt', $html);
echo "Render Debug Saved.\n";
