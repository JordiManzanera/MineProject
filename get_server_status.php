<?php
header('Content-Type: application/json');

// Obtener la lista de contenedores
$output = [];
$return_var = 0;
exec('podman ps -a --format "{{.Names}},{{.Status}}"', $output, $return_var);

$servers = [];

if ($return_var === 0) {
    foreach ($output as $line) {
        list($name, $status) = explode(',', $line);
        
        // Obtener el puerto del contenedor
        $port_output = [];
        exec("podman port $name", $port_output);
        $port = 'No disponible';
        if (!empty($port_output)) {
            $port = explode(':', $port_output[0])[1];
        }
        
        // Determinar el estado
        $server_status = 'unknown';
        if (strpos($status, 'Up') !== false) {
            $server_status = 'running';
        } else if (strpos($status, 'Exited') !== false) {
            $server_status = 'stopped';
        }
        
        $servers[] = [
            'name' => $name,
            'port' => $port,
            'status' => $server_status
        ];
    }
}

echo json_encode([
    'success' => true,
    'servers' => $servers
]);
?> 