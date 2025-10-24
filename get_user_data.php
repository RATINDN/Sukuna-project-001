<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error' => 'User not logged in']);
  exit;
}

try {
  $stmt = $pdo->prepare("SELECT email, phone FROM car WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if ($user) {
    echo json_encode([
      'email' => $user['email'],
      'phone' => $user['phone']
    ]);
  } else {
    echo json_encode(['error' => 'User not found']);
  }
} catch (PDOException $e) {
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>