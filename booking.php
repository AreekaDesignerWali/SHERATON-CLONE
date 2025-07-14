<?php
require_once 'db.php';

// Get parameters
$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;

if (!$hotel_id || !$room_id) {
    header('Location: index.php');
    exit;
}

// Get hotel and room details
try {
    $stmt = $pdo->prepare("SELECT h.*, rt.* FROM hotels h 
                          JOIN room_types rt ON h.id = rt.hotel_id 
                          WHERE h.id = ? AND rt.id = ?");
    $stmt->execute([$hotel_id, $room_id]);
    $booking_data = $stmt->fetch();
    
    if (!$booking_data) {
        header('Location: index.php');
        exit;
    }
} catch(PDOException $e) {
    header('Location: index.php');
    exit;
}

// Calculate nights and total
$nights = 0;
$total_amount = 0;
if ($checkin && $checkout) {
    $nights = calculateNights($checkin, $checkout);
    $total_amount = $nights * $booking_data['price_per_night'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_name = sanitize_input($_POST['guest_name']);
    $guest_email = sanitize_input($_POST['guest_email']);
    $guest_phone = sanitize_input($_POST['guest_phone']);
    $special_requests = sanitize_input($_POST['special_requests']);
    $booking_checkin = $_POST['checkin'];
    $booking_checkout = $_POST['checkout'];
    $booking_guests = (int)$_POST['guests'];
    
    // Recalculate with form data
    $booking_nights = calculateNights($booking_checkin, $booking_checkout);
    $booking_total = $booking_nights * $booking_data['price_per_night'];
    
    // Generate booking reference
    $booking_reference = generateBookingReference();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (hotel_id, room_type_id, guest_name, guest_email, guest_phone, 
                              check_in_date, check_out_date, guests_count, total_nights, total_amount, 
                              special_requests, booking_reference) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $hotel_id, $room_id, $guest_name, $guest_email, $guest_phone,
            $booking_checkin, $booking_checkout, $booking_guests, $booking_nights,
            $booking_total, $special_requests, $booking_reference
        ]);
        
        // Redirect to confirmation
        header("Location: confirmation.php?ref=$booking_reference");
        exit;
        
    } catch(PDOException $e) {
        $error_message = "Booking failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay - Sheraton Hotels</title>
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
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #1a365d 0%, #2d5a87 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
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

        /* Main Content */
        .booking-section {
            padding: 3rem 0;
        }

        .booking-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
        }

        .booking-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-title {
            font-size: 2rem;
            color: #1a365d;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            color: #1a365d;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffd700;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a365d;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d5a87;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .error-message {
            background: #fee;
            color: #c53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #c53030;
        }

        .submit-btn {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        /* Booking Summary */
        .booking-summary {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .summary-title {
            font-size: 1.5rem;
            color: #1a365d;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .hotel-summary {
            margin-bottom: 2rem;
        }

        .hotel-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .hotel-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .room-name {
            color: #666;
            margin-bottom: 1rem;
        }

        .booking-details {
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
            margin-bottom: 2rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .detail-row.total {
            font-weight: bold;
            color: #1a365d;
            font-size: 1.1rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.5rem;
            margin-top: 1rem;
        }

        .price-breakdown {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .booking-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
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
        </div>
    </header>

    <section class="booking-section">
        <div class="container">
            <div class="booking-container">
                <div class="booking-form">
                    <h1 class="form-title">Complete Your Booking</h1>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="bookingForm">
                        <div class="form-section">
                            <h3 class="section-title">Guest Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="guest_name">Full Name *</label>
                                    <input type="text" id="guest_name" name="guest_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="guest_email">Email Address *</label>
                                    <input type="email" id="guest_email" name="guest_email" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="guest_phone">Phone Number</label>
                                    <input type="tel" id="guest_phone" name="guest_phone">
                                </div>
                                <div class="form-group">
                                    <label for="guests">Number of Guests</label>
                                    <select id="guests" name="guests">
                                        <?php for($i = 1; $i <= $booking_data['max_guests']; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $guests ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">Stay Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="checkin">Check-in Date *</label>
                                    <input type="date" id="checkin" name="checkin" value="<?php echo $checkin; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="checkout">Check-out Date *</label>
                                    <input type="date" id="checkout" name="checkout" value="<?php echo $checkout; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">Special Requests</h3>
                            <div class="form-group full-width">
                                <label for="special_requests">Special Requests (Optional)</label>
                                <textarea id="special_requests" name="special_requests" placeholder="Any special requests or preferences..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">Complete Booking</button>
                    </form>
                </div>

                <div class="booking-summary">
                    <h2 class="summary-title">Booking Summary</h2>
                    
                    <div class="hotel-summary">
                        <img src="<?php echo htmlspecialchars($booking_data['image_url']); ?>" alt="Hotel" class="hotel-image">
                        <div class="hotel-name"><?php echo htmlspecialchars($booking_data['name']); ?></div>
                        <div class="room-name"><?php echo htmlspecialchars($booking_data['room_name']); ?></div>
                    </div>

                    <div class="booking-details">
                        <div class="detail-row">
                            <span>Check-in:</span>
                            <span id="summary-checkin"><?php echo $checkin ? date('M d, Y', strtotime($checkin)) : 'Select date'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Check-out:</span>
                            <span id="summary-checkout"><?php echo $checkout ? date('M d, Y', strtotime($checkout)) : 'Select date'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Guests:</span>
                            <span id="summary-guests"><?php echo $guests; ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Nights:</span>
                            <span id="summary-nights"><?php echo $nights; ?></span>
                        </div>
                    </div>

                    <div class="price-breakdown">
                        <div class="detail-row">
                            <span>Room Rate:</span>
                            <span>$<?php echo number_format($booking_data['price_per_night']); ?>/night</span>
                        </div>
                        <div class="detail-row">
                            <span>Subtotal:</span>
                            <span id="summary-subtotal">$<?php echo number_format($total_amount); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Taxes & Fees:</span>
                            <span id="summary-taxes">$<?php echo number_format($total_amount * 0.15); ?></span>
                        </div>
                        <div class="detail-row total">
                            <span>Total:</span>
                            <span id="summary-total">$<?php echo number_format($total_amount * 1.15); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        // Set minimum dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('checkin').min = today;
            document.getElementById('checkout').min = today;
        });

        // Update checkout minimum when checkin changes
        document.getElementById('checkin').addEventListener('change', function() {
            const checkinDate = new Date(this.value);
            checkinDate.setDate(checkinDate.getDate() + 1);
            document.getElementById('checkout').min = checkinDate.toISOString().split('T')[0];
            updateSummary();
        });

        document.getElementById('checkout').addEventListener('change', updateSummary);
        document.getElementById('guests').addEventListener('change', updateSummary);

        function updateSummary() {
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const guests = document.getElementById('guests').value;
            const roomRate = <?php echo $booking_data['price_per_night']; ?>;

            if (checkin && checkout) {
                const checkinDate = new Date(checkin);
                const checkoutDate = new Date(checkout);
                const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
                
                if (nights > 0) {
                    const subtotal = nights * roomRate;
                    const taxes = subtotal * 0.15;
                    const total = subtotal + taxes;

                    document.getElementById('summary-checkin').textContent = checkinDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
                    document.getElementById('summary-checkout').textContent = checkoutDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
                    document.getElementById('summary-guests').textContent = guests;
                    document.getElementById('summary-nights').textContent = nights;
                    document.getElementById('summary-subtotal').textContent = '$' + subtotal.toLocaleString();
                    document.getElementById('summary-taxes').textContent = '$' + taxes.toLocaleString();
                    document.getElementById('summary-total').textContent = '$' + total.toLocaleString();
                }
            }
        }

        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            
            if (!checkin || !checkout) {
                e.preventDefault();
                alert('Please select check-in and check-out dates.');
                return;
            }
            
            const checkinDate = new Date(checkin);
            const checkoutDate = new Date(checkout);
            
            if (checkoutDate <= checkinDate) {
                e.preventDefault();
                alert('Check-out date must be after check-in date.');
                return;
            }
        });
    </script>
</body>
</html>
