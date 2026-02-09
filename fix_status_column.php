<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $pdo = Database::getConnection();

    // Increase status column size to support 'CANCELLED' (which is 9 chars, but 20 is safer)
    $pdo->exec("ALTER TABLE invoices MODIFY COLUMN status VARCHAR(20) DEFAULT 'DRAFT'");

    echo "Datenbank-Spalte 'status' erfolgreich vergrößert. Fehler behoben.\n";

} catch (Throwable $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
    exit(1);
}
