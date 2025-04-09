<?php
require_once '../includes/session_manager.php';
startSecureSession();
require_once '../includes/db_connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if car ID is provided
if (!isset($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$car_id = $_GET['id'];

// Get car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id");
$stmt->bindParam(':id', $car_id);
$stmt->execute();
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: cars.php?error=car_not_found");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and update car details
    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $year = (int)$_POST['year'];
    $color = trim($_POST['color']);
    $price_per_day = (float)$_POST['price_per_day'];
    $available = isset($_POST['available']) ? 1 : 0;

    $update_stmt = $conn->prepare("
        UPDATE cars SET 
        make = :make,
        model = :model,
        year = :year,
        color = :color,
        price_per_day = :price_per_day,
        available = :available
        WHERE id = :id
    ");
    $update_stmt->execute([
        ':make' => $make,
        ':model' => $model,
        ':year' => $year,
        ':color' => $color,
        ':price_per_day' => $price_per_day,
        ':available' => $available,
        ':id' => $car_id
    ]);

    header("Location: cars.php?success=car_updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'dashboard.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Edit Car</h2>
                <a href="cars.php" class="text-blue-500 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Cars
                </a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2">Make</label>
                            <input type="text" name="make" value="<?= htmlspecialchars($car['make']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Model</label>
                            <input type="text" name="model" value="<?= htmlspecialchars($car['model']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Year</label>
                            <input type="number" name="year" min="1900" max="<?= date('Y')+1 ?>" 
                                   value="<?= $car['year'] ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Color</label>
                            <input type="text" name="color" value="<?= htmlspecialchars($car['color']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Price Per Day ($)</label>
                            <input type="number" name="price_per_day" min="0" step="0.01" 
                                   value="<?= $car['price_per_day'] ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="available" id="available" 
                                   <?= $car['available'] ? 'checked' : '' ?> class="mr-2">
                            <label for="available" class="text-gray-700">Available for Rent</label>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
