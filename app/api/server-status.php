<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/cache.php';

function status_varint(int $value): string { $bytes=''; do { $temp=$value & 0x7F; $value >>= 7; if ($value !== 0) $temp |= 0x80; $bytes .= chr($temp); } while ($value !== 0); return $bytes; }
function status_read_varint($socket): ?int { $value=0; $position=0; while (true) { $byte=fread($socket,1); if ($byte===false || $byte==='') return null; $current=ord($byte); $value |= ($current & 0x7F) << $position; if (($current & 0x80) !== 0x80) break; $position += 7; if ($position >= 35) return null; } return $value; }
function status_packet(string $payload): string { return status_varint(strlen($payload)) . $payload; }
function status_string(string $value): string { return status_varint(strlen($value)) . $value; }
function status_read_exact($socket, int $length): ?string { $buffer=''; while (strlen($buffer) < $length) { $chunk=fread($socket, $length - strlen($buffer)); if ($chunk===false || $chunk==='') return null; $buffer .= $chunk; } return $buffer; }

function mineacle_status_ping(string $queryHost, int $queryPort, string $publicHost, int $publicPort, float $timeout): array {
    $socket = @stream_socket_client('tcp://' . $queryHost . ':' . $queryPort, $errno, $error, $timeout, STREAM_CLIENT_CONNECT);
    if (!is_resource($socket)) throw new RuntimeException('Unable to connect to Minecraft server');
    stream_set_timeout($socket, 2);
    $handshake = status_varint(0) . status_varint(763) . status_string($publicHost) . pack('n', $publicPort) . status_varint(1);
    fwrite($socket, status_packet($handshake)); fwrite($socket, status_packet(status_varint(0)));
    $packetLength = status_read_varint($socket); if ($packetLength === null) { fclose($socket); throw new RuntimeException('Invalid status response'); }
    $packetId = status_read_varint($socket); if ($packetId !== 0) { fclose($socket); throw new RuntimeException('Unexpected status packet'); }
    $jsonLength = status_read_varint($socket); if ($jsonLength === null || $jsonLength < 1) { fclose($socket); throw new RuntimeException('Missing status payload'); }
    $json = status_read_exact($socket, $jsonLength); fclose($socket); if ($json === null) throw new RuntimeException('Incomplete status payload');
    $decoded = json_decode($json, true); if (!is_array($decoded)) throw new RuntimeException('Malformed status payload');
    $players = is_array($decoded['players'] ?? null) ? $decoded['players'] : [];
    return ['online' => true, 'host' => $publicHost, 'port' => $publicPort, 'players_online' => (int) ($players['online'] ?? 0), 'players_max' => (int) ($players['max'] ?? 0), 'updated_at' => time()];
}

$config = mineacle_config(); $ttl = (int) ($config['cache']['status_ttl'] ?? 20); $cacheKey = 'server_status_v6';
$cached = mineacle_cache_get($cacheKey, $ttl); if ($cached !== null) mineacle_json($cached, 200, $ttl);
$status = $config['minecraft_status'] ?? [];
$publicHost = (string) ($status['public_host'] ?? 'mineacle.net'); $publicPort = (int) ($status['public_port'] ?? 25565); $queryHost = (string) ($status['query_host'] ?? $publicHost); $queryPort = (int) ($status['query_port'] ?? $publicPort); $timeout = (float) ($status['timeout'] ?? 1.5);
try { $payload = mineacle_status_ping($queryHost, $queryPort, $publicHost, $publicPort, $timeout); }
catch (Throwable) { $payload = ['online' => false, 'host' => $publicHost, 'port' => $publicPort, 'players_online' => 0, 'players_max' => 0, 'updated_at' => time()]; }
mineacle_cache_set($cacheKey, $payload); mineacle_json($payload, 200, $ttl);
