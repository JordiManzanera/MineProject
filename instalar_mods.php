<?php
header('Content-Type: application/json');

// Obtener los datos JSON
$data = json_decode(file_get_contents('php://input'), true);
$nombre_servidor = $data['serverName'] ?? '';
$mods = $data['mods'] ?? [];

if (empty($nombre_servidor)) {
    echo json_encode([
        'success' => false,
        'message' => 'Falta el nombre del servidor'
    ]);
    exit;
}

// Esperar a que el servidor esté listo
sleep(30);

// Construir el comando con los mods seleccionados
$script_path = __DIR__ . '/instalar_mods.sh';
$comando = "bash " . escapeshellarg($script_path) . " " . escapeshellarg($nombre_servidor);
foreach ($mods as $mod) {
    $comando .= " " . escapeshellarg($mod);
}

// Ejecutar el script de instalación de mods
exec($comando, $output, $return_var);

if ($return_var !== 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al instalar los mods: ' . implode("\n", $output)
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Mods instalados correctamente',
    'mods_instalados' => $mods
]);
?> 