<?php
// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar la solicitud OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

// Recibir y validar los datos del formulario
$rawData = file_get_contents('php://input');
if (!$rawData) {
    echo json_encode([
        'success' => false,
        'message' => 'No se recibieron datos'
    ]);
    exit;
}

$data = json_decode($rawData, true);
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al decodificar los datos JSON'
    ]);
    exit;
}

// Validar campos requeridos
if (empty($data['serverName']) || empty($data['port']) || empty($data['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan campos requeridos'
    ]);
    exit;
}

$serverName = $data['serverName'];
$port = $data['port'];
$username = $data['username'];

// Obtener el último número de servidor
$lastNumber = 1;
if (file_exists('last_server_number.txt')) {
    $lastNumber = (int)file_get_contents('last_server_number.txt');
    $lastNumber++;
}
file_put_contents('last_server_number.txt', $lastNumber);

$imageName = "servidor-" . $lastNumber;

// Crear el contenido del script bash
$scriptContent = "#!/bin/bash\n\n";
$scriptContent .= "# Crear Containerfile\n";
$scriptContent .= "cat > Containerfile << 'EOF'\n";
$scriptContent .= "FROM ghcr.io/ronoaldo/luantiserver:stable\n";
$scriptContent .= "USER root\n";
$scriptContent .= "RUN apt install nano\n";
$scriptContent .= "USER luanti\n";
$scriptContent .= "EOF\n\n";

$scriptContent .= "# Construir la imagen\n";
$scriptContent .= "podman build -t $imageName .\n\n";

$scriptContent .= "# Ejecutar el servidor\n";
$scriptContent .= "podman run -d --name $serverName -p $port:30000/udp $imageName\n";

// Guardar el script con un nombre único
$scriptName = "create_server_" . time() . ".sh";
file_put_contents($scriptName, $scriptContent);
chmod($scriptName, 0755);

// Ejecutar el script
$output = [];
$return_var = 0;
exec("./$scriptName 2>&1", $output, $return_var);

if ($return_var !== 0) {
    echo json_encode([
        'success' => false,
        'message' => "Error al ejecutar el script:\n" . implode("\n", $output)
    ]);
    exit;
}

// Devolver la respuesta
echo json_encode([
    'success' => true,
    'message' => "Servidor creado correctamente:\n" .
                "Nombre del servidor: $serverName\n" .
                "Puerto: $port\n" .
                "Imagen: $imageName\n\n" .
                "Salida del script:\n" . implode("\n", $output)
]);

// Opcionalmente, eliminar el script después de ejecutarlo
unlink($scriptName);
?> 