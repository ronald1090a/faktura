<?php
// Standalone DB connection
$host = '127.0.0.1';
$db = 'faktura_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

echo "<h1>Rechnungs-Summen Korrektur</h1>";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 0. Check & Add 'total_amount' column if missing
    try {
        $pdo->query("SELECT total_amount FROM invoices LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE invoices ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_gross");
        echo "<p style='color:blue'>+ Spalte 'total_amount' wurde zur Tabelle 'invoices' hinzugefügt.</p>";
    }

    // 1. Alle Rechnungen holen
    $invoices = $pdo->query("SELECT id, invoice_number FROM invoices")->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Berechne " . count($invoices) . " Rechnungen neu...</p>";

    foreach ($invoices as $inv) {
        // Summe der Items holen
        $stmt = $pdo->prepare("SELECT SUM(quantity * price) as net_sum FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([$inv['id']]);

        $net = (float) $stmt->fetchColumn();

        // Standard Tax 19%
        $tax = $net * 0.19;
        $gross = $net + $tax;

        // Update
        $upd = $pdo->prepare("UPDATE invoices SET total_net = ?, total_gross = ?, total_amount = ? WHERE id = ?");
        $upd->execute([$net, $gross, $gross, $inv['id']]);

        echo "Rechnung <strong>{$inv['invoice_number']}</strong>: Neuer Betrag " . number_format($gross, 2, ',', '.') . " €<br>";
    }

    echo "<hr><p>Fertig. <a href='index.php'>Zurück zur Übersicht</a></p>";

} catch (\PDOException $e) {
    die("Datenbank Fehler: " . $e->getMessage());
}
