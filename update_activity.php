<?php
/**
 * Update User Activity Endpoint
 * Updates last_activity timestamp when users are active
 */
session_start();
require_once 'db_connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Validate the request
if (!isset($_POST['activity_update'])) {
    http_response_code(400);
    exit('Invalid request');
}

$userId = $_SESSION['user_id'];

try {
    // Update last_activity timestamp for the current user
    $stmt = $pdo->prepare("UPDATE car SET last_activity = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$userId]);

    // Return success (no need for JSON in this simple case - just HTTP status)
    http_response_code(200);

} catch (PDOException $e) {
    error_log("Activity update failed for user {$userId}: " . $e->getMessage());
    http_response_code(500);
}
?>
