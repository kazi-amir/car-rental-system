<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

// Get user balance
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
    <title>Available Cars - Car Rental System</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Available Cars</h2>
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full">
                        <i class="fas fa-wallet mr-1"></i>
                        $<?php echo number_format($balance, 2); ?>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    $stmt = $conn->query("SELECT * FROM cars WHERE available = TRUE ORDER BY price_per_day ASC");
                    if ($stmt->rowCount() === 0) {
                        echo '<p class="text-gray-600">No cars available at the moment.</p>';
                    } else {
                        while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<div class="border rounded-lg overflow-hidden">';
                            echo '<div class="bg-gray-200 h-48 flex items-center justify-center">';
                            if ($car['image_path']) {
                                echo '<img src="../' . $car['image_path'] . '" alt="' . $car['make'] . ' ' . $car['model'] . '" class="h-full object-cover">';
                            } else {
                                echo '<i class="fas fa-car text-6xl text-gray-400"></i>';
                            }
                            echo '</div>';
                            echo '<div class="p-4">';
                            echo '<h3 class="font-bold text-lg">' . $car['make'] . ' ' . $car['model'] . '</h3>';
                            echo '<p class="text-gray-600 text-sm">' . $car['year'] . ' • ' . $car['color'] . '</p>';
                            echo '<p class="mt-2 text-lg font-bold">$' . number_format($car['price_per_day'], 2) . '<span class="text-sm font-normal text-gray-600"> / day</span></p>';
                            echo '<div class="mt-4 flex justify-between items-center">';
                            echo '<a href="rent_car.php?id=' . $car['id'] . '" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200">Rent Now</a>';
                            echo '<a href="car_details.php?id=' . $car['id'] . '" class="text-blue-500 hover:underline">Details</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
