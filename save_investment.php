<?php
session_start();
include('config.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if it's an update or new project
        $isUpdate = isset($_POST['project_id']) && !empty($_POST['project_id']);

// Prepare the data
$name = $_POST['name'];
$category = $_POST['category'];
$department = $_POST['department'];
$budget = floatval($_POST['budget']);
$current_value = isset($_POST['current_value']) ? floatval($_POST['current_value']) : 0.0;
$startDate = $_POST['start_date'];
$endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
$roiPercentage = isset($_POST['roi_percentage']) && $_POST['roi_percentage'] !== '' ? floatval($_POST['roi_percentage']) : null;
$status = $_POST['status'];
$spent = $_POST['spent']; // Default for new projects
$user_id = intval($_SESSION['user_id']);

if ($isUpdate) {
    // Update existing project
    $id = intval($_POST['project_id']);

    $query = "UPDATE investments SET 
                name = ?, 
                category = ?, 
                department = ?, 
                budget = ?, 
                spent = ?, 
                current_value = ?, 
                roi_percentage = ?, 
                start_date = ?, 
                end_date = ?, 
                status = ?, 
                user_id = ?
              WHERE id = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "sssdddssssii",
        $name,
        $category,
        $department,
        $budget,
        $spent,
        $current_value,
        $roiPercentage,
        $startDate,
        $endDate,
        $status,
        $user_id,
        $id
    );

    if ($stmt->execute()) {
        $_SESSION['investment_success'] = "Investment project updated successfully!";
    } else {
        throw new Exception("Error updating investment project: " . $stmt->error);
    }

    $stmt->close();

} else {
    // Generate a new project ID
    $projectIdQuery = "SELECT MAX(CAST(SUBSTRING(project_id, 5) AS UNSIGNED)) as max_id FROM investments";
    $result = $conn->query($projectIdQuery);
    $row = $result->fetch_assoc();
    $nextId = ($row['max_id'] ?? 0) + 1;
    $projectId = 'INV-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

    $query = "INSERT INTO investments 
                (project_id, name, category, department, budget, spent, current_value, roi_percentage, start_date, end_date, status, user_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssddddsssi",
        $projectId,
        $name,
        $category,
        $department,
        $budget,
        $spent,
        $current_value,
        $roiPercentage,
        $startDate,
        $endDate,
        $status,
        $user_id
    );

    if ($stmt->execute()) {
        $_SESSION['investment_success'] = "Investment project added successfully!";
    } else {
        throw new Exception("Error adding investment project: " . $stmt->error);
    }

    $stmt->close();
}

        
        // Redirect back to investments page
        header("Location: investments.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['investment_error'] = $e->getMessage();
        header("Location: investments.php");
        exit();
    }
} else {
    // If not a POST request, redirect to investments page
    header("Location: investments.php");
    exit();
}
?>
