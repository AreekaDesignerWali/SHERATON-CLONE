<?php
require_once 'db.php';

// Get featured hotels
try {
    $stmt = $pdo->query("SELECT * FROM hotels ORDER BY rating DESC LIMIT 4");
    $featured_hotels = $stmt->fetchAll();
} catch(PDOException $e) {
    $featured_hotels = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - Luxury Accommodations Worldwide</title>
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

        /* Header Styles */
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

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200') center/cover;
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        /* Search Form */
        .search-section {
            background: white;
            padding: 3rem 0;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
        }

        .search-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: -100px;
            position: relative;
            z-index: 10;
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a365d;
        }

        .form-group input, .form-group select {
            padding: 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #2d5a87;
        }

        .search-btn {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
        }

        /* Featured Hotels */
        .featured-section {
            padding: 4rem 0;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #1a365d;
            margin-bottom: 3rem;
        }

        .hotels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .hotel-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .hotel-info {
            padding: 1.5rem;
        }

        .hotel-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .hotel-location {
            color: #666;
            margin-bottom: 1rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #ffd700;
            font-size: 1.2rem;
        }

        .rating-text {
            color: #666;
            font-size: 0.9rem;
        }

        .hotel-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d5a87;
        }

        /* Footer */
        footer {
            background: #1a365d;
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: #ffd700;
            margin-bottom: 1rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #ffd700;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #2d5a87;
            color: #cbd5e0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
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
        <div class="container">
            <div class="header-content">
                <div class="logo">SHERATON</div>
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

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Sheraton</h1>
            <p>Experience luxury and comfort at our world-class hotels</p>
        </div>
    </section>

    <section class="search-section">
        <div class="container">
            <form class="search-form" method="GET" action="search.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="destination">Destination</label>
                        <input type="text" id="destination" name="destination" placeholder="Where are you going?" required>
                    </div>
                    <div class="form-group">
                        <label for="checkin">Check-in</label>
                        <input type="date" id="checkin" name="checkin" required>
                    </div>
                    <div class="form-group">
                        <label for="checkout">Check-out</label>
                        <input type="date" id="checkout" name="checkout" required>
                    </div>
                    <div class="form-group">
                        <label for="guests">Guests</label>
                        <select id="guests" name="guests">
                            <option value="1">1 Guest</option>
                            <option value="2" selected>2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5+ Guests</option>
                        </select>
                    </div>
                    <button type="submit" class="search-btn">Search Hotels</button>
                </div>
            </form>
        </div>
    </section>

    <section class="featured-section" id="hotels">
        <div class="container">
            <h2 class="section-title">Featured Hotels</h2>
            <div class="hotels-grid">
                <?php foreach($featured_hotels as $hotel): ?>
                <div class="hotel-card" onclick="viewHotel(<?php echo $hotel['id']; ?>)">
                    <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
                    <div class="hotel-info">
                        <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <p class="hotel-location"><?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?></p>
                        <div class="hotel-rating">
                            <span class="stars">
                                <?php 
                                $rating = $hotel['rating'];
                                for($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </span>
                            <span class="rating-text"><?php echo $hotel['rating']; ?> (<?php echo $hotel['total_reviews']; ?> reviews)</span>
                        </div>
                        <div class="hotel-price">From $299/night</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Sheraton</h3>
                    <ul>
                        <li><a href="#">Our Story</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Investor Relations</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Guest Services</h3>
                    <ul>
                        <li><a href="#">Customer Support</a></li>
                        <li><a href="#">Loyalty Program</a></li>
                        <li><a href="#">Gift Cards</a></li>
                        <li><a href="#">Special Offers</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Destinations</h3>
                    <ul>
                        <li><a href="#">New York</a></li>
                        <li><a href="#">Miami</a></li>
                        <li><a href="#">Chicago</a></li>
                        <li><a href="#">Denver</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul>
                        <li>Phone: 1-800-SHERATON</li>
                        <li>Email: info@sheraton.com</li>
                        <li>24/7 Customer Service</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Sheraton Hotels. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('checkin').min = today;
            document.getElementById('checkout').min = today;
            
            // Set default dates
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dayAfter = new Date();
            dayAfter.setDate(dayAfter.getDate() + 2);
            
            document.getElementById('checkin').value = tomorrow.toISOString().split('T')[0];
            document.getElementById('checkout').value = dayAfter.toISOString().split('T')[0];
        });

        // Update checkout minimum date when checkin changes
        document.getElementById('checkin').addEventListener('change', function() {
            const checkinDate = new Date(this.value);
            checkinDate.setDate(checkinDate.getDate() + 1);
            document.getElementById('checkout').min = checkinDate.toISOString().split('T')[0];
        });

        // View hotel details
        function viewHotel(hotelId) {
            window.location.href = 'hotel-details.php?id=' + hotelId;
        }
    </script>
</body>
</html>
