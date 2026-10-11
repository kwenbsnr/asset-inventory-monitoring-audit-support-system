<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Starting database schema integration...\n";

    // 1. Ensure photo_path in assets is nullable
    DB::statement("ALTER TABLE assets MODIFY COLUMN photo_path VARCHAR(255) NULL DEFAULT NULL");
    echo "1. assets.photo_path set to nullable.\n";

    // 2. Expand assets.status enum
    DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('active','inactive','maintenance','transferred','for_disposal','pending_disposal','disposed','missing') NOT NULL DEFAULT 'active'");
    echo "2. assets.status expanded successfully.\n";

    // 3. Expand assets.condition enum
    DB::statement("ALTER TABLE assets MODIFY COLUMN `condition` ENUM('A1','A2','A3','R','good','fair','poor','for_repair','unserviceable') NOT NULL DEFAULT 'A1'");
    echo "3. assets.condition expanded successfully.\n";

    // 4. Expand asset_custodies.status enum
    DB::statement("ALTER TABLE asset_custodies MODIFY COLUMN status ENUM('active','returned','transferred','relieved','cancelled','ended') NOT NULL DEFAULT 'active'");
    echo "4. asset_custodies.status expanded successfully.\n";

    // 5. Expand users.role enum
    DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','asset_manager','supply_officer','inspection_officer') NOT NULL");
    echo "5. users.role expanded successfully.\n";

    // 6. Add updated_at to users if not present
    $usersCols = collect(DB::select("SHOW COLUMNS FROM users"))->pluck('Field')->toArray();
    if (!in_array('updated_at', $usersCols)) {
        DB::statement("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "6. users.updated_at added.\n";
    }

    // 7. Add ip_address to audit_trail if not present
    $auditCols = collect(DB::select("SHOW COLUMNS FROM audit_trail"))->pluck('Field')->toArray();
    if (!in_array('ip_address', $auditCols)) {
        DB::statement("ALTER TABLE audit_trail ADD COLUMN ip_address VARCHAR(45) NULL AFTER new_values");
        echo "7. audit_trail.ip_address added.\n";
    }

    // 8. Add scan_purpose and device_info to qr_scans if not present
    $qrCols = collect(DB::select("SHOW COLUMNS FROM qr_scans"))->pluck('Field')->toArray();
    if (!in_array('scan_purpose', $qrCols)) {
        DB::statement("ALTER TABLE qr_scans ADD COLUMN scan_purpose VARCHAR(50) NULL DEFAULT 'verification' AFTER scanned_by");
        echo "8. qr_scans.scan_purpose added.\n";
    }
    if (!in_array('device_info', $qrCols)) {
        DB::statement("ALTER TABLE qr_scans ADD COLUMN device_info TEXT NULL AFTER scan_purpose");
        echo "9. qr_scans.device_info added.\n";
    }

    // 9. Add purpose to asset_transfers if not present
    $trfCols = collect(DB::select("SHOW COLUMNS FROM asset_transfers"))->pluck('Field')->toArray();
    if (!in_array('purpose', $trfCols)) {
        DB::statement("ALTER TABLE asset_transfers ADD COLUMN purpose VARCHAR(255) NULL AFTER transfer_number");
        echo "10. asset_transfers.purpose added.\n";
    }

    // 10. Create asset_categories view
    DB::statement("CREATE OR REPLACE VIEW asset_categories AS SELECT asset_accounts_id AS id, asset_accounts_id, account_name AS name, account_code AS code, created_at FROM asset_accounts");
    echo "11. asset_categories view created.\n";

    // 11. Create inventory_line_items view
    DB::statement("CREATE OR REPLACE VIEW inventory_line_items AS SELECT record_id AS id, record_id, cycle_id, asset_id, verification_status AS verified_status, verification_status, asset_condition AS verified_condition, asset_condition, findings, discrepancy_type, verified_by, verified_at, created_at, updated_at FROM verification_records");
    echo "12. inventory_line_items view created.\n";

    echo "All database integration steps completed successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
