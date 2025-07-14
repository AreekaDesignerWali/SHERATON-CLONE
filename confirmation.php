<?php
require_once 'db.php';

// Get booking reference
$booking_ref = isset($_GET['ref']) ? sanitize_input($_GET['ref']) : '';

if (!$booking_ref) {
    header('Location: index.php');
    exit;
}

// Get booking details
try {
    $stmt = $pdo->prepare("SELECT b.*, h.name as hotel_name, h.location, h.city, h.country, 
                          rt.room_name, rt.room_type, rt.price_per_night 
                          FROM bookings b 
                          JOIN hotels h ON b.hotel_id = h.id 
                          JOIN room_types rt ON b.room_type_id = rt.id 
                          WHERE b.booking_reference = ?");
    $stmt->execute([$booking_ref]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        header('Location: index.php');
        exit;
    }
} catch(PDOException $e) {
    header('Location: index.php');
    exit;
}

$total_with_taxes = $booking['total_amount'] * 1.15;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Sheraton Hotels</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 20px;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #1a365d 0%, #2d5a87 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd700;
            cursor: pointer;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #ffd700;
        }

        /* Confirmation Card */
        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .success-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .success-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .booking-details {
            padding: 2rem;
        }

        .booking-ref {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
            border-left: 4px solid #ffd700;
        }

        .ref-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .ref-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a365d;
            letter-spacing: 1px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffd700;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .detail-row strong {
            color: #1a365d;
        }

        .price-summary {
            background: #1a365d;
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .price-row.total {
            font-size: 1.3rem;
            font-weight: bold;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 0.5rem;
            margin-top: 1rem;
            color: #ffd700;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        /* Important Info */
        .important-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .info-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 1rem;
        }

        .info-list {
            list-style: none;
            color: #856404;
        }

        .info-list li {
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .info-list li:before {
            content: "•";
            color: #ffd700;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .success-title {
                font-size: 2rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo" onclick="goHome()">SHERATON</div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#hotels">Hotels</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="confirmation-card">
            <div class="success-header">
                <div class="success-icon">✅</div>
                <h1 class="success-title">Booking Confirmed!</h1>
                <p class="success-subtitle">Your reservation has been successfully processed</p>
            </div>

            <div class="booking-details">
                <div class="booking-ref">
                    <div class="ref-label">Booking Reference Number</div>
                    <div class="ref-number"><?php echo htmlspecialchars($booking['booking_reference']); ?></div>
                </div>

                <div class="details-grid">
                    <div class="detail-section">
                        <h3 class="section-title">Hotel Information</h3>
                        <div class="detail-row">
                            <span>Hotel:</span>
                            <strong><?php echo htmlspecialchars($booking['hotel_name']); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Location:</span>
                            <span><?php echo htmlspecialchars($booking['city'] . ', ' . $booking['country']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Room Type:</span>
                            <strong><?php echo htmlspecialchars($booking['room_name']); ?></strong>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 class="section-title">Guest Information</h3>
                        <div class="detail-row">
                            <span>Guest Name:</span>
                            <strong><?php echo htmlspecialchars($booking['guest_name']); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Email:</span>
                            <span><?php echo htmlspecialchars($booking['guest_email']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Phone:</span>
                            <span><?php echo htmlspecialchars($booking['guest_phone'] ?: 'Not provided'); ?></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 class="section-title">Stay Details</h3>
                        <div class="detail-row">
                            <span>Check-in:</span>
                            <strong><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Check-out:</span>
                            <strong><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Nights:</span>
                            <span><?php echo $booking['total_nights']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Guests:</span>
                            <span><?php echo $booking['guests_count']; ?></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 class="section-title">Booking Status</h3>
                        <div class="detail-row">
                            <span>Status:</span>
                            <strong style="color: #28a745;">Confirmed</strong>
                        </div>
                        <div class="detail-row">
                            <span>Booked on:</span>
                            <span><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($booking['special_requests']): ?>
                <div class="detail-section">
                    <h3 class="section-title">Special Requests</h3>
                    <p><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                </div>
                <?php endif; ?>

                <div class="price-summary">
                    <div class="price-row">
                        <span>Room Rate (<?php echo $booking['total_nights']; ?> nights):</span>
                        <span>$<?php echo number_format($booking['total_amount']); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Taxes & Fees:</span>
                        <span>$<?php echo number_format($booking['total_amount'] * 0.15); ?></span>
                    </div>
                    <div class="price-row total">
                        <span>Total Paid:</span>
                        <span>$<?php echo number_format($total_with_taxes); ?></span>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="index.php" class="btn btn-primary">Book Another Stay</a>
                    <button onclick="window.print()" class="btn btn-secondary">Print Confirmation</button>
                </div>
            </div>
        </div>

        <div class="important-info">
            <div class="info-title">Important Information</div>
            <ul class="info-list">
                <li>Please arrive at the hotel with a valid photo ID and the credit card used for booking</li>
                <li>Check-in time is 3:00 PM and check-out time is 11:00 AM</li>
                <li>A confirmation email has been sent to your registered email address</li>
                <li>For any changes or cancellations, please contact us at least 24 hours in advance</li>
                <li>Contact hotel directly at 1-800-SHERATON for any special arrangements</li>
            </ul>
        </div>
    </div>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        // Auto-scroll to top on page load
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });
    </script>
</body>
</html>
