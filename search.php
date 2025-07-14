<?php
require_once 'db.php';

// Debug: Check if file is being accessed
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get search parameters with default values
$destination = isset($_GET['destination']) ? sanitize_input($_GET['destination']) : '';
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;

// Build search query
$sql = "SELECT h.*, MIN(rt.price_per_night) as min_price 
        FROM hotels h 
        LEFT JOIN room_types rt ON h.id = rt.hotel_id 
        WHERE 1=1";
$params = [];

if (!empty($destination)) {
    $sql .= " AND (h.city LIKE ? OR h.country LIKE ? OR h.name LIKE ? OR h.location LIKE ?)";
    $searchTerm = "%$destination%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " GROUP BY h.id ORDER BY h.rating DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hotels = $stmt->fetchAll();
} catch(PDOException $e) {
    $hotels = [];
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Sheraton Hotels</title>
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

        /* Search Summary */
        .search-summary {
            background: white;
            padding: 2rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-info h1 {
            color: #1a365d;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .search-details {
            color: #666;
            font-size: 1rem;
        }

        .modify-search {
            background: #ffd700;
            color: #1a365d;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .modify-search:hover {
            transform: translateY(-2px);
        }

        /* Filters */
        .filters-section {
            background: white;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .filters {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-weight: 600;
            color: #1a365d;
            font-size: 0.9rem;
        }

        .filter-group select {
            padding: 0.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        /* Results */
        .results-section {
            padding: 2rem 0;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .results-count {
            color: #666;
            font-size: 1.1rem;
        }

        .hotels-list {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .hotel-result {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .hotel-result:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .hotel-content {
            display: grid;
            grid-template-columns: 300px 1fr auto;
            gap: 1.5rem;
        }

        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .hotel-details {
            padding: 1.5rem 0;
        }

        .hotel-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .hotel-location {
            color: #666;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #ffd700;
            font-size: 1.1rem;
        }

        .rating-text {
            color: #666;
            font-size: 0.9rem;
        }

        .hotel-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .amenity-tag {
            background: #e2e8f0;
            color: #1a365d;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .hotel-description {
            color: #666;
            line-height: 1.5;
        }

        .hotel-pricing {
            padding: 1.5rem;
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }

        .price-info {
            margin-bottom: 1rem;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: #2d5a87;
        }

        .price-note {
            color: #666;
            font-size: 0.9rem;
        }

        .view-hotel-btn {
            background: linear-gradient(135deg, #2d5a87 0%, #1a365d 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .view-hotel-btn:hover {
            transform: translateY(-2px);
        }

        .no-results {
            text-align: center;
            padding: 4rem 0;
            color: #666;
        }

        .no-results h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #1a365d;
        }

        .error-message {
            background: #fee;
            color: #c53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #c53030;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hotel-content {
                grid-template-columns: 1fr;
            }

            .hotel-pricing {
                text-align: left;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .summary-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
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

    <section class="search-summary">
        <div class="container">
            <div class="summary-content">
                <div class="search-info">
                    <h1>Hotels<?php echo !empty($destination) ? ' in ' . htmlspecialchars($destination) : ''; ?></h1>
                    <div class="search-details">
                        <?php if($checkin && $checkout): ?>
                            <?php echo date('M d', strtotime($checkin)) . ' - ' . date('M d, Y', strtotime($checkout)); ?> • 
                        <?php endif; ?>
                        <?php echo $guests; ?> Guest<?php echo $guests > 1 ? 's' : ''; ?>
                    </div>
                </div>
                <a href="index.php" class="modify-search">Modify Search</a>
            </div>
        </div>
    </section>

    <section class="filters-section">
        <div class="container">
            <div class="filters">
                <div class="filter-group">
                    <label>Sort by</label>
                    <select id="sortBy" onchange="sortResults()">
                        <option value="rating">Best Rating</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name">Hotel Name</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Price Range</label>
                    <select id="priceFilter" onchange="filterByPrice()">
                        <option value="all">All Prices</option>
                        <option value="0-200">Under $200</option>
                        <option value="200-400">$200 - $400</option>
                        <option value="400-600">$400 - $600</option>
                        <option value="600+">$600+</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Rating</label>
                    <select id="ratingFilter" onchange="filterByRating()">
                        <option value="all">All Ratings</option>
                        <option value="4.5">4.5+ Stars</option>
                        <option value="4.0">4.0+ Stars</option>
                        <option value="3.5">3.5+ Stars</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <section class="results-section">
        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="results-header">
                <div class="results-count">
                    <?php echo count($hotels); ?> hotel<?php echo count($hotels) != 1 ? 's' : ''; ?> found
                </div>
            </div>

            <?php if (empty($hotels)): ?>
                <div class="no-results">
                    <h2>No hotels found</h2>
                    <p>Try adjusting your search criteria or browse our featured hotels.</p>
                    <a href="index.php" class="modify-search" style="margin-top: 1rem;">Search Again</a>
                </div>
            <?php else: ?>
                <div class="hotels-list" id="hotelsList">
                    <?php foreach($hotels as $hotel): ?>
                        <div class="hotel-result" data-rating="<?php echo $hotel['rating']; ?>" data-price="<?php echo $hotel['min_price'] ?? 299; ?>" onclick="viewHotel(<?php echo $hotel['id']; ?>)">
                            <div class="hotel-content">
                                <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
                                
                                <div class="hotel-details">
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
                                    
                                    <div class="hotel-amenities">
                                        <?php 
                                        $amenities = explode(',', $hotel['amenities']);
                                        foreach(array_slice($amenities, 0, 4) as $amenity): 
                                        ?>
                                            <span class="amenity-tag"><?php echo trim($amenity); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <p class="hotel-description">
                                        <?php echo htmlspecialchars(substr($hotel['description'], 0, 150)) . '...'; ?>
                                    </p>
                                </div>
                                
                                <div class="hotel-pricing">
                                    <div class="price-info">
                                        <div class="price">$<?php echo number_format($hotel['min_price'] ?? 299); ?></div>
                                        <div class="price-note">per night</div>
                                    </div>
                                    <button class="view-hotel-btn" onclick="event.stopPropagation(); viewHotel(<?php echo $hotel['id']; ?>)">
                                        View Hotel
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        function viewHotel(hotelId) {
            const checkin = '<?php echo $checkin; ?>';
            const checkout = '<?php echo $checkout; ?>';
            const guests = '<?php echo $guests; ?>';
            
            let url = 'hotel-details.php?id=' + hotelId;
            if (checkin && checkout) {
                url += '&checkin=' + checkin + '&checkout=' + checkout + '&guests=' + guests;
            }
            
            window.location.href = url;
        }

        function sortResults() {
            const sortBy = document.getElementById('sortBy').value;
            const hotelsList = document.getElementById('hotelsList');
            const hotels = Array.from(hotelsList.children);

            hotels.sort((a, b) => {
                switch(sortBy) {
                    case 'rating':
                        return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
                    case 'price_low':
                        return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    case 'price_high':
                        return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    case 'name':
                        const nameA = a.querySelector('.hotel-name').textContent;
                        const nameB = b.querySelector('.hotel-name').textContent;
                        return nameA.localeCompare(nameB);
                    default:
                        return 0;
                }
            });

            hotels.forEach(hotel => hotelsList.appendChild(hotel));
        }

        function filterByPrice() {
            const priceFilter = document.getElementById('priceFilter').value;
            const hotels = document.querySelectorAll('.hotel-result');

            hotels.forEach(hotel => {
                const price = parseFloat(hotel.dataset.price);
                let show = true;

                switch(priceFilter) {
                    case '0-200':
                        show = price < 200;
                        break;
                    case '200-400':
                        show = price >= 200 && price < 400;
                        break;
                    case '400-600':
                        show = price >= 400 && price < 600;
                        break;
                    case '600+':
                        show = price >= 600;
                        break;
                    default:
                        show = true;
                }

                hotel.style.display = show ? 'block' : 'none';
            });
        }

        function filterByRating() {
            const ratingFilter = document.getElementById('ratingFilter').value;
            const hotels = document.querySelectorAll('.hotel-result');

            hotels.forEach(hotel => {
                const rating = parseFloat(hotel.dataset.rating);
                const show = ratingFilter === 'all' || rating >= parseFloat(ratingFilter);
                hotel.style.display = show ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>
