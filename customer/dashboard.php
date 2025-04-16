<?php
require_once '../includes/session_manager.php';
startSecureSession();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Car Rental System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 p-4">
            <div class="flex items-center space-x-2 mb-8">
                <i class="fas fa-car text-2xl"></i>
                <h1 class="text-xl font-bold">Car Rental</h1>
            </div>
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="available_cars.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-car"></i>
                            <span>Available Cars</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_rentals.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-receipt"></i>
                            <span>My Rentals</span>
                        </a>
                    </li>
                    <li>
                        <a href="recharge.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-wallet"></i>
                            <span>Recharge Account</span>
                        </a>
                    </li>
                    <li>
                        <a href="../includes/logout.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <?php if (basename($_SERVER['SCRIPT_NAME']) === 'dashboard.php'): ?>
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Customer Dashboard</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full">
                        <i class="fas fa-wallet mr-1"></i>
                        $<?php echo number_format($balance, 2); ?>
                    </div>
                </div>
            </div>

            <!-- Active Rentals -->
            <div class="bg-white p-6 rounded-lg shadow mb-8">
                <h3 class="text-xl font-bold mb-4">Active Rentals</h3>
                <?php
                $stmt = $conn->prepare("
                    SELECT r.id, c.make, c.model, r.start_date, r.end_date, r.total_price
                    FROM rentals r
                    JOIN cars c ON r.car_id = c.id
                    WHERE r.user_id = :user_id AND r.status = 'active'
                    ORDER BY r.start_date DESC
                ");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
                    while ($rental = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="border rounded-lg p-4">';
                        echo '<h4 class="font-bold">' . $rental['make'] . ' ' . $rental['model'] . '</h4>';
                        echo '<p class="text-gray-600 text-sm">' . $rental['start_date'] . ' to ' . $rental['end_date'] . '</p>';
                        echo '<p class="mt-2">Total: <span class="font-bold">$' . number_format($rental['total_price'], 2) . '</span></p>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-600">You have no active rentals.</p>';
                }
                ?>
            </div>

            <!-- Available Cars -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Available Cars</h3>
                    <a href="available_cars.php" class="text-blue-500 hover:underline">View All</a>
                </div>
                <?php
                $stmt = $conn->query("
                    SELECT * FROM cars 
                    WHERE available = TRUE 
                    ORDER BY id DESC
                ");

                if ($stmt->rowCount() > 0) {
                    echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
                    while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="border rounded-lg p-4">';
                        echo '<h4 class="font-bold">' . $car['make'] . ' ' . $car['model'] . '</h4>';
                        echo '<p class="text-gray-600 text-sm">' . $car['year'] . ' • ' . $car['color'] . '</p>';
                        echo '<p class="mt-2">Price: <span class="font-bold">$' . number_format($car['price_per_day'], 2) . '/day</span></p>';
                        echo '<a href="rent_car.php?id=' . $car['id'] . '" class="inline-block mt-2 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Rent Now</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-600">No cars available at the moment.</p>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
