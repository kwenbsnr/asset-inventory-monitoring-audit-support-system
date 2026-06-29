<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('active','maintenance','transferred','for_disposal','missing') NOT NULL DEFAULT 'active'");
    echo "assets.status altered successfully.\n";

    DB::statement("ALTER TABLE assets MODIFY COLUMN `condition` ENUM('good','fair','poor','for_repair','unserviceable') NOT NULL DEFAULT 'good'");
    echo "assets.condition altered successfully.\n";

    DB::statement("ALTER TABLE asset_custodies MODIFY COLUMN status ENUM('active','relieved','cancelled','ended') NOT NULL DEFAULT 'active'");
    echo "asset_custodies.status altered successfully.\n";

    DB::statement("ALTER TABLE inventory_line_items MODIFY COLUMN verified_status ENUM('pending','verified','discrepancy','found','missing','unserviceable','transferred') NOT NULL");
    echo "inventory_line_items.verified_status altered successfully.\n";

    DB::statement("ALTER TABLE inventory_line_items MODIFY COLUMN verified_condition ENUM('good','fair','poor','for_repair','unserviceable') NULL DEFAULT NULL");
    echo "inventory_line_items.verified_condition altered successfully.\n";

    DB::statement("ALTER TABLE qr_scans MODIFY COLUMN scan_purpose ENUM('view','verify','transfer','inventory','audit') NOT NULL");
    echo "qr_scans.scan_purpose altered successfully.\n";

    echo "Database patch applied successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
