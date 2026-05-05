<?php
require_once __DIR__ . '/include/db.php';
$rows = $conn->query("SELECT * FROM stagaires")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre style='font-size:14px;padding:20px;'>";
echo "Total rows: " . count($rows) . "\n\n";
foreach ($rows as $r) {
    foreach ($r as $k => $v) {
        echo str_pad($k, 30) . ": " . $v . "\n";
    }
    echo "─────────────────────────────────────────\n";
}
echo "</pre>";