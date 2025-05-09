<?php
session_start();
include('config.php');

if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $query = "SELECT * FROM investments WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $project = $result->fetch_assoc();
            
            // Format dates for HTML date inputs
            if ($project['start_date']) {
                $project['start_date'] = date('Y-m-d', strtotime($project['start_date']));
            }
            if ($project['end_date']) {
                $project['end_date'] = date('Y-m-d', strtotime($project['end_date']));
            }
            
            header('Content-Type: application/json');
            echo json_encode($project);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Project not found']);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid project ID']);
}
?>