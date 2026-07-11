<?php
require_once __DIR__ . '/koneksi.php';

applyCommonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJson([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan GET.',
    ], 405);
}

try {
    $pdo = getDbConnection();

    $statement = $pdo->query("
        SELECT
            video_id, title, description, channel_title, published_at,
            thumbnail, view_count, like_count, duration, raw_json,
            created_at, updated_at
        FROM videos
        ORDER BY created_at DESC
    ");

    $rows = $statement->fetchAll();
    foreach ($rows as &$row) {
        if (!empty($row['raw_json'])) {
            $decoded = json_decode($row['raw_json'], true);
            $row['raw_json'] = $decoded ?? [];
        } else {
            $row['raw_json'] = [];
        }
    }
    unset($row);

    sendJson([
        'success' => true,
        'data' => $rows,
    ]);
} catch (Throwable $e) {
    sendJson([
        'success' => false,
        'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
    ], 500);
}
