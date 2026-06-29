<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = array_map(fn($t) => array_values((array)$t)[0], DB::select('SHOW TABLES'));
    echo "TABLES:\n";
    print_r($tables);

    foreach ($tables as $table) {
        echo "\nTABLE: $table\n";
        $columns = DB::select("SHOW COLUMNS FROM `$table`");
        foreach ($columns as $column) {
            echo "  - {$column->Field}: {$column->Type} | Null: {$column->Null} | Key: {$column->Key} | Default: " . json_encode($column->Default) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
