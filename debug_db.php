<?php
require_once "config/db.php";

// Récupérer toutes les tables
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "<pre style='background:#111;color:#d4a72c;padding:20px;font-family:monospace;'>";
echo "=== STRUCTURE BASE DE DONNÉES ===\n\n";

foreach ($tables as $table) {
    echo "TABLE: <strong>$table</strong>\n";
    echo "---\n";
    
    $columns = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'NO' ? '[NOT NULL]' : '[NULL]') . "\n";
    }
    
    // Afficher quelques données d'exemple
    $rows = $conn->query("SELECT * FROM $table LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo "\n  Exemple données:\n";
        foreach ($rows as $row) {
            echo "    " . json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    echo "\n";
}

echo "</pre>";
?>
