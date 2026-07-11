<?php
require_once __DIR__ . '/koneksi.php';

applyCommonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan POST.',
    ], 405);
}

$rawBody = file_get_contents('php://input');
$video = json_decode($rawBody, true);

if (!is_array($video) || empty($video['video_id'])) {
    sendJson([
        'success' => false,
        'message' => 'Field video_id wajib diisi.',
    ], 400);
}

try {
    $pdo = getDbConnection();
    $checkStatement = $pdo->prepare("SELECT id FROM videos WHERE video_id = :video_id LIMIT 1");
    $checkStatement->execute([':video_id' => $video['video_id']]);

    if ($checkStatement->fetch()) {
        sendJson([
            'success' => false,
            'message' => 'Video dengan video_id ini sudah ada. Gunakan update_video.php untuk mengubahnya.',
        ], 409);
    }

    $insertStatement = $pdo->prepare("
        INSERT INTO videos
            (video_id, title, description, channel_title, published_at,
             thumbnail, view_count, like_count, duration, raw_json)
        VALUES
            (:video_id, :title, :description, :channel_title, :published_at,
             :thumbnail, :view_count, :like_count, :duration, :raw_json)
    ");

    $insertStatement->execute([
        ':video_id'      => $video['video_id'],
        ':title'         => $video['title'] ?? '',
        ':description'   => $video['description'] ?? null,
        ':channel_title' => $video['channel_title'] ?? null,
        ':published_at'  => $video['published_at'] ?? null,
        ':thumbnail'     => $video['thumbnail'] ?? null,
        ':view_count'    => $video['view_count'] ?? null,
        ':like_count'    => $video['like_count'] ?? null,
        ':duration'      => $video['duration'] ?? null,
        ':raw_json'      => isset($video['raw_json'])
            ? json_encode($video['raw_json'], JSON_UNESCAPED_UNICODE)
            : null,
    ]);

    sendJson([
        'success' => true,
        'message' => 'Video berhasil ditambahkan.',
    ]);
} catch (Throwable $e) {
    sendJson([
        'success' => false,
        'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
    ], 500);
}
