<?php
session_start();
include('config.php');

if (!isset($_SESSION['email'])) { 
    header("Location: login.php");
    exit();
}

// Check if report type is set
if (!isset($_POST['report_type']) && !isset($_POST['report_template'])) {
    echo "Error: No report type specified";
    exit();
}

// Get report type
$reportType = isset($_POST['report_type']) ? $_POST['report_type'] : $_POST['report_template'];
$format = isset($_POST['format']) ? $_POST['format'] : 'csv'; // Default to CSV

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $reportType) . '_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Generate report based on type
switch ($reportType) {
    case 'Asset Inventory':
        generateAssetInventoryReport($output);
        break;
    case 'Financial Summary':
        generateFinancialSummaryReport($output);
        break;
    case 'Audit History':
        generateAuditHistoryReport($output);
        break;
    default:
        generateCustomReport($output);
        break;
}

// Close the output stream
fclose($output);
exit();

// Function to generate Asset Inventory Report
function generateAssetInventoryReport($output) {
    global $conn;
    
    // CSV Headers
    $headers = array('Asset ID', 'Name', 'Type', 'Status', 'Location', 'Purchase Date', 'Purchase Cost', 'Assigned To');
    fputcsv($output, $headers);
    
    try {
        // Query to get asset data
        $query = "SELECT * FROM assets ORDER BY id ASC";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $csvRow = array(
                    $row['asset_id'],
                    $row['name'],
                    $row['type'],
                    $row['status'],
                    $row['location'],
                    $row['purchase_date'],
                    $row['purchase_cost'],
                    $row['assigned_to']
                );
                fputcsv($output, $csvRow);
            }
        } else {
            // Sample data if no records found
            for ($i = 1; $i <= 10; $i++) {
                $csvRow = array(
                    'AST-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'Sample Asset ' . $i,
                    'Hardware',
                    'Active',
                    'Main Office',
                    '2023-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    $i * 1000,
                    'Employee ' . $i
                );
                fputcsv($output, $csvRow);
            }
        }
    } catch (Exception $e) {
        // Output error as a row in the CSV file
        fputcsv($output, array('Error: ' . $e->getMessage()));
    }
}

// Function to generate Financial Summary Report
function generateFinancialSummaryReport($output) {
    global $conn;
    
    // CSV Headers
    $headers = array('Investment ID', 'Project Name', 'Category', 'Department', 'Budget', 'Start Date', 'End Date', 'ROI (%)', 'Status');
    fputcsv($output, $headers);
    
    try {
        // Query to get investment data
        $query = "SELECT * FROM investments ORDER BY id ASC";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $csvRow = array(
                    $row['investment_id'],
                    $row['project_name'],
                    $row['category'],
                    $row['department'],
                    $row['budget'],
                    $row['start_date'],
                    $row['end_date'],
                    $row['roi'],
                    $row['status']
                );
                fputcsv($output, $csvRow);
            }
        } else {
            // Sample data if no records found
            for ($i = 1; $i <= 10; $i++) {
                $csvRow = array(
                    'INV-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'Sample Project ' . $i,
                    'Software',
                    'IT',
                    $i * 10000,
                    '2023-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    '2023-06-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    $i * 5,
                    'In Progress'
                );
                fputcsv($output, $csvRow);
            }
        }
    } catch (Exception $e) {
        // Output error as a row in the CSV file
        fputcsv($output, array('Error: ' . $e->getMessage()));
    }
}

