<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$conn = \Illuminate\Support\Facades\DB::connection('erp_contiflex');

// Confirmar que existe y ver sus columnas
$cols = $conn->select("
    SELECT COLUMN_NAME, DATA_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'CRM_Consolidado_Ventas_cliente'
    ORDER BY ORDINAL_POSITION
");

if (empty($cols)) {
    echo "La tabla NO existe.\n";
} else {
    echo "Columnas de CRM_Consolidado_Ventas_cliente:\n";
    foreach ($cols as $c) {
        echo "  - $c->COLUMN_NAME ($c->DATA_TYPE)\n";
    }
}
