<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("SELECT balance FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$balance = $user['balance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    
    if (!is_numeric($amount) || $amount <= 0) {
        header("Location: recharge.php?error=invalid_amount");
        exit();
    }
    
    try {
        
        $stmt = $conn->prepare("UPDATE users SET balance = balance + :amount WHERE id = :id");
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        header("Location: recharge.php?success=recharge_completed");
        exit();
        
    } catch(PDOException $e) {
        header("Location: recharge.php?error=recharge_failed");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge Account - Car Rental System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'dashboard.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Recharge Account</h2>
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full">
                        <i class="fas fa-wallet mr-1"></i>
                        $<?php echo number_format($balance, 2); ?>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    Account successfully recharged!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['error']) {
                            case 'invalid_amount':
                                echo 'Please enter a valid amount greater than 0';
                                break;
                            case 'recharge_failed':
                                echo 'Failed to process recharge. Please try again.';
                                break;
                        }
                    ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow max-w-md mx-auto">
                <form method="POST">
                    <div class="mb-6">
                        <label for="amount" class="block text-gray-700 mb-2">Amount to Recharge ($)</label>
                        <input type="number" id="amount" name="amount" min="1" step="0.01"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-lg font-bold mb-2">Payment Method</h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" class="mr-2" checked>
                                <label for="credit_card">Credit/Debit Card</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="paypal" name="payment_method" value="paypal" class="mr-2">
                                <label for="paypal">PayPal</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" class="mr-2">
                                <label for="bank_transfer">Bank Transfer</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" 
                            class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">
                        Recharge Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
