<?php
require_once __DIR__ . '/koneksi.php';

applyCommonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJson([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan GET.',
    ], 405);
}

$videoId = $_GET['video_id'] ?? '';

if (trim($videoId) === '') {
    sendJson([
        'success' => false,
        'message' => 'Parameter video_id wajib diisi. Contoh: get_video.php?video_id=abc123',
    ], 400);
}

try {
    $pdo = getDbConnection();

    $statement = $pdo->prepare("
        SELECT
            video_id, title, description, channel_title, published_at,
            thumbnail, view_count, like_count, duration, raw_json,
            created_at, updated_at
        FROM videos
        WHERE video_id = :video_id
        LIMIT 1
    ");
    $statement->execute([':video_id' => $videoId]);

    $row = $statement->fetch();

    if (!$row) {
        sendJson([
            'success' => false,
            'message' => 'Video tidak ditemukan.',
        ], 404);
    }

    $row['raw_json'] = !empty($row['raw_json'])
        ? (json_decode($row['raw_json'], true) ?? [])
        : [];

    sendJson([
        'success' => true,
        'data' => $row,
    ]);
} catch (Throwable $e) {
    sendJson([
        'success' => false,
        'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
    ], 500);
}
