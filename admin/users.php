<?php
require_once '../includes/session_manager.php';
startSecureSession();
require_once '../includes/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    try {
        // Prevent admin from deleting themselves
        if ($user_id == $_SESSION['user_id']) {
            header("Location: users.php?error=cannot_delete_self");
            exit();
        }
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        header("Location: users.php?success=user_deleted");
        exit();
    } catch(PDOException $e) {
        header("Location: users.php?error=delete_failed");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Users - Car Rental Admin</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Manage Users</h2>
                <button id="toggleSidebar" class="md:hidden bg-blue-500 text-white px-3 py-1 rounded">
                    <i class="fas fa-bars"></i> Menu
                </button>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['success']) {
                            case 'user_deleted':
                                echo 'User successfully deleted';
                                break;
                            case 'user_updated':
                                echo 'User successfully updated';
                                break;
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['error']) {
                            case 'cannot_delete_self':
                                echo 'You cannot delete your own account';
                                break;
                            case 'delete_failed':
                                echo 'Failed to delete user';
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
                                <th class="py-2 px-4 border-b">Username</th>
                                <th class="py-2 px-4 border-b">Email</th>
                                <th class="py-2 px-4 border-b">Role</th>
                                <th class="py-2 px-4 border-b">Balance</th>
                                <th class="py-2 px-4 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("SELECT id, username, email, role, balance FROM users ORDER BY id DESC");
                            while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>{$user['id']}</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>" . htmlspecialchars($user['username']) . "</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>" . htmlspecialchars($user['email']) . "</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>" . htmlspecialchars($user['role']) . "</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>$" . number_format($user['balance'], 2) . "</td>";
                                echo "<td class='py-2 px-4 border-b' style='text-align: center'>
                                        <a href='edit_user.php?id={$user['id']}' class='text-blue-500 hover:text-blue-700 mr-2'>
                                            <i class='fas fa-edit'></i>
                                        </a>
                                        <a href='users.php?delete={$user['id']}' class='text-red-500 hover:text-red-700' onclick=\"return confirm('Are you sure you want to delete this user?');\">
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

    </script>
</body>
</html>
