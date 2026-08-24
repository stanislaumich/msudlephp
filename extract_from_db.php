<?php

$db = new PDO('sqlite:C:/Users/Lz42/.local/share/kilo/kilo.db');

// List tables
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
foreach ($tables as $t) echo "  - $t\n";

// Search for view files in all text columns
foreach ($tables as $table) {
    $cols = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        if (in_array($col['type'], ['TEXT', 'text'])) {
            $stmt = $db->prepare("SELECT rowid, " . $col['name'] . " FROM " . $table . " WHERE " . $col['name'] . " LIKE ? LIMIT 5");
            $stmt->execute(['%app/views/students/list.php%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                echo "\nFound in $table.$" . $col['name'] . ":\n";
                foreach ($results as $r) {
                    echo "  RowID: " . $r['rowid'] . ", length: " . strlen($r[$col['name']]) . "\n";
                }
            }
        }
    }
}
