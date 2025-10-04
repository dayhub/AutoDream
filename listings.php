<?php
include 'config.php';

// Get search filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Build query
$sql = "SELECT * FROM vehicles WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR brand LIKE ? OR model LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($brand)) {
    $sql .= " AND brand = ?";
    $params[] = $brand;
}

if (!empty($min_price)) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
}

$sql .= " ORDER BY date_posted DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Get unique brands for filter
$brands_stmt = $pdo->query("SELECT DISTINCT brand FROM vehicles ORDER BY brand");
$brands = $brands_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Listings - AutoMarket</title>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .filters { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .filter-group { display: inline-block; margin-right: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .vehicle-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .vehicle-card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; }
        .vehicle-photo { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        .vehicle-title { font-size: 1.2em; font-weight: bold; margin: 10px 0; }
        .vehicle-price { color: #007bff; font-size: 1.3em; font-weight: bold; }
        .vehicle-details { color: #666; margin: 5px 0; }
        .no-photo { width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <h1>AutoMarket Vehicle Listings</h1>
        
        <!-- Search and Filters -->
        <div class="filters">
            <form method="GET">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search vehicles...">
                </div>
                
                <div class="filter-group">
                    <label>Brand</label>
                    <select name="brand">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?php echo $b; ?>" <?php echo $brand == $b ? 'selected' : ''; ?>>
                                <?php echo $b; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Min Price</label>
                    <input type="number" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>" placeholder="Min price">
                </div>
                
                <div class="filter-group">
                    <label>Max Price</label>
                    <input type="number" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>" placeholder="Max price">
                </div>
                
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit">Apply Filters</button>
                    <a href="listings.php" style="margin-left: 10px;">Clear</a>
                </div>
            </form>
        </div>
        
        <!-- Vehicle Listings -->
        <div class="vehicle-grid">
            <?php if (empty($vehicles)): ?>
                <p>No vehicles found matching your criteria.</p>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="vehicle-card">
                        <?php if (!empty($vehicle['photo'])): ?>
                            <img src="<?php echo $vehicle['photo']; ?>" alt="<?php echo htmlspecialchars($vehicle['title']); ?>" class="vehicle-photo">
                        <?php else: ?>
                            <div class="no-photo">No Photo Available</div>
                        <?php endif; ?>
                        
                        <div class="vehicle-title"><?php echo htmlspecialchars($vehicle['title']); ?></div>
                        <div class="vehicle-price">$<?php echo number_format($vehicle['price'], 2); ?></div>
                        <div class="vehicle-details">
                            <strong><?php echo htmlspecialchars($vehicle['brand']); ?> <?php echo htmlspecialchars($vehicle['model']); ?></strong>
                        </div>
                        <div class="vehicle-details">Year: <?php echo $vehicle['year']; ?></div>
                        <?php if ($vehicle['mileage']): ?>
                            <div class="vehicle-details">Mileage: <?php echo number_format($vehicle['mileage']); ?> miles</div>
                        <?php endif; ?>
                        <?php if ($vehicle['fuel_type']): ?>
                            <div class="vehicle-details">Fuel: <?php echo htmlspecialchars($vehicle['fuel_type']); ?></div>
                        <?php endif; ?>
                        <?php if ($vehicle['transmission']): ?>
                            <div class="vehicle-details">Transmission: <?php echo htmlspecialchars($vehicle['transmission']); ?></div>
                        <?php endif; ?>
                        <div class="vehicle-details">Location: <?php echo htmlspecialchars($vehicle['location']); ?></div>
                        
                        <?php if (!empty($vehicle['description'])): ?>
                            <div class="vehicle-details" style="margin-top: 10px;">
                                <?php echo nl2br(htmlspecialchars(substr($vehicle['description'], 0, 100))); ?>
                                <?php if (strlen($vehicle['description']) > 100): ?>...<?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="vehicle-details" style="margin-top: 10px;">
                            <small>Contact: <?php echo htmlspecialchars($vehicle['contact_email']); ?>
                            <?php if ($vehicle['contact_phone']): ?>
                                | <?php echo htmlspecialchars($vehicle['contact_phone']); ?>
                            <?php endif; ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <p style="margin-top: 20px;"><a href="add_vehicle.php">Add New Vehicle</a></p>
    </div>
</body>
</html>