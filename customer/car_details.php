<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

// Check if car ID is provided
if (!isset($_GET['id'])) {
    header("Location: available_cars.php");
    exit();
}

$car_id = $_GET['id'];

// Get car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id");
$stmt->bindParam(':id', $car_id);
$stmt->execute();
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: available_cars.php?error=car_not_found");
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
    <title><?php echo $car['make'] . ' ' . $car['model']; ?> - Car Rental System</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Car Details</h2>
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full">
                        <i class="fas fa-wallet mr-1"></i>
                        $<?php echo number_format($balance, 2); ?>
                    </div>
                    <a href="available_cars.php" class="text-blue-500 hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Available Cars
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Car Image -->
                    <div class="bg-gray-200 h-96 flex items-center justify-center rounded-lg">
                        <?php if ($car['image_path']): ?>
                            <img src="../<?php echo $car['image_path']; ?>" alt="<?php echo $car['make'] . ' ' . $car['model']; ?>" class="h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-car text-6xl text-gray-400"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Car Details -->
                    <div>
                        <h3 class="text-2xl font-bold mb-2"><?php echo $car['make'] . ' ' . $car['model']; ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo $car['year']; ?></p>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-gray-500 text-sm">Color</p>
                                <p class="font-medium"><?php echo $car['color']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Seats</p>
                                <p class="font-medium"><?php echo $car['seats'] ?? '4'; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Transmission</p>
                                <p class="font-medium"><?php echo $car['transmission'] ?? 'Automatic'; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Fuel Type</p>
                                <p class="font-medium"><?php echo $car['fuel_type'] ?? 'Petrol'; ?></p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-lg font-bold mb-2">Description</h4>
                            <p class="text-gray-700"><?php echo $car['description'] ?? 'No description available.'; ?></p>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-lg font-bold mb-2">Features</h4>
                            <ul class="grid grid-cols-2 gap-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span>Air Conditioning</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span>Bluetooth</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span>Navigation</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span>USB Ports</span>
                                </li>
                            </ul>
                        </div>

                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Price per day</p>
                                <p class="text-2xl font-bold">$<?php echo number_format($car['price_per_day'], 2); ?></p>
                            </div>
                            <div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $car['available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $car['available'] ? 'Available' : 'Not Available'; ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($car['available']): ?>
                            <div class="mt-6">
                                <a href="rent_car.php?id=<?php echo $car['id']; ?>" 
                                   class="w-full bg-blue-500 text-white py-3 px-6 rounded-lg hover:bg-blue-600 transition duration-200 text-center block">
                                    Rent Now
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
