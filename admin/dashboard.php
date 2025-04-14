<?php
require_once '../includes/session_manager.php';
startSecureSession();
require_once '../includes/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 p-4">
            <div class="flex items-center space-x-2 mb-8">
                <i class="fas fa-car text-2xl"></i>
                <h1 class="text-xl font-bold">Car Rental Admin</h1>
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
                        <a href="cars.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-car"></i>
                            <span>Manage Cars</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="rentals.php" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-receipt"></i>
                            <span>Rental History</span>
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
                <h2 class="text-2xl font-bold text-gray-800">Admin Dashboard</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500">Total Cars</p>
                            <h3 class="text-2xl font-bold">
                                <?php 
                                    $stmt = $conn->query("SELECT COUNT(*) FROM cars");
                                    echo $stmt->fetchColumn();
                                ?>
                            </h3>
                        </div>
                        <i class="fas fa-car text-blue-500 text-3xl"></i>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500">Total Users</p>
                            <h3 class="text-2xl font-bold">
                                <?php 
                                    $stmt = $conn->query("SELECT COUNT(*) FROM users");
                                    echo $stmt->fetchColumn();
                                ?>
                            </h3>
                        </div>
                        <i class="fas fa-users text-green-500 text-3xl"></i>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500">Active Rentals</p>
                            <h3 class="text-2xl font-bold">
                                <?php 
                                    $stmt = $conn->query("SELECT COUNT(*) FROM rentals WHERE status = 'active'");
                                    echo $stmt->fetchColumn();
                                ?>
                            </h3>
                        </div>
                        <i class="fas fa-receipt text-yellow-500 text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Rentals -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold mb-4">Recent Rentals</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b">ID</th>
                                <th class="py-2 px-4 border-b">User</th>
                                <th class="py-2 px-4 border-b">Car</th>
                                <th class="py-2 px-4 border-b">Dates</th>
                                <th class="py-2 px-4 border-b">Price</th>
                                <th class="py-2 px-4 border-b">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("
                                SELECT r.id, u.username, CONCAT(c.make, ' ', c.model) AS car, 
                                       r.start_date, r.end_date, r.total_price, r.status
                                FROM rentals r
                                JOIN users u ON r.user_id = u.id
                                JOIN cars c ON r.car_id = c.id
                                ORDER BY r.created_at DESC
                                LIMIT 5
                            ");
                            while ($rental = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td class='py-2 px-4 border-b'>{$rental['id']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$rental['username']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$rental['car']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$rental['start_date']} to {$rental['end_date']}</td>";
                                echo "<td class='py-2 px-4 border-b'>\${$rental['total_price']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$rental['status']}</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
