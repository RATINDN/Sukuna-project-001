<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $contract_id = $_POST['contract_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // آپدیت وضعیت قرارداد به "پرداخت شده"
        $stmt = $pdo->prepare("UPDATE contracts SET status = 'paid' WHERE id = ? AND user_id = ?");
        $stmt->execute([$contract_id, $user_id]);

        // شبیه‌سازی تاخیر بانک
        sleep(2);

        // هدایت به صفحه قرارداد با پیام موفقیت
        echo "<script>
            alert('پرداخت با موفقیت انجام شد! قرارداد نهایی صادر گردید.');
            window.location.href = 'print_contract.php?id=$contract_id';
        </script>";

    } catch (PDOException $e) {
        echo "خطا در پردازش پرداخت.";
    }
} else {
    header("Location: index.php");
}
?>