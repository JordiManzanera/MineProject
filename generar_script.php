<?php
header('Content-Type: application/json');

// Obtener los datos del formulario
$nombre_usuario = $_POST['username'];
$nombre_servidor = $_POST['serverName'];
$puerto = $_POST['port'];

// Contenido del Dockerfile
$dockerfile_content = "FROM ghcr.io/ronoaldo/luantiserver:stable\nUSER root\nRUN apt install nano\nUSER luanti";

// Ejecutar los comandos en secuencia
$comandos = [
    // Crear directorio
    "echo 'usuario' | sudo -S mkdir /home/usuario/" . escapeshellarg($nombre_usuario),
    
    // Cambiar al directorio y crear Dockerfile
    "cd /home/usuario/" . escapeshellarg($nombre_usuario) . " && echo " . escapeshellarg($dockerfile_content) . " | sudo tee Dockerfile",
    
    // Dar permisos correctos
    "sudo chown -R usuario:usuario /home/usuario/" . escapeshellarg($nombre_usuario),
    
    // Construir la imagen
    "cd /home/usuario/" . escapeshellarg($nombre_usuario) . " && podman build -t server-" . escapeshellarg($nombre_usuario) . " .",
    
    // Ejecutar el servidor
    "podman run -d --name " . escapeshellarg($nombre_servidor) . " -p " . escapeshellarg($puerto) . ":30000/udp server-" . escapeshellarg($nombre_usuario)
];

$errores = [];
$exitoso = true;

foreach ($comandos as $comando) {
    exec($comando, $output, $return_var);
    if ($return_var !== 0) {
        $errores[] = "Error en comando: $comando\n" . implode("\n", $output);
        $exitoso = false;
    }
}

if (!$exitoso) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al ejecutar los comandos: ' . implode("\n\n", $errores)
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Servidor creado exitosamente',
    'details' => [
        'directorio' => '/home/usuario/' . $nombre_usuario,
        'nombre_servidor' => $nombre_servidor,
        'puerto' => $puerto,
        'imagen' => 'server-' . $nombre_usuario
    ]
]);
?> 