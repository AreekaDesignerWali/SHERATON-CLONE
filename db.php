<?php
// Database configuration
$host = 'localhost';
$dbname = 'dbklg0v6fc4emx';
$username = 'uc7ggok7oyoza';
$password = 'gqypavorhbbc';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate booking reference
function generateBookingReference() {
    return 'SH' . strtoupper(uniqid());
}

// Function to calculate nights between dates
function calculateNights($checkin, $checkout) {
    $date1 = new DateTime($checkin);
    $date2 = new DateTime($checkout);
    $interval = $date1->diff($date2);
    return $interval->days;
}
?>
