<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $audit_id = isset($_POST['audit_id']) && !empty($_POST['audit_id']) ? $_POST['audit_id'] : null;
    $audit_name = $_POST['audit_name'];
    $audit_type = $_POST['audit_type'];
    $assignee = $_POST['assignee'];
    $scope = $_POST['scope'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];
    $checklist = isset($_POST['checklist']) ? implode(',', $_POST['checklist']) : '';
    
    // Generate audit_id if new audit
    if (!$audit_id) {
        // Get the last audit ID
        $query = "SELECT audit_id FROM audits ORDER BY id DESC LIMIT 1";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_id = $row['audit_id'];
            // Extract the numeric part and increment
            $num = intval(substr($last_id, 4)) + 1;
            $new_audit_id = 'AUD-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } else {
            $new_audit_id = 'AUD-001';
        }
        
        // Insert new audit
        $query = "INSERT INTO audits (audit_id, name, type, assignee, scope, date, end_date, description, checklist, status, compliance, issues) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Upcoming', 0, 0)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssssssss', $new_audit_id, $audit_name, $audit_type, $assignee, $scope, $start_date, $end_date, $description, $checklist);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "New audit scheduled successfully!";
        } else {
            $_SESSION['error_message'] = "Error scheduling audit: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        // Update existing audit
        $query = "UPDATE audits SET 
                  name = ?, 
                  type = ?, 
                  assignee = ?, 
                  scope = ?, 
                  date = ?, 
                  end_date = ?, 
                  description = ?, 
                  checklist = ? 
                  WHERE audit_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssssssss', $audit_name, $audit_type, $assignee, $scope, $start_date, $end_date, $description, $checklist, $audit_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Audit updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating audit: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // Redirect back to audits page
    header("Location: audits.php");
    exit();
} else {
    // If not a POST request, redirect to audits page
    header("Location: audits.php");
    exit();
}
?>