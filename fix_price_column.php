<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    echo "Prüfe Tabelle 'invoice_items'...\n";
    $pdo = Database::getConnection();

    // Get all column names
    $stmt = $pdo->query("SHOW COLUMNS FROM invoice_items");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check scenarios
    if (in_array('price', $columns)) {
        // Scenario A: Already correct
        echo "Alles OK: Spalte 'price' existiert bereits.\n";

    } elseif (in_array('unit_price', $columns)) {
        // Scenario B: Rename unit_price -> price
        // Note: We should preserve the type. Usually DECIMAL(10,2).
        // Let's check the type of unit_price first to be safe, or just enforce DECIMAL(10,2) as per request.
        $pdo->exec("ALTER TABLE invoice_items CHANGE unit_price price DECIMAL(10,2)");
        echo "Erfolg: Spalte 'unit_price' wurde in 'price' umbenannt.\n";

    } elseif (in_array('single_price', $columns)) {
        // Scenario B variant
        $pdo->exec("ALTER TABLE invoice_items CHANGE single_price price DECIMAL(10,2)");
        echo "Erfolg: Spalte 'single_price' wurde in 'price' umbenannt.\n";

    } elseif (in_array('amount', $columns)) {
        // Scenario B variant
        $pdo->exec("ALTER TABLE invoice_items CHANGE amount price DECIMAL(10,2)");
        echo "Erfolg: Spalte 'amount' wurde in 'price' umbenannt.\n";

    } elseif (in_array('preis', $columns)) {
        // Scenario B variant
        $pdo->exec("ALTER TABLE invoice_items CHANGE preis price DECIMAL(10,2)");
        echo "Erfolg: Spalte 'preis' wurde in 'price' umbenannt.\n";

    } else {
        // Scenario C: Create new
        $pdo->exec("ALTER TABLE invoice_items ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER description");
        echo "Erfolg: Spalte 'price' wurde neu erstellt (war nicht vorhanden).\n";
    }

} catch (Throwable $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
    exit(1);
}
