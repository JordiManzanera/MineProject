<?php
header('Content-Type: application/json');

// Obtener los datos JSON
$data = json_decode(file_get_contents('php://input'), true);
$serverName = $data['serverName'] ?? '';
$action = $data['action'] ?? '';

if (empty($serverName) || empty($action)) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

// Validar la acción
$validActions = ['start', 'stop', 'restart'];
if (!in_array($action, $validActions)) {
    echo json_encode([
        'success' => false,
        'message' => 'Acción no válida'
    ]);
    exit;
}

// Ejecutar el comando correspondiente
$output = [];
$return_var = 0;

switch ($action) {
    case 'start':
        exec("podman start $serverName", $output, $return_var);
        break;
    case 'stop':
        exec("podman stop $serverName", $output, $return_var);
        break;
    case 'restart':
        exec("podman restart $serverName", $output, $return_var);
        break;
}

if ($return_var === 0) {
    echo json_encode([
        'success' => true,
        'message' => "Servidor $action correctamente"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Error al $action el servidor: " . implode("\n", $output)
    ]);
}
?> 