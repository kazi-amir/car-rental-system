<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

// Check if rental ID is provided
if (!isset($_GET['id'])) {
    header("Location: my_rentals.php");
    exit();
}

$rental_id = $_GET['id'];

// Get rental details
$stmt = $conn->prepare("
    SELECT r.*, c.make, c.model, c.price_per_day
    FROM rentals r
    JOIN cars c ON r.car_id = c.id
    WHERE r.id = :id AND r.user_id = :user_id AND r.status = 'active'
");
$stmt->bindParam(':id', $rental_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$rental = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rental) {
    header("Location: my_rentals.php?error=rental_not_found");
    exit();
}

// Calculate refund amount (50% of remaining days)
$start = new DateTime($rental['start_date']);
$end = new DateTime($rental['end_date']);
$today = new DateTime();
$remaining_days = $today->diff($end)->days;
$refund_amount = $remaining_days * $rental['price_per_day'] * 0.5;

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Update rental status
        $stmt = $conn->prepare("UPDATE rentals SET status = 'cancelled' WHERE id = :id");
        $stmt->bindParam(':id', $rental_id);
        $stmt->execute();
        
        // Make car available again
        $stmt = $conn->prepare("UPDATE cars SET available = TRUE WHERE id = :car_id");
        $stmt->bindParam(':car_id', $rental['car_id']);
        $stmt->execute();
        
        // Refund to user balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance + :amount WHERE id = :user_id");
        $stmt->bindParam(':amount', $refund_amount);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        // Record transaction
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) 
                               VALUES (:user_id, :amount, 'credit', 'Rental cancellation refund')");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':amount', $refund_amount);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        header("Location: my_rentals.php?success=rental_cancelled");
        exit();
    } catch(PDOException $e) {
        $conn->rollBack();
        header("Location: my_rentals.php?error=cancel_failed");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Rental - Car Rental System</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Cancel Rental</h2>
                <a href="my_rentals.php" class="text-blue-500 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to My Rentals
                </a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow max-w-2xl mx-auto">
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-2">Rental Details</h3>
                    <div class="border rounded-lg p-4">
                        <h4 class="font-bold"><?php echo $rental['make'] . ' ' . $rental['model']; ?></h4>
                        <p class="text-gray-600 text-sm">Rental ID: #<?php echo $rental['id']; ?></p>
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <p class="text-gray-500 text-sm">Start Date</p>
                                <p><?php echo date('F j, Y', strtotime($rental['start_date'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">End Date</p>
                                <p><?php echo date('F j, Y', strtotime($rental['end_date'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Original Price</p>
                                <p>$<?php echo number_format($rental['total_price'], 2); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Refund Amount</p>
                                <p class="font-bold text-green-600">$<?php echo number_format($refund_amount, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Note:</strong> Cancelling this rental will refund 50% of the remaining days' value to your account balance.
                                The refund amount is calculated based on the time remaining until the end date.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" class="form-checkbox" required>
                            <span class="ml-2">I understand that cancelling this rental will refund 50% of the remaining days' value</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <a href="my_rentals.php" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
