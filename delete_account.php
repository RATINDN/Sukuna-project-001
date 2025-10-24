<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    http_response_code(405);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();
    
    // Here you could add logic to handle related data, 
    // e.g., reassigning posts, deleting comments, etc.

    $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
    $stmt->execute([$userId]);

    $pdo->commit();

    // Destroy the session after successful deletion
    session_unset();
    session_destroy();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    http_response_code(500);
}
?>