<?php
session_start();
include('config.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['cost'])) {
    $id = $_GET['id'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete the asset
        $deleteQuery = "DELETE FROM assets WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error deleting asset: " . $stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['asset_success'] = "Asset deleted successfully!";
        http_response_code(200);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in delete_asset.php: " . $e->getMessage());
        $_SESSION['asset_error'] = $e->getMessage();
        http_response_code(500);
    }
}

if (!isset($_GET['cost'])) {
    header("Location: dashboard.php");
    exit();
}

header("Location: assets.php");
exit();