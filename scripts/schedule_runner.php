<?php
require_once __DIR__ . '/../db/dbConnection.php';
require_once __DIR__ . '/../src/OAuthService.php';

function sendPostToPlatform($userId, $platform, $content, $oauthService, $pdo, $scheduleId) {
    try {
        $token = $oauthService->getToken($userId, $platform);
        if (!$token) {
            throw new Exception("No valid token for platform $platform");
        }

        // Example API call - replace with actual platform API integration
        // For demonstration, we simulate a successful post
        $response = [
            'success' => true,
            'message' => "Post sent to $platform"
        ];

        // Log success and update schedule status
        $stmt = $pdo->prepare("UPDATE schedules SET status = 'sent' WHERE id = :id");
        $stmt->execute([':id' => $scheduleId]);

        echo "Post sent to $platform successfully.\n";
    } catch (Exception $e) {
        // Log failure and update schedule status
        $stmt = $pdo->prepare("UPDATE schedules SET status = 'failed' WHERE id = :id");
        $stmt->execute([':id' => $scheduleId]);

        echo "Failed to send post to $platform: " . $e->getMessage() . "\n";
    }
}

try {
    $pdo = getDBConnection();
    $oauthService = new OAuthService();

    $now = (new DateTime())->format('Y-m-d H:i:s');

    // Fetch pending schedules due for posting
    $stmt = $pdo->prepare("
        SELECT s.id as schedule_id, s.platform, p.user_id, p.content
        FROM schedules s
        JOIN posts p ON s.post_id = p.id
        WHERE s.status = 'pending' AND s.scheduled_time <= :now
    ");
    $stmt->execute([':now' => $now]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($schedules as $schedule) {
        sendPostToPlatform(
            $schedule['user_id'],
            $schedule['platform'],
            $schedule['content'],
            $oauthService,
            $pdo,
            $schedule['schedule_id']
        );
    }

} catch (Exception $e) {
    echo "Error running schedule runner: " . $e->getMessage() . "\n";
}
?>
