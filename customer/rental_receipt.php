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
    SELECT r.*, c.make, c.model, c.year, c.color, c.price_per_day, 
           u.username, u.email, u.phone
    FROM rentals r
    JOIN cars c ON r.car_id = c.id
    JOIN users u ON r.user_id = u.id
    WHERE r.id = :id AND r.user_id = :user_id
");
$stmt->bindParam(':id', $rental_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$rental = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rental) {
    header("Location: my_rentals.php?error=rental_not_found");
    exit();
}

// Calculate days
$start = new DateTime($rental['start_date']);
$end = new DateTime($rental['end_date']);
$days = $start->diff($end)->days;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Receipt - Car Rental System</title>
    <link rel="stylesheet" href="/car-rental-system/assets/css/tailwind.css">
    <link rel="stylesheet" href="/car-rental-system/assets/css/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/car-rental-system/assets/css/fonts/inter.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'dashboard.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Rental Receipt</h2>
                <div class="flex items-center space-x-4">
                    <a href="my_rentals.php" class="text-blue-500 hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to My Rentals
                    </a>
                    <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mr-2">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <?php if ($rental['status'] == 'active'): ?>
                    <a href="cancel_rental.php?id=<?= $rental['id'] ?>" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        <i class="fas fa-times mr-1"></i> Cancel Rental
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow max-w-4xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-8 pb-4 border-b">
                    <div>
                        <h1 class="text-2xl font-bold">Car Rental System</h1>
                        <p class="text-gray-600">123 Rental Street, City</p>
                        <p class="text-gray-600">Phone: (123) 456-7890</p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-xl font-bold">Rental Receipt</h2>
                        <p class="text-gray-600">#<?php echo $rental['id']; ?></p>
                        <p class="text-gray-600"><?php echo date('F j, Y', strtotime($rental['created_at'])); ?></p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-bold mb-2">Customer Information</h3>
                        <p class="font-medium"><?php echo $rental['username']; ?></p>
                        <p class="text-gray-600"><?php echo $rental['email']; ?></p>
                        <p class="text-gray-600"><?php echo $rental['phone'] ?? 'N/A'; ?></p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2">Rental Information</h3>
                        <p class="text-gray-600">Status: 
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                switch($rental['status']) {
                                    case 'active': echo 'bg-green-100 text-green-800'; break;
                                    case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                }
                            ?>">
                                <?php echo $rental['status']; ?>
                            </span>
                        </p>
                        <p class="text-gray-600">Pickup: <?php echo date('F j, Y', strtotime($rental['start_date'])); ?></p>
                        <p class="text-gray-600">Return: <?php echo date('F j, Y', strtotime($rental['end_date'])); ?></p>
                    </div>
                </div>

                <!-- Car Info -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold mb-4">Car Information</h3>
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="bg-gray-200 h-48 w-64 flex items-center justify-center rounded-lg">
                            <?php if ($rental['image_path']): ?>
                                <img src="../<?php echo $rental['image_path']; ?>" alt="<?php echo $rental['make'] . ' ' . $rental['model']; ?>" class="h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-car text-6xl text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold"><?php echo $rental['make'] . ' ' . $rental['model']; ?></h4>
                            <p class="text-gray-600"><?php echo $rental['year'] . ' • ' . $rental['color']; ?></p>
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-500 text-sm">Rental Days</p>
                                    <p class="font-medium"><?php echo $days; ?> days</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Daily Rate</p>
                                    <p class="font-medium">$<?php echo number_format($rental['price_per_day'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-bold mb-4">Payment Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <div class="flex justify-between py-2 border-b">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($rental['total_price'], 2); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span>Tax (10%):</span>
                                <span>$<?php echo number_format($rental['total_price'] * 0.1, 2); ?></span>
                            </div>
                            <div class="flex justify-between py-2 font-bold text-lg">
                                <span>Total:</span>
                                <span>$<?php echo number_format($rental['total_price'] * 1.1, 2); ?></span>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold mb-2">Payment Method</h4>
                            <p class="text-gray-600">Account Balance</p>
                            <p class="text-gray-600">Paid on <?php echo date('F j, Y', strtotime($rental['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 pt-4 border-t text-center text-gray-500 text-sm">
                    <p>Thank you for choosing Car Rental System!</p>
                    <p>For any questions, please contact support@carrentalsystem.com</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
