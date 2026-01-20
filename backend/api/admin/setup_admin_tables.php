<?php
// Setup admin-specific database tables
require_once '../config.php';

$conn = getDBConnection();

$sql = "
CREATE TABLE IF NOT EXISTS admin_users (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS pricing_config (
    id VARCHAR(50) PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value DECIMAL(10, 2) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS paper_size_multipliers (
    id VARCHAR(50) PRIMARY KEY,
    paper_size VARCHAR(50) UNIQUE NOT NULL,
    multiplier DECIMAL(10, 2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS binding_costs (
    id VARCHAR(50) PRIMARY KEY,
    binding_type VARCHAR(100) UNIQUE NOT NULL,
    cost DECIMAL(10, 2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";

// Execute each statement
$statements = explode(';', $sql);
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            echo "Error creating table: " . $conn->error . "\n";
        }
    }
}

// Insert default pricing configuration
$defaultPricing = [
    ['id' => uniqid('price_', true), 'config_key' => 'color_page_cost', 'config_value' => 0.15, 'description' => 'Cost per color page'],
    ['id' => uniqid('price_', true), 'config_key' => 'black_white_page_cost', 'config_value' => 0.05, 'description' => 'Cost per black & white page'],
    ['id' => uniqid('price_', true), 'config_key' => 'service_fee', 'config_value' => 2.00, 'description' => 'Base service fee per order'],
];

foreach ($defaultPricing as $price) {
    $stmt = $conn->prepare("INSERT IGNORE INTO pricing_config (id, config_key, config_value, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssds', $price['id'], $price['config_key'], $price['config_value'], $price['description']);
    $stmt->execute();
    $stmt->close();
}

// Insert default paper size multipliers
$defaultPaperSizes = [
    ['id' => uniqid('paper_', true), 'paper_size' => 'A4', 'multiplier' => 1.0],
    ['id' => uniqid('paper_', true), 'paper_size' => 'Letter', 'multiplier' => 1.0],
    ['id' => uniqid('paper_', true), 'paper_size' => 'Legal', 'multiplier' => 1.2],
    ['id' => uniqid('paper_', true), 'paper_size' => 'A3', 'multiplier' => 1.5],
    ['id' => uniqid('paper_', true), 'paper_size' => 'A5', 'multiplier' => 0.8],
];

foreach ($defaultPaperSizes as $paper) {
    $stmt = $conn->prepare("INSERT IGNORE INTO paper_size_multipliers (id, paper_size, multiplier) VALUES (?, ?, ?)");
    $stmt->bind_param('ssd', $paper['id'], $paper['paper_size'], $paper['multiplier']);
    $stmt->execute();
    $stmt->close();
}

// Insert default binding costs
$defaultBindings = [
    ['id' => uniqid('bind_', true), 'binding_type' => 'None', 'cost' => 0.0],
    ['id' => uniqid('bind_', true), 'binding_type' => 'Staple', 'cost' => 1.0],
    ['id' => uniqid('bind_', true), 'binding_type' => 'Spiral', 'cost' => 3.0],
    ['id' => uniqid('bind_', true), 'binding_type' => 'Perfect Binding', 'cost' => 5.0],
];

foreach ($defaultBindings as $binding) {
    $stmt = $conn->prepare("INSERT IGNORE INTO binding_costs (id, binding_type, cost) VALUES (?, ?, ?)");
    $stmt->bind_param('ssd', $binding['id'], $binding['binding_type'], $binding['cost']);
    $stmt->execute();
    $stmt->close();
}

// Create default admin user (username: admin, password: admin123)
$defaultAdminId = uniqid('admin_', true);
$defaultAdminUsername = 'admin';
$defaultAdminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$defaultAdminEmail = 'admin@printingservice.com';

$stmt = $conn->prepare("INSERT IGNORE INTO admin_users (id, username, password_hash, email, role) VALUES (?, ?, ?, ?, 'admin')");
$stmt->bind_param('ssss', $defaultAdminId, $defaultAdminUsername, $defaultAdminPassword, $defaultAdminEmail);
$stmt->execute();
$stmt->close();

$conn->close();

echo "Admin tables created successfully! Default admin user: admin / admin123";
?>

