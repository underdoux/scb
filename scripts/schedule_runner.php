<?php
require_once __DIR__ . '/../db/dbConnection.php';
require_once __DIR__ . '/../src/OAuthService.php';

function sendPostToPlatform($userId, $platform, $content, $oauthService, $pdo, $scheduleId) {
    try {
        $token = $oauthService->getToken($userId, $platform);
        if (!$token) {
            throw new Exception("No valid token for platform $platform");
        }

        $accessToken = $token['access_token'];
        $response = null;

        switch ($platform) {
            case 'facebook':
                $response = sendFacebookPost($accessToken, $content);
                break;
            case 'instagram':
                $response = sendInstagramPost($accessToken, $content);
                break;
            case 'twitter':
                $response = sendTwitterPost($accessToken, $content);
                break;
            case 'linkedin':
                $response = sendLinkedInPost($accessToken, $content);
                break;
            default:
                throw new Exception("Unsupported platform: $platform");
        }

        if ($response['success']) {
            $stmt = $pdo->prepare("UPDATE schedules SET status = 'sent' WHERE id = :id");
            $stmt->execute([':id' => $scheduleId]);
            logNotification($pdo, $userId, "Post sent to $platform successfully.", 'success');
            sendEmailNotification($userId, "Post sent to $platform successfully.");
            echo "Post sent to $platform successfully.\n";
        } else {
            throw new Exception($response['message']);
        }
    } catch (Exception $e) {
        // Handle 401 Unauthorized by refreshing token and retrying once
        if (strpos($e->getMessage(), '401') !== false) {
            logNotification($pdo, $userId, "Token expired for $platform. Attempting refresh.", 'token_issue');
            sendEmailNotification($userId, "Token expired for $platform. Attempting refresh.");
            $newToken = $oauthService->refreshToken($userId, $platform);
            if ($newToken) {
                $accessToken = $newToken['access_token'];
                // Retry sending post
                $retryResponse = null;
                switch ($platform) {
                    case 'facebook':
                        $retryResponse = sendFacebookPost($accessToken, $content);
                        break;
                    case 'instagram':
                        $retryResponse = sendInstagramPost($accessToken, $content);
                        break;
                    case 'twitter':
                        $retryResponse = sendTwitterPost($accessToken, $content);
                        break;
                    case 'linkedin':
                        $retryResponse = sendLinkedInPost($accessToken, $content);
                        break;
                }
                if ($retryResponse && $retryResponse['success']) {
                    $stmt = $pdo->prepare("UPDATE schedules SET status = 'sent' WHERE id = :id");
                    $stmt->execute([':id' => $scheduleId]);
                    logNotification($pdo, $userId, "Post sent to $platform successfully after token refresh.", 'success');
                    sendEmailNotification($userId, "Post sent to $platform successfully after token refresh.");
                    echo "Post sent to $platform successfully after token refresh.\n";
                    return;
                }
            }
        }
        $stmt = $pdo->prepare("UPDATE schedules SET status = 'failed' WHERE id = :id");
        $stmt->execute([':id' => $scheduleId]);
        logNotification($pdo, $userId, "Failed to send post to $platform: " . $e->getMessage(), 'failure');
        sendEmailNotification($userId, "Failed to send post to $platform: " . $e->getMessage());
        echo "Failed to send post to $platform: " . $e->getMessage() . "\n";
    }
}

function logNotification($pdo, $userId, $message, $type) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (:user_id, :message, :type)");
    $stmt->execute([':user_id' => $userId, ':message' => $message, ':type' => $type]);
}

function sendEmailNotification($userId, $message) {
    // For simplicity, get user email from database
    global $pdo;
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || empty($user['email'])) {
        return;
    }
    $to = $user['email'];
    $subject = "Social Content Generator Notification";
    $headers = "From: no-reply@myphpapp.com\r\n";
    $body = $message;

    mail($to, $subject, $body, $headers);
}

function sendFacebookPost($accessToken, $content) {
    // Facebook Graph API to post on page feed
    $url = "https://graph.facebook.com/me/feed";
    $data = [
        'message' => $content,
        'access_token' => $accessToken
    ];
    return sendApiRequest($url, $data);
}

function sendInstagramPost($accessToken, $content) {
    // Instagram Graph API requires container creation and publishing
    // For this example, we assume a fixed image URL is used
    $imageUrl = 'https://example.com/path/to/image.jpg'; // Replace with actual image URL or logic

    // Step 1: Create media container
    $createContainerUrl = "https://graph.facebook.com/v15.0/me/media";
    $containerData = [
        'image_url' => $imageUrl,
        'caption' => $content,
        'access_token' => $accessToken
    ];
    $containerResponse = sendApiRequestWithResponse($createContainerUrl, $containerData);
    if (!$containerResponse || !isset($containerResponse['id'])) {
        return ['success' => false, 'message' => 'Failed to create Instagram media container'];
    }

    $containerId = $containerResponse['id'];

    // Step 2: Publish media container
    $publishUrl = "https://graph.facebook.com/v15.0/me/media_publish";
    $publishData = [
        'creation_id' => $containerId,
        'access_token' => $accessToken
    ];
    $publishResponse = sendApiRequestWithResponse($publishUrl, $publishData);
    if (!$publishResponse || !isset($publishResponse['id'])) {
        return ['success' => false, 'message' => 'Failed to publish Instagram media'];
    }

    return ['success' => true, 'message' => 'Instagram post published'];
}

function sendTwitterPost($accessToken, $content) {
    // Twitter API v2 - Post a tweet using Bearer token
    $url = "https://api.twitter.com/2/tweets";
    $data = json_encode(['text' => $content]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'message' => 'Tweet posted successfully'];
    } else {
        return ['success' => false, 'message' => "HTTP $httpCode: $response"];
    }
}

function sendLinkedInPost($accessToken, $content) {
    // LinkedIn API to create a share post
    // Get user urn
    $profileUrl = "https://api.linkedin.com/v2/me";
    $profileResponse = sendLinkedInApiRequest($profileUrl, $accessToken);
    if (!$profileResponse || !isset($profileResponse['id'])) {
        return ['success' => false, 'message' => 'Failed to get LinkedIn profile'];
    }
    $userUrn = "urn:li:person:" . $profileResponse['id'];

    // Create share post
    $shareUrl = "https://api.linkedin.com/v2/ugcPosts";
    $postData = json_encode([
        "author" => $userUrn,
        "lifecycleState" => "PUBLISHED",
        "specificContent" => [
            "com.linkedin.ugc.ShareContent" => [
                "shareCommentary" => [
                    "text" => $content
                ],
                "shareMediaCategory" => "NONE"
            ]
        ],
        "visibility" => [
            "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
        ]
    ]);

    $ch = curl_init($shareUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json",
        "X-Restli-Protocol-Version: 2.0.0"
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'message' => 'LinkedIn post published'];
    } else {
        return ['success' => false, 'message' => "HTTP $httpCode: $response"];
    }
}

function sendApiRequestWithResponse($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        return null;
    }
}

function sendLinkedInApiRequest($url, $accessToken) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken"
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        return null;
    }
}

function sendApiRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'message' => 'Posted successfully'];
    } else {
        return ['success' => false, 'message' => "HTTP $httpCode: $response"];
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
