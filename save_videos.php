<?php
require_once __DIR__ . '/config.php';

applyCommonHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan POST.',
    ], 405);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload) || !isset($payload['videos']) || !is_array($payload['videos'])) {
    sendJson([
        'success' => false,
        'message' => 'Body request tidak valid. Wajib ada field "videos" berupa array.',
    ], 400);
}

$videos = $payload['videos'];

if (count($videos) === 0) {
    sendJson([
        'success' => false,
        'message' => 'Tidak ada video yang dikirim.',
    ], 400);
}

try {
    $pdo = getDbConnection();

    $sql = "
        INSERT INTO videos
            (video_id, title, description, channel_title, published_at,
             thumbnail, view_count, like_count, duration, raw_json)
        VALUES
            (:video_id, :title, :description, :channel_title, :published_at,
             :thumbnail, :view_count, :like_count, :duration, :raw_json)
        ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            description = VALUES(description),
            channel_title = VALUES(channel_title),
            published_at = VALUES(published_at),
            thumbnail = VALUES(thumbnail),
            view_count = VALUES(view_count),
            like_count = VALUES(like_count),
            duration = VALUES(duration),
            raw_json = VALUES(raw_json)
    ";

    $statement = $pdo->prepare($sql);

    $savedCount = 0;

    foreach ($videos as $video) {
        if (!is_array($video) || empty($video['video_id'])) {
            continue; 
        }

        $statement->execute([
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

        $savedCount++;
    }

    sendJson([
        'success' => true,
        'message' => 'Berhasil menyimpan video.',
        'data' => [
            'saved_count' => $savedCount,
        ],
    ]);
} catch (Throwable $e) {
    sendJson([
        'success' => false,
        'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
    ], 500);
}
