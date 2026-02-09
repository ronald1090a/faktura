<?php
// Datenbankverbindung laden
// Pfad ggf. anpassen, falls Config woanders liegt

// Load Autoloader and Env (Adapted for this project's structure)
require __DIR__ . '/../vendor/autoload.php';

// Manually load .env as we are not going through index.php
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Throwable $e) {
    // If .env loading fails (e.g. file missing), we might rely on system envs or fail.
    // Proceeding to let Database class handle it or throw exception.
}

use App\Core\Database;

echo "<h1>PIM Installation & Setup</h1>";
echo "<p>Verbinde zur Datenbank...</p>";

try {
    $pdo = Database::getConnection();
} catch (\Throwable $e) {
    die("<div style='color:red'>Fehler: Datenbankverbindung konnte nicht hergestellt werden. " . $e->getMessage() . "</div>");
}

$files = [
    __DIR__ . '/../database/pim_schema.sql',
    __DIR__ . '/../database/pim_seed.sql'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $sql = file_get_contents($file);
        try {
            // Wir teilen Statements am Semikolon, falls PDO exec() Probleme mit multiplen Statements hat
            // (Einfache Variante für Migrationen)
            $pdo->exec($sql);
            echo "<div style='color:green'>Datei <b>" . basename($file) . "</b> erfolgreich importiert.</div>";
        } catch (PDOException $e) {
            echo "<div style='color:red'>Fehler bei <b>" . basename($file) . "</b>: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div style='color:red'>Datei nicht gefunden: $file</div>";
    }
}

echo "<hr><h3>Installation abgeschlossen.</h3>";
echo "<p>Du kannst diese Datei (public/install_pim.php) nun löschen.</p>";
echo "<a href='index.php'>Zurück zum Dashboard</a>";
