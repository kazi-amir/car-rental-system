<?php
require_once '../includes/session_manager.php';
startSecureSession();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    file_put_contents('debug.log', "No rental ID provided\n", FILE_APPEND);
    header("Location: my_rentals.php?error=no_rental_id");
    exit();
}

$rental_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT r.id, r.user_id, r.car_id, r.start_date, r.end_date, r.total_price, r.status,
        c.make, c.model, c.price_per_day
    FROM rentals r
    INNER JOIN cars c ON r.car_id=c.id
    WHERE r.id=:id AND r.user_id=:user_id AND r.status='active'
");
$stmt->bindParam(':id', $rental_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$rental = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rental) {
    header("Location: my_rentals.php?error=rental_not_found");
    exit();
}

$start = new DateTime($rental['start_date']);
$end = new DateTime($rental['end_date']);
$today = new DateTime();
if ($today > $end) {
    $remaining_days = 0;
} 
elseif ($today < $start) {
    $remaining_days = $start->diff($end)->days + 1;
} 
else {
    $remaining_days = $today->diff($end)->days + 1;
}
$refund_amount = max(0, $remaining_days) * $rental['price_per_day'] * 0.5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("UPDATE rentals SET status = 'cancelled' WHERE id = :id AND status = 'active'");
        $stmt->bindParam(':id', $rental_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            
            throw new Exception("Rental is not active or already cancelled.");
        }
        
        $stmt = $conn->prepare("UPDATE cars SET available = TRUE WHERE id = :car_id");
        $stmt->bindParam(':car_id', $rental['car_id']);
        $stmt->execute();
        
        if ($refund_amount > 0) {
            $stmt = $conn->prepare("UPDATE users SET balance = balance + :amount WHERE id = :user_id");
            $stmt->bindParam(':amount', $refund_amount);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();

        }
        
        $conn->commit();
        
        header("Location: my_rentals.php?success=rental_cancelled&refunded");
        exit();

    } catch(Exception $e) {
        $conn->rollBack();
        file_put_contents('debug.log', "Cancellation failed: " . $e->getMessage() . "\n", FILE_APPEND);
        header("Location: my_rentals.php?error=cancel_failed&details=" . urlencode($e->getMessage()));
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
                    <button type="submit" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            onclick="return confirm('Are you sure you want to cancel this rental? You will receive a 50% refund for remaining days.')">
                        Confirm Cancellation
                    </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
