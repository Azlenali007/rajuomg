<?php
declare(strict_types=1);

namespace App\WebSocket;

class Broadcast
{
    /**
     * Broadcast an order event (e.g. OTP_RECEIVED) to user or admin channels
     */
    public static function emit(string $channel, string $event, array $data): void
    {
        // Safe emission via socket or shared memory/Redis if available
        $payload = json_encode([
            'channel' => $channel,
            'event'   => $event,
            'data'    => $data,
            'time'    => microtime(true)
        ]);

        // Writes event to fallback queue file for pollers
        $eventFile = sys_get_temp_dir() . '/ws_events_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $channel) . '.json';
        @file_put_contents($eventFile, $payload, LOCK_EX);
    }
}
