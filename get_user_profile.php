<?php
session_start();
require_once 'db_connect.php';

// Set headers to prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Debug logging
error_log("get_user_profile.php accessed");
error_log("SESSION data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    error_log("Error: User not authenticated (no user_id in session)");
    echo json_encode(['error' => 'Not authenticated']);
    http_response_code(401);
    exit();
}

$userId = $_SESSION['user_id'];
$response = [];

try {
    error_log("Fetching user data for ID: $userId");
    
    // Force a database query for newly registered users
    $forceDbQuery = isset($_GET['force_refresh']) && $_GET['force_refresh'] === '1';
    
    // First check if we have the data in session to avoid database query
    if (!$forceDbQuery && isset($_SESSION['user_name']) && isset($_SESSION['avatar_color']) && isset($_SESSION['role'])) {
        error_log("Using session data for user");
        $response['success'] = true;
        $response['username'] = $_SESSION['user_name'];
        $response['avatar_color'] = $_SESSION['avatar_color'];
        $response['role'] = $_SESSION['role'];
        
        // Still need to get email and phone from database
        $stmt = $pdo->prepare("SELECT email, phone FROM car WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $response['email'] = $user['email'];
            $response['phone'] = $user['phone'];
        } else {
            // Fall back to full database query if partial data not found
            error_log("User found in session but not in database. This shouldn't happen.");
            throw new Exception("User found in session but not in database");
        }
    } else {
        // Full database query if session doesn't have all needed data
        error_log("Querying database for complete user data");
        $stmt = $pdo->prepare("SELECT user_name, email, phone, avatar_color, role FROM car WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            error_log("User found in database");
            $response['success'] = true;
            $response['username'] = $user['user_name'];
            $response['email'] = $user['email'];
            $response['phone'] = $user['phone'];
            $response['avatar_color'] = $user['avatar_color'];
            $response['role'] = $user['role'];
            
            // Update session with this data for future use
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['avatar_color'] = $user['avatar_color'];
            $_SESSION['role'] = $user['role'];
            
            // Log the updated session data
            error_log("Updated session data: " . print_r($_SESSION, true));
        } else {
            error_log("Error: User ID $userId not found in database");
            $response['error'] = 'User not found.';
            http_response_code(404);
        }
    }
} catch (Exception $e) {
    error_log("Exception in get_user_profile.php: " . $e->getMessage());
    $response['error'] = 'Error: ' . $e->getMessage();
    http_response_code(500);
}

error_log("Sending response: " . json_encode($response));
echo json_encode($response);
?>