<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    http_response_code(405);
    exit();
}

$new_username = trim($_POST['new_username']);
$user_id = $_SESSION['user_id'];

if (empty($new_username)) {
    echo json_encode(['success' => false, 'error' => 'Username cannot be empty']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE car SET user_name = ? WHERE id = ?");
    $stmt->execute([$new_username, $user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['user_name'] = $new_username;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update username or username is the same']);
    }
} catch (PDOException $e) {
    error_log("Error updating username: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    http_response_code(500);
}
?>