<?php
/**
 * Get Online Statuses for Admin
 * Returns user statuses with online/offline indicators
 */
session_start();
require_once 'db_connect.php';

// Check admin access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get all users with their status info
    $stmt = $pdo->prepare("SELECT id, user_name, email, phone, role, last_activity FROM car ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process each user to determine online status
    $processedUsers = [];
    $currentTime = time(); // UNIX timestamp

    foreach ($users as $user) {
        $isOnline = false;

        // Check if user has activity in last 3 minutes
        if ($user['last_activity']) {
            $lastActivityTime = strtotime($user['last_activity']);
            $timeDiff = $currentTime - $lastActivityTime;

            // Consider online if last activity < 3 minutes (180 seconds)
            $isOnline = ($timeDiff < 180);
        }

        $processedUsers[] = [
            'id' => $user['id'],
            'user_name' => htmlspecialchars($user['user_name']),
            'email' => htmlspecialchars($user['email']),
            'phone' => htmlspecialchars($user['phone']),
            'role' => (int)$user['role'],
            'last_activity' => $user['last_activity'],
            'is_online' => $isOnline,
            'status_display' => $isOnline ? '🟢 آنلاین' : '🔴 آفلاین'
        ];
    }

    // Return as JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'users' => $processedUsers]);

} catch (PDOException $e) {
    error_log("Failed to fetch online statuses: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
