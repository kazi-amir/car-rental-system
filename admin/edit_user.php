<?php
require_once '../includes/session_manager.php';
startSecureSession();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php?error=user_not_found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $balance = floatval($_POST['balance']);

    if (empty($username) || empty($email) || empty($role)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $update_stmt = $conn->prepare("
                UPDATE users SET 
                username = :username,
                email = :email,
                role = :role,
                balance = :balance
                WHERE id = :id
            ");
            $update_stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':balance' => $balance,
                ':id' => $user_id
            ]);
            header("Location: users.php?success=user_updated");
            exit();
        } catch (PDOException $e) {
            $error = "Failed to update user: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit User - Car Rental Admin</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Edit User</h2>
                <button id="toggleSidebar" class="md:hidden bg-blue-500 text-white px-3 py-1 rounded">
                    <i class="fas fa-bars"></i> Menu
                </button>
                <a href="users.php" class="text-blue-500 hover:underline ml-4">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Users
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow">
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="username" class="block text-gray-700 mb-2">Username</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="role" class="block text-gray-700 mb-2">Role</label>
                            <select id="role" name="role" 
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div>
                            <label for="balance" class="block text-gray-700 mb-2">Balance ($)</label>
                            <input type="number" id="balance" name="balance" min="0" step="0.01" value="<?= number_format($user['balance'], 2) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                            Save Changes
                        </button>
                    </div>
                </form>
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
