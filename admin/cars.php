<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $car_id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        header("Location: cars.php?success=car_deleted");
        exit();
    } catch(PDOException $e) {
        header("Location: cars.php?error=delete_failed");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Car Rental System</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Manage Cars</h2>
                <a href="add_car.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New Car
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['success']) {
                            case 'car_deleted':
                                echo 'Car successfully deleted';
                                break;
                            case 'car_added':
                                echo 'Car successfully added';
                                break;
                            case 'car_updated':
                                echo 'Car successfully updated';
                                break;
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['error']) {
                            case 'delete_failed':
                                echo 'Failed to delete car';
                                break;
                        }
                    ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b" style="text-align: center">ID</th>
                                <th class="py-2 px-4 border-b">Make</th>
                                <th class="py-2 px-4 border-b">Model</th>
                                <th class="py-2 px-4 border-b">Year</th>
                                <th class="py-2 px-4 border-b">Color</th>
                                <th class="py-2 px-4 border-b">Price/Day</th>
                                <th class="py-2 px-4 border-b">Status</th>
                                <th class="py-2 px-4 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("SELECT * FROM cars ORDER BY id DESC");
                            while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$car['id']}</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$car['make']}</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$car['model']}</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$car['year']}</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$car['color']}</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>\${$car['price_per_day']}</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>" . ($car['available'] ? 'Available' : 'Rented') . "</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>
                                        <a href='edit_car.php?id={$car['id']}' class='text-blue-500 hover:text-blue-700 mr-2'>
                                            <i class='fas fa-edit'></i>
                                        </a>
                                        <a href='cars.php?delete={$car['id']}' class='text-red-500 hover:text-red-700' onclick=\"return confirm('Are you sure you want to delete this car?');\">
                                            <i class='fas fa-trash'></i>
                                        </a>
                                    </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
