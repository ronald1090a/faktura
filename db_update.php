<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

// 1. Manual .env Loading (copied from index.php logic)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
}

echo "<html><body style='font-family: sans-serif; padding: 20px; line-height: 1.5;'>";
echo "<h2>Datenbank-Update-Skript</h2>";

try {
    $pdo = Database::getConnection();
    echo "✅ Datenbankverbindung erfolgreich.<br><br>";

    $commands = [
        // Invoices Table
        "ALTER TABLE invoices ADD COLUMN delivery_date DATE NULL AFTER invoice_date",
        "ALTER TABLE invoices ADD COLUMN our_reference VARCHAR(50) NULL AFTER invoice_number",
        "ALTER TABLE invoices ADD COLUMN shipping_method VARCHAR(50) NULL",
        "ALTER TABLE invoices ADD COLUMN payment_condition VARCHAR(50) NULL",
        "ALTER TABLE invoices ADD COLUMN has_delivery_address TINYINT(1) DEFAULT 0",
        "ALTER TABLE invoices ADD COLUMN delivery_company VARCHAR(255) NULL",
        "ALTER TABLE invoices ADD COLUMN delivery_street VARCHAR(255) NULL",
        "ALTER TABLE invoices ADD COLUMN delivery_zip VARCHAR(20) NULL",
        "ALTER TABLE invoices ADD COLUMN delivery_city VARCHAR(100) NULL",

        // Invoice Items Table
        "ALTER TABLE invoice_items ADD COLUMN article_number VARCHAR(50) NULL AFTER invoice_id",
        "ALTER TABLE invoice_items ADD COLUMN ean VARCHAR(50) NULL AFTER article_number"
    ];

    foreach ($commands as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Befehl erfolgreich: <code style='background: #e6fffa; padding: 2px 5px;'>" . htmlspecialchars($sql) . "</code><br>";
        } catch (PDOException $e) {
            // Check for "Duplicate column name" error (Code 42S21 or similar text)
            if (str_contains($e->getMessage(), "Duplicate column name") || str_contains($e->getMessage(), "already exists")) {
                echo "ℹ️ Spalte existiert bereits (übersprungen): <code style='background: #f1f5f9; padding: 2px 5px; color: #64748b;'>" . htmlspecialchars($sql) . "</code><br>";
            } else {
                echo "❌ Fehler: " . htmlspecialchars($e->getMessage()) . "<br>";
            }
        }
    }

    echo "<h1 style='color: green; margin-top: 20px;'>Datenbank erfolgreich aktualisiert!</h1>";
    echo "<p>Du kannst dieses Fenster jetzt schließen und Faktura weiter nutzen.</p>";

} catch (Exception $e) {
    echo "<h1 style='color: red;'>Kritischer Fehler</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
