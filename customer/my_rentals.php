<?php
session_start();
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
    <title>My Rentals - Car Rental System</title>
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
                <h2 class="text-2xl font-bold text-gray-800">My Rentals</h2>
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full">
                        <i class="fas fa-wallet mr-1"></i>
                        $<?php echo number_format($balance, 2); ?>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['success']) {
                            case 'car_rented':
                                echo 'Car successfully rented!';
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
                                <th class="py-2 px-4 border-b">ID</th>
                                <th class="py-2 px-4 border-b">Car</th>
                                <th class="py-2 px-4 border-b">Dates</th>
                                <th class="py-2 px-4 border-b">Price</th>
                                <th class="py-2 px-4 border-b">Status</th>
                                <th class="py-2 px-4 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("
                                SELECT r.id, c.make, c.model, r.start_date, r.end_date, 
                                       r.total_price, r.status, r.created_at
                                FROM rentals r
                                JOIN cars c ON r.car_id = c.id
                                WHERE r.user_id = :user_id
                                ORDER BY r.created_at DESC
                            ");
                            $stmt->bindParam(':user_id', $_SESSION['user_id']);
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                while ($rental = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$rental['id']}</td>";
                                    echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$rental['make']} {$rental['model']}</td>";
                                    echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$rental['start_date']} to {$rental['end_date']}</td>";
                                    echo "<td class='py-2 px-4 border-b' style='text-align: center'>$" . number_format($rental['total_price'], 2) . "</td>";
                                    echo "<td class='py-2 px-4 border-b' style='text-align: center'>";
                                    echo "<span class='px-2 py-1 rounded-full text-xs ";
                                    switch($rental['status']) {
                                        case 'active':
                                            echo "bg-green-100 text-green-800";
                                            break;
                                        case 'completed':
                                            echo "bg-blue-100 text-blue-800";
                                            break;
                                        case 'cancelled':
                                            echo "bg-red-100 text-red-800";
                                            break;
                                    }
                                    echo "'>{$rental['status']}</span>";
                                    echo "</td>";
                                    echo "<td class='py-2 px-4 border-b' style='text-align: center'>";
                                    if ($rental['status'] === 'active') {
                                        echo "<a href='cancel_rental.php?id={$rental['id']}' 
                                              class='text-red-500 hover:text-red-700 mr-2'
                                              onclick=\"return confirm('Are you sure you want to cancel this rental?');\">
                                              <i class='fas fa-times'></i> Cancel
                                          </a>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='py-4 text-center text-gray-600'>You have no rental history.</td></tr>";
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
