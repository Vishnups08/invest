<?php
session_start();
include('config.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug information
    error_log("Form data received: " . print_r($_POST, true));
    
    try {
        // Generate asset ID (AST-XXX format)
        $countQuery = "SELECT COUNT(*) as count FROM assets";
        $countResult = $conn->query($countQuery);
        
        if (!$countResult) {
            throw new Exception("Error counting assets: " . $conn->error);
        }
        
        $countData = $countResult->fetch_assoc();
        $count = $countData['count'] + 1;
        $asset_id = 'AST-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        
        // Get and sanitize form data
        $name = $conn->real_escape_string($_POST['asset_name']);
        $type = $conn->real_escape_string($_POST['asset_type']);
        $manufacturer = $conn->real_escape_string($_POST['manufacturer'] ?? '');
        $model = $conn->real_escape_string($_POST['model'] ?? '');
        $serial_number = $conn->real_escape_string($_POST['serial_number'] ?? '');
        $department = $conn->real_escape_string($_POST['department']);
        $cost = floatval($_POST['cost']);
        $purchase_date = $conn->real_escape_string($_POST['purchase_date']);
        $warranty_end = !empty($_POST['warranty_end']) ? $conn->real_escape_string($_POST['warranty_end']) : null;
        $status = $conn->real_escape_string($_POST['status']);
        $notes = $conn->real_escape_string($_POST['notes'] ?? '');

        // Use prepared statement for insert
        $stmt = $conn->prepare("INSERT INTO assets(asset_id, name, type, manufacturer, model, serial_number, location, cost, purchase_date, warranty_end, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssssssdssss", 
            $asset_id,
            $name,
            $type,
            $manufacturer,
            $model,
            $serial_number,
            $department,
            $cost,
            $purchase_date,
            $warranty_end,
            $status,
            $notes
        );
        
        if ($stmt->execute()) {
            $_SESSION['asset_success'] = "Asset added successfully!";
            error_log("Asset added successfully with ID: " . $asset_id);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Error in process_asset.php: " . $e->getMessage());
        $_SESSION['asset_error'] = "Error adding asset: " . $e->getMessage();
    }
    
    // Redirect back to assets page
    header("Location: assets.php");
    exit();
} else {
    // If not POST request, redirect to assets page
    $_SESSION['asset_error'] = "Invalid request method";
    header("Location: assets.php");
    exit();
}