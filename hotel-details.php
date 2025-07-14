<?php
require_once 'db.php';

// Get hotel ID from URL
$hotel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;

if (!$hotel_id) {
    header('Location: index.php');
    exit;
}

// Get hotel details
try {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch();
    
    if (!$hotel) {
        header('Location: index.php');
        exit;
    }
    
    // Get room types for this hotel
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE hotel_id = ? ORDER BY price_per_night ASC");
    $stmt->execute([$hotel_id]);
    $rooms = $stmt->fetchAll();
    
    // Get reviews for this hotel
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE hotel_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$hotel_id]);
    $reviews = $stmt->fetchAll();
    
} catch(PDOException $e) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['name']); ?> - Sheraton Hotels</title>
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

        /* Breadcrumb */
        .breadcrumb {
            background: white;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .breadcrumb a {
            color: #2d5a87;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Hotel Hero */
        .hotel-hero {
            background: white;
            padding: 2rem 0;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .hotel-main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .hotel-info {
            padding: 1rem;
        }

        .hotel-title {
            font-size: 2.5rem;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .hotel-location {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stars {
            color: #ffd700;
            font-size: 1.5rem;
        }

        .rating-details {
            color: #666;
        }

        .hotel-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1a365d;
            font-weight: 500;
        }

        .amenity-icon {
            width: 20px;
            height: 20px;
            background: #ffd700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        /* Rooms Section */
        .rooms-section {
            background: white;
            padding: 3rem 0;
            margin-top: 2rem;
        }

        .section-title {
            font-size: 2rem;
            color: #1a365d;
            margin-bottom: 2rem;
            text-align: center;
        }

        .rooms-grid {
            display: grid;
            gap: 2rem;
        }

        .room-card {
            background: #f8f9fa;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .room-card:hover {
            transform: translateY(-3px);
        }

        .room-content {
            display: grid;
            grid-template-columns: 300px 1fr auto;
            gap: 1.5rem;
        }

        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .room-details {
            padding: 1.5rem 0;
        }

        .room-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .room-type {
            color: #666;
            margin-bottom: 1rem;
        }

        .room-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .feature-tag {
            background: #e2e8f0;
            color: #1a365d;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .room-amenities {
            color: #666;
            line-height: 1.5;
        }

        .room-pricing {
            padding: 1.5rem;
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .price-display {
            margin-bottom: 1rem;
        }

        .room-price {
            font-size: 2rem;
            font-weight: bold;
            color: #2d5a87;
        }

        .price-note {
            color: #666;
            font-size: 0.9rem;
        }

        .book-btn {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .book-btn:hover {
            transform: translateY(-2px);
        }

        /* Reviews Section */
        .reviews-section {
            background: white;
            padding: 3rem 0;
            margin-top: 2rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .review-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid #ffd700;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .reviewer-name {
            font-weight: bold;
            color: #1a365d;
        }

        .review-rating {
            color: #ffd700;
        }

        .review-text {
            color: #666;
            line-height: 1.6;
        }

        .review-date {
            color: #999;
            font-size: 0.8rem;
            margin-top: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content {
                grid-template-columns: 1fr;
            }

            .room-content {
                grid-template-columns: 1fr;
            }

            .room-pricing {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .hotel-title {
                font-size: 2rem;
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

    <section class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a> > 
            <a href="search.php">Hotels</a> > 
            <?php echo htmlspecialchars($hotel['name']); ?>
        </div>
    </section>

    <section class="hotel-hero">
        <div class="container">
            <div class="hero-content">
                <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-main-image">
                
                <div class="hotel-info">
                    <h1 class="hotel-title"><?php echo htmlspecialchars($hotel['name']); ?></h1>
                    <p class="hotel-location"><?php echo htmlspecialchars($hotel['location'] . ', ' . $hotel['city'] . ', ' . $hotel['country']); ?></p>
                    
                    <div class="hotel-rating">
                        <span class="stars">
                            <?php 
                            $rating = $hotel['rating'];
                            for($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '★' : '☆';
                            }
                            ?>
                        </span>
                        <div class="rating-details">
                            <strong><?php echo $hotel['rating']; ?>/5</strong><br>
                            <small><?php echo $hotel['total_reviews']; ?> reviews</small>
                        </div>
                    </div>
                    
                    <p class="hotel-description"><?php echo htmlspecialchars($hotel['description']); ?></p>
                    
                    <div class="amenities-grid">
                        <?php 
                        $amenities = explode(',', $hotel['amenities']);
                        foreach($amenities as $amenity): 
                        ?>
                            <div class="amenity-item">
                                <div class="amenity-icon">✓</div>
                                <?php echo trim($amenity); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rooms-section">
        <div class="container">
            <h2 class="section-title">Available Rooms</h2>
            
            <?php if (empty($rooms)): ?>
                <p style="text-align: center; color: #666; font-size: 1.2rem;">No rooms available at this hotel.</p>
            <?php else: ?>
                <div class="rooms-grid">
                    <?php foreach($rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-content">
                                <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="<?php echo htmlspecialchars($room['room_name']); ?>" class="room-image">
                                
                                <div class="room-details">
                                    <h3 class="room-name"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                                    <p class="room-type"><?php echo htmlspecialchars($room['room_type']); ?> • <?php echo htmlspecialchars($room['room_size']); ?> • <?php echo htmlspecialchars($room['bed_type']); ?></p>
                                    
                                    <div class="room-features">
                                        <span class="feature-tag">Max <?php echo $room['max_guests']; ?> guests</span>
                                        <span class="feature-tag"><?php echo $room['available_rooms']; ?> available</span>
                                    </div>
                                    
                                    <div class="room-amenities">
                                        <?php echo htmlspecialchars($room['description']); ?>
                                        <br><br>
                                        <strong>Amenities:</strong> <?php echo htmlspecialchars($room['amenities']); ?>
                                    </div>
                                </div>
                                
                                <div class="room-pricing">
                                    <div class="price-display">
                                        <div class="room-price">$<?php echo number_format($room['price_per_night']); ?></div>
                                        <div class="price-note">per night</div>
                                    </div>
                                    <button class="book-btn" onclick="bookRoom(<?php echo $room['id']; ?>)">
                                        Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($reviews)): ?>
    <section class="reviews-section">
        <div class="container">
            <h2 class="section-title">Guest Reviews</h2>
            <div class="reviews-grid">
                <?php foreach($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-name"><?php echo htmlspecialchars($review['guest_name']); ?></div>
                            <div class="review-rating">
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    echo $i <= $review['rating'] ? '★' : '☆';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></div>
                        <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        function bookRoom(roomId) {
            const checkin = '<?php echo $checkin; ?>';
            const checkout = '<?php echo $checkout; ?>';
            const guests = '<?php echo $guests; ?>';
            const hotelId = '<?php echo $hotel_id; ?>';
            
            let url = `booking.php?hotel_id=${hotelId}&room_id=${roomId}`;
            if (checkin && checkout) {
                url += `&checkin=${checkin}&checkout=${checkout}&guests=${guests}`;
            }
            
            window.location.href = url;
        }
    </script>
</body>
</html>
