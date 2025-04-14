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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Rental History - Car Rental Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="transition-transform duration-300">
            <?php include 'dashboard.php'; ?>
        </div>

        <!-- Main Content -->
        <div id="main-content" class="flex-1 p-8 transition-all duration-300">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Rental History</h2>
                <button id="toggleSidebar" class="md:hidden bg-blue-500 text-white px-3 py-1 rounded">
                    <i class="fas fa-bars"></i> Menu
                </button>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b">Rental ID</th>
                                <th class="py-2 px-4 border-b">User</th>
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
                                SELECT r.id, u.username, c.make, c.model, r.start_date, r.end_date, 
                                       r.total_price, r.status, r.created_at
                                FROM rentals r
                                JOIN users u ON r.user_id = u.id
                                JOIN cars c ON r.car_id = c.id
                                ORDER BY r.created_at DESC
                            ");
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                while ($rental = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td class='py-2 px-4 border-b'>{$rental['id']}</td>";
                                    echo "<td class='py-2 px-4 border-b'>" . htmlspecialchars($rental['username']) . "</td>";
                                    echo "<td class='py-2 px-4 border-b'>{$rental['make']} {$rental['model']}</td>";
                                    echo "<td class='py-2 px-4 border-b'>{$rental['start_date']} to {$rental['end_date']}</td>";
                                    echo "<td class='py-2 px-4 border-b'>$" . number_format($rental['total_price'], 2) . "</td>";
                                    echo "<td class='py-2 px-4 border-b'>";
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
                                    echo "'>" . htmlspecialchars($rental['status']) . "</span>";
                                    echo "</td>";
                                    echo "<td class='py-2 px-4 border-b'>";
                                    echo "<a href='../customer/rental_receipt.php?id={$rental['id']}' 
                                          class='text-blue-500 hover:text-blue-700' target='_blank'>
                                          <i class='fas fa-receipt'></i> Receipt
                                          </a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='py-4 text-center text-gray-600'>No rental history found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        toggleBtn.addEventListener('click', () => {
            if (sidebar.style.transform === 'translateX(-100%)') {
                sidebar.style.transform = 'translateX(0)';
                mainContent.style.marginLeft = '0';
            } else {
                sidebar.style.transform = 'translateX(-100%)';
                mainContent.style.marginLeft = '0';
            }
        });

        // Hide sidebar by default on small screens or if not dashboard page
        if (window.innerWidth < 768 || window.location.pathname.indexOf('dashboard.php') === -1) {
            sidebar.style.transform = 'translateX(-100%)';
        }
    </script>
</body>
</html>