// Function to generate Audit History Report
function generateAuditHistoryReport($output) {
    global $conn;
    
    // CSV Headers
    $headers = array('Audit ID', 'Type', 'Name', 'Assignee', 'Date', 'Compliance (%)', 'Status');
    fputcsv($output, $headers);
    
    try {
        // Query to get audit data
        $query = "SELECT * FROM audits ORDER BY id ASC";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $csvRow = array(
                    $row['audit_id'],
                    $row['type'],
                    $row['name'],
                    $row['assignee'],
                    $row['date'],
                    $row['compliance'],
                    $row['status']
                );
                fputcsv($output, $csvRow);
            }
        } else {
            // Sample data if no records found
            for ($i = 1; $i <= 10; $i++) {
                $csvRow = array(
                    'AUD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    ($i % 2 == 0 ? 'Software' : 'Hardware'),
                    'Sample Audit ' . $i,
                    'Auditor ' . $i,
                    '2023-' . str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT) . '-15',
                    85 + ($i % 15),
                    ($i % 3 == 0 ? 'Completed' : ($i % 3 == 1 ? 'In Progress' : 'Upcoming'))
                );
                fputcsv($output, $csvRow);
            }
        }
    } catch (Exception $e) {
        // Output error as a row in the CSV file
        fputcsv($output, array('Error: ' . $e->getMessage()));
    }
}

// Function to generate Custom Report
function generateCustomReport($output) {
    global $conn;
    
    // Get form data
    $dataSource = isset($_POST['data_source']) ? $_POST['data_source'] : 'assets';
    $fields = isset($_POST['fields']) ? $_POST['fields'] : array('id', 'name', 'type', 'status');
    
    // Determine table and fields based on data source
    $table = '';
    $tableFields = array();
    
    switch ($dataSource) {
        case 'assets':
            $table = 'assets';
            $tableFields = array(
                'id' => 'ID',
                'asset_id' => 'Asset ID',
                'name' => 'Name',
                'type' => 'Type',
                'status' => 'Status',
                'location' => 'Location',
                'purchase_date' => 'Purchase Date',
                'purchase_cost' => 'Purchase Cost',
                'assigned_to' => 'Assigned To'
            );
            break;
        case 'investments':
            $table = 'investments';
            $tableFields = array(
                'id' => 'ID',
                'investment_id' => 'Investment ID',
                'project_name' => 'Project Name',
                'category' => 'Category',
                'department' => 'Department',
                'budget' => 'Budget',
                'start_date' => 'Start Date',
                'end_date' => 'End Date',
                'roi' => 'ROI (%)',
                'status' => 'Status'
            );
            break;
        case 'audits':
            $table = 'audits';
            $tableFields = array(
                'id' => 'ID',
                'audit_id' => 'Audit ID',
                'type' => 'Type',
                'name' => 'Name',
                'assignee' => 'Assignee',
                'date' => 'Date',
                'compliance' => 'Compliance (%)',
                'status' => 'Status'
            );
            break;
        default:
            $table = 'assets';
            $tableFields = array(
                'id' => 'ID',
                'asset_id' => 'Asset ID',
                'name' => 'Name',
                'type' => 'Type',
                'status' => 'Status'
            );
            break;
    }
    
    // CSV Headers - only include selected fields
    $headers = array();
    foreach ($tableFields as $field => $label) {
        if (in_array($field, $fields) || empty($fields)) {
            $headers[] = $label;
        }
    }
    fputcsv($output, $headers);
    
    try {
        // Query to get data
        $query = "SELECT * FROM $table ORDER BY id ASC";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $csvRow = array();
                foreach ($tableFields as $field => $label) {
                    if (in_array($field, $fields) || empty($fields)) {
                        $csvRow[] = isset($row[$field]) ? $row[$field] : '';
                    }
                }
                fputcsv($output, $csvRow);
            }
        } else {
            // Sample data if no records found
            for ($i = 1; $i <= 10; $i++) {
                $csvRow = array();
                foreach ($tableFields as $field => $label) {
                    if (in_array($field, $fields) || empty($fields)) {
                        $csvRow[] = 'Sample ' . $label . ' ' . $i;
                    }
                }
                fputcsv($output, $csvRow);
            }
        }
    } catch (Exception $e) {
        // Output error as a row in the CSV file
        fputcsv($output, array('Error: ' . $e->getMessage()));
    }
}
?>