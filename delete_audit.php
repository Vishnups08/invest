<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if audit ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $audit_id = $_GET['id'];
    
    // Delete the audit
    $query = "DELETE FROM audits WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $audit_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Audit deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting audit: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error_message'] = "No audit ID provided for deletion.";
}

// Redirect back to audits page
header("Location: audits.php");
exit();
?>