<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <i class="fas fa-car text-4xl text-blue-500 mb-2"></i>
                <h1 class="text-2xl font-bold text-gray-800">Create Account</h1>
                <p class="text-gray-600">Join our car rental system</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['error']) {
                            case 'username_taken':
                                echo 'Username already taken';
                                break;
                            case 'email_taken':
                                echo 'Email already registered';
                                break;
                            case 'password_mismatch':
                                echo 'Passwords do not match';
                                break;
                            default:
                                echo 'Registration failed';
                        }
                    ?>
                </div>
            <?php endif; ?>

            <form action="includes/register_process.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 mb-2">Username</label>
                    <input type="text" id="username" name="username" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <button type="submit" 
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">
                    Register
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="text-gray-600">Already have an account? 
                    <a href="login.php" class="text-blue-500 hover:underline">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
