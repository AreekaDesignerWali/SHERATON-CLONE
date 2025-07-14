<?php
// Simple test file to check if search is working
echo "<h1>Search Test Page</h1>";
echo "<p>If you can see this, PHP is working!</p>";

// Test database connection
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM hotels");
    $result = $stmt->fetch();
    echo "<p>Database connection successful! Found " . $result['count'] . " hotels.</p>";
} catch(Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}

// Test search parameters
echo "<h2>Search Parameters:</h2>";
echo "<p>Destination: " . ($_GET['destination'] ?? 'Not set') . "</p>";
echo "<p>Check-in: " . ($_GET['checkin'] ?? 'Not set') . "</p>";
echo "<p>Check-out: " . ($_GET['checkout'] ?? 'Not set') . "</p>";
echo "<p>Guests: " . ($_GET['guests'] ?? 'Not set') . "</p>";

echo '<p><a href="index.php">Back to Home</a></p>';
?>
