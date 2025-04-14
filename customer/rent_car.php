<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

// Check if car ID is provided
if (!isset($_GET['id'])) {
    header("Location: available_cars.php");
    exit();
}

$car_id = $_GET['id'];

// Get car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id AND available = TRUE");
$stmt->bindParam(':id', $car_id);
$stmt->execute();
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: available_cars.php?error=car_not_available");
    exit();
}

// Get user balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$balance = $user['balance'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Calculate days and total price
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;
    $total_price = $days * $car['price_per_day'];
    
    // Check if user has enough balance
    if ($balance < $total_price) {
        header("Location: rent_car.php?id=$car_id&error=insufficient_balance");
        exit();
    }
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Create rental record
        $stmt = $conn->prepare("INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price) 
                               VALUES (:user_id, :car_id, :start_date, :end_date, :total_price)");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':car_id', $car_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':total_price', $total_price);
        $stmt->execute();
        
        // Update car availability
        $stmt = $conn->prepare("UPDATE cars SET available = FALSE WHERE id = :id");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        
        // Deduct from user balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance - :amount WHERE id = :id");
        $stmt->bindParam(':amount', $total_price);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        header("Location: my_rentals.php?success=car_rented");
        exit();
    } catch(PDOException $e) {
        $conn->rollBack();
        header("Location: rent_car.php?id=$car_id&error=rental_failed");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Car - Car Rental System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'dashboard.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Rent Car</h2>
                <a href="available_cars.php" class="text-blue-500 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Available Cars
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                        switch($_GET['error']) {
                            case 'insufficient_balance':
                                echo 'Insufficient balance to complete this rental';
                                break;
                            case 'rental_failed':
                                echo 'Failed to process rental. Please try again.';
                                break;
                        }
                    ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Car Details -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">Car Details</h3>
                        <div class="bg-gray-200 h-64 flex items-center justify-center mb-4 rounded-lg">
                            <?php if ($car['image_path']): ?>
                                <img src="../<?php echo $car['image_path']; ?>" alt="<?php echo $car['make'] . ' ' . $car['model']; ?>" class="h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-car text-6xl text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                        <h4 class="font-bold text-lg"><?php echo $car['make'] . ' ' . $car['model']; ?></h4>
                        <p class="text-gray-600 text-sm"><?php echo $car['year'] . ' • ' . $car['color']; ?></p>
                        <p class="mt-2 text-lg font-bold">$<?php echo number_format($car['price_per_day'], 2); ?><span class="text-sm font-normal text-gray-600"> / day</span></p>
                    </div>

                    <!-- Rental Form -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">Rental Information</h3>
                        <form method="POST">
                            <div class="mb-4">
                                <label for="start_date" class="block text-gray-700 mb-2">Start Date</label>
                                <input type="text" id="start_date" name="start_date" 
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div class="mb-4">
                                <label for="end_date" class="block text-gray-700 mb-2">End Date</label>
                                <input type="text" id="end_date" name="end_date" 
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Estimated Price</label>
                                <div id="price_estimate" class="text-xl font-bold">$0.00</div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Your Balance</label>
                                <div class="text-xl font-bold">$<?php echo number_format($balance, 2); ?></div>
                            </div>
                            <button type="submit" 
                                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">
                                Confirm Rental
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize date pickers
        const startDate = flatpickr("#start_date", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (endDate.selectedDates[0]) {
                    calculatePrice();
                }
            }
        });

        const endDate = flatpickr("#end_date", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (startDate.selectedDates[0]) {
                    calculatePrice();
                }
            }
        });

        // Calculate rental price
        function calculatePrice() {
            const start = new Date(startDate.selectedDates[0]);
            const end = new Date(endDate.selectedDates[0]);
            let days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

            const pricePerDay = <?php echo $car['price_per_day']; ?>;
            const total = (days + 1) * pricePerDay;
            
            document.getElementById('price_estimate').textContent = '$' + total.toFixed(2);
        }
    </script>
</body>
</html>
