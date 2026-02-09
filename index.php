<?php

declare(strict_types=1);

// Start session FIRST for authentication
session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\Modules\Faktura\Controllers\InvoiceController;
use App\Modules\Faktura\Controllers\CustomerController;
use App\Modules\User\UserController;
use App\Modules\Auth\AuthController;
use App\Modules\Favorites\FavoritesController;
use App\Modules\Settings\SettingsController;
use App\Modules\Pim\Controllers\PimController;

// -----------------------------------------------------------------------------
// 1. Manual .env Loading
// -----------------------------------------------------------------------------
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Populate getenv() and $_ENV
            if (!getenv($key)) {
                putenv("$key=$value");
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// -----------------------------------------------------------------------------
// 2. Authentication Middleware
// -----------------------------------------------------------------------------
// Default action
$action = $_GET['action'] ?? 'dashboard';

// Public routes that don't require authentication
$publicRoutes = ['login', 'authenticate'];

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) && !in_array($action, $publicRoutes)) {
    header('Location: index.php?action=login');
    exit;
}

// -----------------------------------------------------------------------------
// 3. Routing Logic
// -----------------------------------------------------------------------------
try {

    switch ($action) {
        // Dashboard
        case 'dashboard':
            // Simple view include, no controller needed for static dashboard yet
            include __DIR__ . '/../views/dashboard.php';
            break;

        // Auth routes
        case 'login':
            $controller = new AuthController();
            $controller->login();
            break;

        case 'authenticate':
            $controller = new AuthController();
            $controller->authenticate();
            break;

        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;

        // Invoice routes
        case 'list_invoices':
        case 'index':
            $controller = new InvoiceController(\App\Core\Database::getConnection());
            $controller->index();
            break;

        // Formular anzeigen
        case 'create_invoice':
        case 'create':
            $controller = new InvoiceController(\App\Core\Database::getConnection());
            $controller->create();
            break;

        // Formular speichern (POST)
        case 'store_invoice':
        case 'store':
            $controller = new InvoiceController(\App\Core\Database::getConnection());
            $controller->store();
            break;

        // PDF Download
        case 'download_pdf':
            $controller = new InvoiceController(\App\Core\Database::getConnection());
            $controller->downloadPdf();
            break;

        // XML Download
        case 'download_xml':
            $controller = new InvoiceController(\App\Core\Database::getConnection());
            $controller->downloadXml();
            break;

        // Stornieren
        case 'cancel_invoice':
            $controller = new InvoiceController(\App\Core\Database::getConnection());
            $controller->cancel();
            break;

        // --- KUNDEN (Customer Management) ---

        // Liste anzeigen
        case 'customers':
        case 'list_customers':
            $controller = new \App\Modules\Customer\Controllers\CustomerController($pdo);
            $controller->index();
            break;

        // Erstellen (Formular)
        case 'create_customer':
            $controller = new \App\Modules\Customer\Controllers\CustomerController($pdo);
            $controller->create();
            break;

        // Speichern (Neu)
        case 'store_customer':
            $controller = new \App\Modules\Customer\Controllers\CustomerController($pdo);
            $controller->store();
            break;

        // Bearbeiten (Formular)
        case 'edit_customer':
            $controller = new \App\Modules\Customer\Controllers\CustomerController($pdo);
            $controller->edit();
            break;

        // Speichern (Update) - Hier fing der Fehler "update" ab
        case 'update':
        case 'update_customer':
            $controller = new \App\Modules\Customer\Controllers\CustomerController($pdo);
            $controller->update();
            break;

        // Löschen
        case 'delete_customer':
            $controller = new \App\Modules\Customer\Controllers\CustomerController($pdo);
            $controller->delete();
            break;

        // User routes
        case 'list_users':
            $controller = new UserController();
            $controller->index();
            break;

        case 'create_user':
            $controller = new UserController();
            $controller->create();
            break;

        case 'store_user':
            $controller = new UserController();
            $controller->store();
            break;

        case 'edit_user':
            $controller = new UserController();
            $controller->edit();
            break;

        case 'update_user':
            $controller = new UserController();
            $controller->update();
            break;

        case 'delete_user':
            $controller = new UserController();
            $controller->delete();
            break;

        // Favorites routes
        case 'favorites':
            $controller = new FavoritesController();
            $controller->index();
            break;

        case 'store_favorite':
            $controller = new FavoritesController();
            $controller->store();
            break;

        case 'update_favorite':
            $controller = new FavoritesController();
            $controller->update();
            break;

        case 'delete_favorite':
            $controller = new FavoritesController();
            $controller->delete();
            break;

        // API routes
        case 'api_customer_search':
            $controller = new CustomerController();
            $controller->search();
            break;

        // Settings Routes
        case 'settings':
            $controller = new SettingsController();
            $controller->index();
            break;

        case 'store_shipping':
            $controller = new SettingsController();
            $controller->store_shipping();
            break;

        case 'delete_shipping':
            $controller = new SettingsController();
            $controller->delete_shipping();
            break;

        case 'store_payment':
            $controller = new SettingsController();
            $controller->store_payment();
            break;

        case 'delete_payment':
            $controller = new SettingsController();
            $controller->delete_payment();
            break;

        // PIM Routes
        case 'list_products':
        case 'pim_list':
            $controller = new PimController(\App\Core\Database::getConnection());
            $controller->index();
            break;

        case 'pim_show':
            $controller = new PimController(\App\Core\Database::getConnection());
            $controller->show();
            break;

        default:
            http_response_code(404);
            echo "Seite nicht gefunden.";
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);

    // Use layout for nice error page if possible, or fallback to simple HTML
    // Simple Error View
    ?>
    <!DOCTYPE html>
    <html lang="de">

    <head>
        <meta charset="UTF-8">
        <title>Fehler</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-red-50 p-10 font-sans">
        <div class="max-w-xl mx-auto bg-white p-8 rounded shadow text-center">
            <h1 class="text-3xl font-bold text-red-600 mb-4">Ein Fehler ist aufgetreten</h1>
            <p class="text-gray-700">
                <?= htmlspecialchars($e->getMessage()) ?>
            </p>
            <a href="/" class="mt-6 inline-block bg-gray-500 hover:bg-gray-700 text-white py-2 px-4 rounded">Zurück zur
                Startseite</a>
        </div>
    </body>

    </html>
    <?php
}
