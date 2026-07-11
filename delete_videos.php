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

$videoId = is_array($payload) ? ($payload['video_id'] ?? '') : '';

if (trim($videoId) === '') {
    sendJson([
        'success' => false,
        'message' => 'Field video_id wajib diisi pada body request.',
    ], 400);
}

try {
    $pdo = getDbConnection();

    $statement = $pdo->prepare("DELETE FROM videos WHERE video_id = :video_id");
    $statement->execute([':video_id' => $videoId]);

    if ($statement->rowCount() === 0) {
        sendJson([
            'success' => false,
            'message' => 'Video tidak ditemukan atau sudah terhapus sebelumnya.',
        ], 404);
    }

    sendJson([
        'success' => true,
        'message' => 'Video berhasil dihapus.',
    ]);
} catch (Throwable $e) {
    sendJson([
        'success' => false,
        'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
    ], 500);
}
