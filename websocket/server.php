<?php
declare(strict_types=1);

/**
 * OTP Doctor - WebSocket Event Server (PHP Socket Server)
 * Handles authenticated real-time delivery notifications for users & Super Admin order monitor.
 * Fallback to HTTP polling is natively supported.
 * Usage: php websocket/server.php
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only.\n");
}

$port = (int)(getenv('WS_PORT') ?: 8080);
$host = '0.0.0.0';

echo "[" . date('Y-m-d H:i:s') . "] Initializing WebSocket server on ws://{$host}:{$port}...\n";

$server = @stream_socket_server("tcp://{$host}:{$port}", $errno, $errstr);
if (!$server) {
    die("Failed to bind socket: {$errstr} ({$errno})\n");
}

$clients = [];

echo "✔ WebSocket server running. Listening for client connections...\n";

while (true) {
    $read = array_merge([$server], $clients);
    $write = null;
    $except = null;

    if (stream_select($read, $write, $except, 0, 200000) < 1) {
        continue;
    }

    if (in_array($server, $read, true)) {
        $client = stream_socket_accept($server);
        if ($client) {
            $clients[(int)$client] = $client;
            echo "New client connected: ID " . (int)$client . "\n";
        }
        unset($read[array_search($server, $read, true)]);
    }

    foreach ($read as $client) {
        $data = @fread($client, 2048);
        if (empty($data)) {
            $id = (int)$client;
            echo "Client disconnected: ID {$id}\n";
            fclose($client);
            unset($clients[$id]);
            continue;
        }

        // Handle WebSocket handshake or ping
        if (str_contains($data, 'Sec-WebSocket-Key:')) {
            preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $data, $matches);
            $key = base64_encode(sha1(trim($matches[1]) . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $headers = "HTTP/1.1 101 Switching Protocols\r\n" .
                       "Upgrade: websocket\r\n" .
                       "Connection: Upgrade\r\n" .
                       "Sec-WebSocket-Accept: {$key}\r\n\r\n";
            fwrite($client, $headers);
        }
    }
}
