<?php
// Wir bauen die Verbindung direkt auf, um Pfad-Probleme zu umgehen.
$host = '127.0.0.1';
$db = 'faktura_db'; // Standard-Name in diesem Projekt
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

echo "<h1>Datenbank Reparatur & Update</h1>";

try {
    // 1. Verbindung aufbauen
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color:green'>✔ Verbindung zur Datenbank '$db' erfolgreich.</p>";

    // 2. Spalte 'street' prüfen
    try {
        $pdo->query("SELECT street FROM customers LIMIT 1");
        echo "<p style='color:green'>✔ Spalte 'street' existiert bereits.</p>";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN street VARCHAR(255) NULL AFTER customer_number");
        echo "<p style='color:blue'>+ Spalte 'street' wurde hinzugefügt.</p>";
    }

    // 3. Spalte 'zip' prüfen
    try {
        $pdo->query("SELECT zip FROM customers LIMIT 1");
        echo "<p style='color:green'>✔ Spalte 'zip' existiert bereits.</p>";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN zip VARCHAR(20) NULL AFTER street");
        echo "<p style='color:blue'>+ Spalte 'zip' wurde hinzugefügt.</p>";
    }

    // 4. Spalte 'city' prüfen
    try {
        $pdo->query("SELECT city FROM customers LIMIT 1");
        echo "<p style='color:green'>✔ Spalte 'city' existiert bereits.</p>";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN city VARCHAR(100) NULL AFTER zip");
        echo "<p style='color:blue'>+ Spalte 'city' wurde hinzugefügt.</p>";
    }

    echo "<hr><h3>Reparatur abgeschlossen!</h3>";
    echo "<p>Du kannst jetzt <a href='index.php'>zurück zum System</a> und Rechnungen erstellen.</p>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red'>Verbindungsfehler</h3>";
    echo "Konnte nicht zu Datenbank '$db' verbinden.<br>";
    echo "Fehler: " . $e->getMessage() . "<br><br>";
    echo "Falls deine Datenbank anders heißt, ändere bitte Zeile 4 in dieser Datei.";
}
