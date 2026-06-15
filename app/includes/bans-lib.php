<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function mineacle_table(string $name): string {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) throw new RuntimeException('Invalid table name');
    return $name;
}

function mineacle_table_exists(PDO $pdo, string $table): bool {
    $stmt = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($table));
    return (bool) $stmt->fetchColumn();
}

function mineacle_column_exists(PDO $pdo, string $table, string $column): bool {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $column)) return false;
    try {
        $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . mineacle_table($table) . '` LIKE :column');
        $stmt->execute(['column' => $column]);
        return (bool) $stmt->fetch();
    } catch (Throwable) {
        return false;
    }
}

function mineacle_skin_url(string $value): string {
    $value = trim($value);
    if ($value === '' || str_starts_with($value, '#')) $value = 'Steve';
    return 'https://mc-heads.net/avatar/' . rawurlencode($value) . '/128';
}

function mineacle_format_date(mixed $millis): string {
    $millis = (int) $millis;
    if ($millis <= 0) return 'Unknown';
    return date('M j, Y g:i A', (int) floor($millis / 1000));
}

function mineacle_format_duration(mixed $until, mixed $time): string {
    $until = (int) $until;
    $time = (int) $time;
    if ($until <= 0) return 'Permanent';

    $seconds = max(0, (int) floor(($until - $time) / 1000));
    if ($seconds <= 0) return 'Expired';

    $days = intdiv($seconds, 86400);
    if ($days > 0) return $days . ' day' . ($days === 1 ? '' : 's');

    $hours = intdiv($seconds, 3600);
    if ($hours > 0) return $hours . ' hour' . ($hours === 1 ? '' : 's');

    $minutes = max(1, intdiv($seconds, 60));
    return $minutes . ' minute' . ($minutes === 1 ? '' : 's');
}

function mineacle_lookup_name(PDO $pdo, string $historyTable, string $uuid, string $fallback = ''): string {
    $fallback = trim($fallback);
    $uuid = trim($uuid);

    if ($uuid === '') return $fallback !== '' ? $fallback : '#offline#';
    if (!mineacle_table_exists($pdo, $historyTable)) return $fallback !== '' ? $fallback : '#offline#';
    if (!mineacle_column_exists($pdo, $historyTable, 'uuid') || !mineacle_column_exists($pdo, $historyTable, 'name')) return $fallback !== '' ? $fallback : '#offline#';

    try {
        $order = mineacle_column_exists($pdo, $historyTable, 'date') ? ' ORDER BY `date` DESC' : '';
        $stmt = $pdo->prepare('SELECT `name` FROM `' . mineacle_table($historyTable) . '` WHERE `uuid` = :uuid' . $order . ' LIMIT 1');
        $stmt->execute(['uuid' => $uuid]);
        $name = trim((string) ($stmt->fetchColumn() ?: ''));
        if ($name !== '') return $name;
    } catch (Throwable) {}

    return $fallback !== '' ? $fallback : '#offline#';
}

function fetch_litebans_bans_page(string $search = '', int $page = 1): array {
    $config = mineacle_config();
    $pdo = mineacle_db();
    $litebans = $config['litebans'] ?? [];

    $bansTable = mineacle_table((string) ($litebans['bans_table'] ?? 'litebans_bans'));
    $historyTable = mineacle_table((string) ($litebans['history_table'] ?? 'litebans_history'));

    if (!mineacle_table_exists($pdo, $bansTable)) throw new RuntimeException('LiteBans bans table not found');

    foreach (['id', 'uuid', 'reason', 'time', 'until', 'active', 'ipban'] as $column) {
        if (!mineacle_column_exists($pdo, $bansTable, $column)) throw new RuntimeException('LiteBans bans table is missing required columns');
    }

    $limit = max(1, min(100, (int) ($config['page']['limit'] ?? 25)));
    $page = max(1, $page);
    $offset = ($page - 1) * $limit;
    $nowMillis = (int) floor(microtime(true) * 1000);

    $hasHistory = mineacle_table_exists($pdo, $historyTable)
        && mineacle_column_exists($pdo, $historyTable, 'uuid')
        && mineacle_column_exists($pdo, $historyTable, 'name');
    $hasBanName = mineacle_column_exists($pdo, $bansTable, 'name');

    $where = ['`active` = 1', '(`until` <= 0 OR `until` > :now)'];
    $params = ['now' => $nowMillis];

    $search = trim($search);
    if ($search !== '') {
        $params['search'] = '%' . $search . '%';
        $parts = ['`uuid` LIKE :search'];
        if ($hasBanName) $parts[] = '`name` LIKE :search';
        if ($hasHistory) $parts[] = '`uuid` IN (SELECT `uuid` FROM `' . mineacle_table($historyTable) . '` WHERE `name` LIKE :search)';
        $where[] = '(' . implode(' OR ', $parts) . ')';
    }

    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM `' . mineacle_table($bansTable) . '` ' . $whereSql);
    foreach ($params as $key => $value) $countStmt->bindValue(':' . $key, $value);
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $select = ['`id`', '`uuid`', '`reason`', '`time`', '`until`', '`active`', '`ipban`', $hasBanName ? '`name`' : 'NULL AS `name`'];
    $stmt = $pdo->prepare('SELECT ' . implode(', ', $select) . ' FROM `' . mineacle_table($bansTable) . '` ' . $whereSql . ' ORDER BY `time` DESC LIMIT :limit OFFSET :offset');

    foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $bans = [];
    foreach ($stmt->fetchAll() as $row) {
        $uuid = trim((string) ($row['uuid'] ?? ''));
        $name = mineacle_lookup_name($pdo, $historyTable, $uuid, (string) ($row['name'] ?? ''));
        $isIpBan = ((int) ($row['ipban'] ?? 0)) === 1;
        $until = (int) ($row['until'] ?? 0);
        $time = (int) ($row['time'] ?? 0);
        $permanent = $until <= 0;

        $bans[] = [
            'id' => (string) ($row['id'] ?? ''),
            'uuid' => $uuid,
            'username' => $isIpBan && $name === '#offline#' ? '#offline#' : $name,
            'skin' => mineacle_skin_url($uuid !== '' ? $uuid : $name),
            'reason' => (string) ($row['reason'] ?? 'No reason provided'),
            'type' => $isIpBan ? 'IP Ban' : 'Player Ban',
            'duration' => mineacle_format_duration($until, $time),
            'date' => mineacle_format_date($time),
            'status' => $isIpBan || $permanent ? 'Permanently Banned' : 'Active',
            'status_type' => $isIpBan ? 'ip' : 'active',
            'ipban' => $isIpBan,
            'appeal_id' => 'MCL-' . str_pad((string) ($row['id'] ?? '0'), 6, '0', STR_PAD_LEFT),
            'support_email' => (string) ($config['site']['support_email'] ?? 'support@mineacle.net'),
            'discord' => (string) ($config['site']['discord'] ?? 'https://discord.gg/4xrYFxdSWg'),
            'can_pay' => !$isIpBan,
            'price' => $permanent ? (string) ($config['payments']['permanent_unban_price'] ?? '$19.99') : (string) ($config['payments']['temporary_unban_price'] ?? '$9.99'),
            'unban_url' => strtr((string) ($config['site']['unban_checkout_url'] ?? '#'), ['{id}' => (string) ($row['id'] ?? ''), '{uuid}' => $uuid, '{username}' => $name]),
        ];
    }

    $totalPages = max(1, (int) ceil($total / $limit));

    return [
        'bans' => $bans,
        'pagination' => [
            'page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ],
    ];
}
