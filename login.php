<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <i class="fas fa-car text-4xl text-blue-500 mb-2"></i>
                <h1 class="text-2xl font-bold text-gray-800">Car Rental System</h1>
                <p class="text-gray-600">Please sign in to your account</p>
            </div>

            <form action="includes/login_process.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 mb-2">Username</label>
                    <input type="text" id="username" name="username" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                <button type="submit" 
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">
                    Sign In
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="text-gray-600">Don't have an account? 
                    <a href="register.php" class="text-blue-500 hover:underline">Sign up</a>
                </p>
            </div>
        </div>
    </div>
    <div style="text-align:center">
        <?php include 'includes/footer.php';?>
    </div>
</body>
</html>
