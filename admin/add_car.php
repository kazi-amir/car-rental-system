<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $color = $_POST['color'];
    $price_per_day = $_POST['price_per_day'];
    $available = isset($_POST['available']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("INSERT INTO cars (make, model, year, color, price_per_day, available) 
                               VALUES (:make, :model, :year, :color, :price_per_day, :available)");
        $stmt->bindParam(':make', $make);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':price_per_day', $price_per_day);
        $stmt->bindParam(':available', $available);
        $stmt->execute();

        header("Location: cars.php?success=car_added");
        exit();
    } catch(PDOException $e) {
        header("Location: add_car.php?error=database_error");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car - Car Rental System</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Add New Car</h2>
                <a href="cars.php" class="text-blue-500 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Cars
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    Error: Failed to add car
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow">
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="make" class="block text-gray-700 mb-2">Make</label>
                            <input type="text" id="make" name="make" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                        <div>
                            <label for="model" class="block text-gray-700 mb-2">Model</label>
                            <input type="text" id="model" name="model" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                        <div>
                            <label for="year" class="block text-gray-700 mb-2">Year</label>
                            <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                        <div>
                            <label for="color" class="block text-gray-700 mb-2">Color</label>
                            <input type="text" id="color" name="color" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                        <div>
                            <label for="price_per_day" class="block text-gray-700 mb-2">Price Per Day ($)</label>
                            <input type="number" id="price_per_day" name="price_per_day" min="0" step="0.01"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="available" name="available" class="mr-2" checked>
                            <label for="available" class="text-gray-700">Available for Rent</label>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                            Add Car
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
