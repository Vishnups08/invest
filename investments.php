<?php
session_start();
include('config.php');
if (!isset($_SESSION['email'])) { 
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch summary data for cards
try {
    // Total investments for this user
    $totalInvestmentQuery = "SELECT SUM(budget) as total_investment FROM investments WHERE user_id = ?";
    $totalInvestmentStmt = $conn->prepare($totalInvestmentQuery);
    $totalInvestmentStmt->bind_param('i', $user_id);
    $totalInvestmentStmt->execute();
    $totalInvestmentResult = $totalInvestmentStmt->get_result();
    $totalInvestmentData = $totalInvestmentResult->fetch_assoc();
    $totalInvestment = number_format($totalInvestmentData['total_investment'] ?? 0);

    // Calculate ROI for this user
    $roiQuery = "SELECT AVG(roi_percentage) as avg_roi FROM investments WHERE user_id = ? AND roi_percentage IS NOT NULL";
    $roiStmt = $conn->prepare($roiQuery);
    $roiStmt->bind_param('i', $user_id);
    $roiStmt->execute();
    $roiResult = $roiStmt->get_result();
    $roiData = $roiResult->fetch_assoc();
    $avgRoi = number_format($roiData['avg_roi'] ?? 0, 1);

    // Planned investments for this user
    $plannedInvestmentQuery = "SELECT SUM(budget) as planned_investment FROM investments WHERE user_id = ? AND status = 'Planned'";
    $plannedInvestmentStmt = $conn->prepare($plannedInvestmentQuery);
    $plannedInvestmentStmt->bind_param('i', $user_id);
    $plannedInvestmentStmt->execute();
    $plannedInvestmentResult = $plannedInvestmentStmt->get_result();
    $plannedInvestmentData = $plannedInvestmentResult->fetch_assoc();
    $plannedInvestment = number_format($plannedInvestmentData['planned_investment'] ?? 0);

    // Calculate depreciation for this user
    $depreciationQuery = "SELECT SUM(budget) * 0.15 as depreciation FROM investments WHERE user_id = ?";
    $depreciationStmt = $conn->prepare($depreciationQuery);
    $depreciationStmt->bind_param('i', $user_id);
    $depreciationStmt->execute();
    $depreciationResult = $depreciationStmt->get_result();
    $depreciationData = $depreciationResult->fetch_assoc();
    $depreciation = number_format($depreciationData['depreciation'] ?? 0);

    // Category distribution for this user
    $categoryQuery = "SELECT category, SUM(budget) as total FROM investments WHERE user_id = ? GROUP BY category";
    $categoryStmt = $conn->prepare($categoryQuery);
    $categoryStmt->bind_param('i', $user_id);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    $categoryLabels = [];
    $categoryValues = [];
    while ($row = $categoryResult->fetch_assoc()) {
        $categoryLabels[] = $row['category'];
        $categoryValues[] = round($row['total'] / 100000, 2); // Convert to lakhs
    }

    // Department distribution for this user
    $departmentQuery = "SELECT department, SUM(budget) as total FROM investments WHERE user_id = ? GROUP BY department";
    $departmentStmt = $conn->prepare($departmentQuery);
    $departmentStmt->bind_param('i', $user_id);
    $departmentStmt->execute();
    $departmentResult = $departmentStmt->get_result();
    $departmentLabels = [];
    $departmentValues = [];
    while ($row = $departmentResult->fetch_assoc()) {
        $departmentLabels[] = $row['department'];
        $departmentValues[] = round($row['total'] / 100000, 2); // Convert to lakhs
    }

    // Monthly trends for this user
    $monthlyQuery = "SELECT MONTH(start_date) as month, SUM(budget) as total FROM investments WHERE user_id = ? AND YEAR(start_date) = YEAR(CURDATE()) GROUP BY MONTH(start_date)";
    $monthlyStmt = $conn->prepare($monthlyQuery);
    $monthlyStmt->bind_param('i', $user_id);
    $monthlyStmt->execute();
    $monthlyResult = $monthlyStmt->get_result();
    $monthlyData = array_fill(0, 12, 0); // Initialize with zeros
    while ($row = $monthlyResult->fetch_assoc()) {
        $monthlyData[$row['month']-1] = round($row['total'] / 100000, 2); // Convert to lakhs
    }

    // Quarterly trends
    $quarterlyData = [
        array_sum(array_slice($monthlyData, 0, 3)),
        array_sum(array_slice($monthlyData, 3, 3)),
        array_sum(array_slice($monthlyData, 6, 3)),
        array_sum(array_slice($monthlyData, 9, 3))
    ];

    // Yearly trends for this user
    $yearlyQuery = "SELECT YEAR(start_date) as year, SUM(budget) as total FROM investments WHERE user_id = ? GROUP BY YEAR(start_date) ORDER BY year DESC LIMIT 5";
    $yearlyStmt = $conn->prepare($yearlyQuery);
    $yearlyStmt->bind_param('i', $user_id);
    $yearlyStmt->execute();
    $yearlyResult = $yearlyStmt->get_result();
    $yearlyLabels = [];
    $yearlyValues = [];
    while ($row = $yearlyResult->fetch_assoc()) {
        $yearlyLabels[] = $row['year'];
        $yearlyValues[] = round($row['total'] / 100000, 2); // Convert to lakhs
    }
    $yearlyLabels = array_reverse($yearlyLabels);
    $yearlyValues = array_reverse($yearlyValues);

    // Fetch investment projects for table for this user
    $projectsQuery = "SELECT * FROM investments WHERE user_id = ? ORDER BY id DESC";
    $projectsStmt = $conn->prepare($projectsQuery);
    $projectsStmt->bind_param('i', $user_id);
    $projectsStmt->execute();
    $projectsResult = $projectsStmt->get_result();

} catch (Exception $e) {
    // Log the error
    error_log($e->getMessage());
    // Set default values
    $totalInvestment = "0";
    $avgRoi = "0.0";
    $plannedInvestment = "0";
    $depreciation = "0";
    $categoryLabels = [];
    $categoryValues = [];
    $departmentLabels = [];
    $departmentValues = [];
    $monthlyData = array_fill(0, 12, 0);
    $quarterlyData = array_fill(0, 4, 0);
    $yearlyLabels = [];
    $yearlyValues = [];
    $projectsResult = null;
}

// Handle form submission for adding new investment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_investment'])) {
        try {
            // Check if we're editing an existing project
            if (!empty($_POST['project_id'])) {
                // This is an edit operation
                $user_id = $_SESSION['user_id'];
                $id = $_POST['project_id'];
                $name = $_POST['name'];
                $category = $_POST['category'];
                $department = $_POST['department'];
                $budget = $_POST['budget'];
                $spent = $_POST['spent'] ?? 0;
                $roi_percentage = !empty($_POST['roi_percentage']) ? $_POST['roi_percentage'] : null;
                $status = $_POST['status'];
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'] ?? null;
                $description = $_POST['description'] ?? '';
                
                if (!empty($end_date) && preg_match('/^\\d{4}$/', $end_date)) {
                    // If only a year is provided, default to the last day of the year
                    $end_date = $end_date . '-12-31';
                }
                
                // Prepare the SQL statement for update
                $stmt = $conn->prepare("UPDATE investments SET name=?, category=?, department=?, budget=?, spent=?, roi_percentage=?, status=?, start_date=?, end_date=?, description=? WHERE id=?");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("sssddsssssi", $name, $category, $department, $budget, $spent, $roi_percentage, $status, $start_date, $end_date, $description, $id);
                
                if ($stmt->execute()) {
                    $_SESSION['investment_success'] = "Investment project updated successfully!";
                    error_log("Investment project updated successfully with ID: " . $id);
                } else {
                    error_log("MySQL error: " . $stmt->error);
                    throw new Exception("Error updating investment project: " . $stmt->error);
                }
                
                $stmt->close();
                header("Location: investments.php");
                exit();
            } else {
                // This is an add operation (existing code)
                // Get the last project ID from the database
                $getLastIdQuery = "SELECT project_id FROM investments ORDER BY id DESC LIMIT 1";
                $lastIdResult = $conn->query($getLastIdQuery);
                
                if ($lastIdResult && $lastIdResult->num_rows > 0) {
                    $lastProject = $lastIdResult->fetch_assoc();
                    $lastId = intval(substr($lastProject['project_id'], 4)); // Remove 'INV-' prefix and convert to number
                    $newId = $lastId + 1;
                } else {
                    $newId = 1; // If no projects exist, start with 1
                }
                
                // Format the new project ID with leading zeros
                $project_id = 'INV-' . str_pad($newId, 3, '0', STR_PAD_LEFT);
                
                $name = $_POST['name'];
                $category = $_POST['category'];
                $department = $_POST['department'];
                $budget = $_POST['budget'];
                $spent = $_POST['spent'] ?? 0;
                $roi_percentage = !empty($_POST['roi_percentage']) ? $_POST['roi_percentage'] : null;
                $status = $_POST['status'];
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'] ?? null;
                $description = $_POST['description'] ?? '';
                
                if (!empty($end_date) && preg_match('/^\\d{4}$/', $end_date)) {
                    // If only a year is provided, default to the last day of the year
                    $end_date = $end_date . '-12-31';
                }
                
                // Prepare the SQL statement
                $stmt = $conn->prepare("INSERT INTO investments (project_id, name, category, department, budget, spent, roi_percentage, status, start_date, end_date, description, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                error_log('Insert user_id: ' . $user_id);
                
                $stmt->bind_param("ssssddsssssi", $project_id, $name, $category, $department, $budget, $spent, $roi_percentage, $status, $start_date, $end_date, $description, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['investment_success'] = "Investment project added successfully with ID: " . $project_id;
                    error_log("Investment project added successfully with ID: " . $project_id);
                } else {
                    error_log("MySQL error: " . $stmt->error);
                    throw new Exception("Error adding investment project: " . $stmt->error);
                }
                
                $stmt->close();
                header("Location: investments.php");
                exit();
            }
        } catch (Exception $e) {
            error_log("Error in investment submission: " . $e->getMessage());
            $_SESSION['investment_error'] = $e->getMessage();
            header("Location: investments.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investments</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-laptop-code"></i> Asset & invest Manager</h2>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
            <li class="active"><a href="investments.php"><i class="fas fa-chart-line"></i> Investments</a></li>
            <li><a href="audits.html"><i class="fas fa-clipboard-check"></i> Audits</a></li>
            <li><a href="reports.html"><i class="fas fa-file-alt"></i> Reports</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="settings.html"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="toggle-btn">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="search-bar">
                    <input type="text" placeholder="Search investments...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="user-profile">
                    <span class="notification-icon"><i class="fas fa-bell"></i></span>
                    <div class="profile-img">
                        <img src="profile.jpeg" alt="User Profile">
                    </div>
                    <span class="user-name"><?php echo $_SESSION['full_name']; ?></span>
                </div>
            </div>

            <!-- Investments Content -->
            <div class="dashboard-content">
                <div class="page-header">
                    <h1>Investment Management</h1>
                    <p>Track and analyze your IT investments and ROI</p>
                </div>

                <!-- Investment Summary Cards -->
                <div class="summary-cards">
                    <div class="card">
                        <div class="card-icon blue">
                            <i class= "fa-solid fa-sack-dollar"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Investments</h3>
                            <p class="value">₹<?php echo $totalInvestment; ?></p>
                            <p class="change positive"><i class="fas fa-arrow-up"></i> 8.5% since last quarter</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon green">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-info">
                            <h3>ROI</h3>
                            <p class="value"><?php echo $avgRoi; ?>%</p>
                            <p class="change positive"><i class="fas fa-arrow-up"></i> 2.3% since last quarter</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon purple">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="card-info">
                            <h3>Planned Investments</h3>
                            <p class="value">₹<?php echo $plannedInvestment; ?></p>
                            <p class="change positive"><i class="fas fa-arrow-up"></i> 12.4% from last plan</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon orange">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="card-info">
                            <h3>Depreciation</h3>
                            <p class="value">₹<?php echo $depreciation; ?></p>
                            <p class="change negative"><i class="fas fa-arrow-up"></i> 5.2% since last quarter</p>
                        </div>
                    </div>
                </div>

                <!-- Investment Charts Section -->
                <div class="charts-container">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Investment Trends</h3>
                            <div class="chart-actions">
                                <button class="chart-period-btn active" data-period="monthly">Monthly</button>
                                <button class="chart-period-btn" data-period="quarterly">Quarterly</button>
                                <button class="chart-period-btn" data-period="yearly">Yearly</button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="investmentTrendsChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Investment Distribution</h3>
                            <div class="chart-actions">
                                <button class="chart-type-btn active" data-type="category">By Category</button>
                                <button class="chart-type-btn" data-type="department">By Department</button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="investmentDistributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Investment Projects Section -->
                <div class="table-section">
                    <div class="table-header">
                        <h2>Investment Projects</h2>
                        <div class="table-actions">
                            <button class="add-btn" id="add-investment-btn"><i class="fas fa-plus"></i> Add Project</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="investments-table">
                            <thead>
                                <tr>
                                    <th>Project ID</th>
                                    <th>Project Name</th>
                                    <th>Category</th>
                                    <th>Department</th>
                                    <th>Budget</th>
                                    <th>Spent</th>
                                    <th>ROI</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($projectsResult && $projectsResult->num_rows > 0): ?>
                                <?php while($project = $projectsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $project['project_id']; ?></td>
                                    <td><?php echo $project['name']; ?></td>
                                    <td><?php echo $project['category']; ?></td>
                                    <td><?php echo $project['department']; ?></td>
                                    <td>₹<?php echo number_format($project['budget']); ?></td>
                                    <td>₹<?php echo number_format($project['spent']); ?></td>
                                    <td><?php echo $project['roi_percentage'] ? $project['roi_percentage'] . '%' : 'N/A'; ?></td>
                                    <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $project['status'])); ?>"><?php echo $project['status']; ?></span></td>
                                    <td>
                                        <button class="action-btn edit" title="Edit" data-id="<?php echo $project['id']; ?>"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete" title="Delete" data-id="<?php echo $project['id']; ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="9" class="no-data">No investment projects found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Display success/error messages -->
    <?php if(isset($_SESSION['investment_success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['investment_success']; ?>
        <?php unset($_SESSION['investment_success']); ?>
    </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['investment_error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['investment_error']; ?>
        <?php unset($_SESSION['investment_error']); ?>
    </div>
    <?php endif; ?>

    <!-- Investment Modal -->
    <div class="modal" id="investment-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Investment Project</h2>
                <button class="close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="investment-form" method="POST" action="save_investment.php" onsubmit="return validateInvestmentForm()">
                    <input type="hidden" id="project-id" name="project_id">
                    <input type="hidden" name="add_investment" value="1"> <!-- Add this line -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project-name">Project Name</label>
                            <input type="text" id="project-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="project-category">Category</label>
                            <select id="project-category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Software">Software</option>
                                <option value="Cloud Services">Cloud Services</option>
                                <option value="Network">Network</option>
                                <option value="Security">Security</option>
                                <option value="Services">Services</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project-department">Department</label>
                            <select id="project-department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="project-budget">Budget (₹)</label>
                            <input type="number" id="project-budget" name="budget" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project-start-date">Start Date</label>
                            <input type="date" id="project-start-date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="project-end-date">Expected End Date</label>
                            <input type="date" id="project-end-date" name="end_date">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project-roi">Expected ROI (%)</label>
                            <input type="number" id="project-roi" name="roi_percentage" step="0.1" min="0">
                        </div>
                        <div class="form-group">
                            <label for="project-status">Status</label>
                            <select id="project-status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Planned">Planned</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="On Hold">On Hold</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project-spent">Amount Spent (₹)</label>
                            <input type="number" id="project-spent" name="spent" step="0.01" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label for="project-description">Description</label>
                            <textarea id="project-description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" name="add_investment" class="submit-btn">Add Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function validateInvestmentForm() {
        const name = document.getElementById('project-name').value;
        const category = document.getElementById('project-category').value;
        const department = document.getElementById('project-department').value;
        const budget = document.getElementById('project-budget').value;
        const startDate = document.getElementById('project-start-date').value;
        const status = document.getElementById('project-status').value;
        const endDate = document.getElementById('project-end-date').value;
        const roi = document.getElementById('project-roi').value;

        if (!name || !category || !department || !budget || !startDate || !status) {
            alert('Please fill in all required fields');
            return false;
        }

        if (budget <= 0) {
            alert('Budget must be greater than 0');
            return false;
        }

        if (endDate && new Date(endDate) < new Date(startDate)) {
            alert('End date cannot be before start date');
            return false;
        }

        if (roi && (roi < 0 || roi > 100)) {
            alert('ROI percentage must be between 0 and 100');
            return false;
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Investment Distribution Chart
        const distributionCtx = document.getElementById('investmentDistributionChart').getContext('2d');
        
        // Initial data for category distribution
        const categoryData = {
            labels: <?php echo json_encode($categoryLabels); ?>,
            datasets: [{
                label: 'Investment by Category',
                data: <?php echo json_encode($categoryValues); ?>,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b',
                    '#6f42c1'
                ],
                borderWidth: 1
            }]
        };
        
        // Department distribution data
        const departmentData = {
            labels: <?php echo json_encode($departmentLabels); ?>,
            datasets: [{
                label: 'Investment by Department',
                data: <?php echo json_encode($departmentValues); ?>,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ],
                borderWidth: 1
            }]
        };
        
        // Create the initial chart with category data
        let distributionChart = new Chart(distributionCtx, {
            type: 'pie',
            data: categoryData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ₹${value} Lakhs (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Toggle between category and department views
        const chartTypeButtons = document.querySelectorAll('.chart-type-btn');
        chartTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                chartTypeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Update chart data based on selected type
                const type = this.getAttribute('data-type');
                if (type === 'category') {
                    distributionChart.data = categoryData;
                } else if (type === 'department') {
                    distributionChart.data = departmentData;
                }
                
                distributionChart.update();
            });
        });
        
        // Investment Trends Chart
        const trendsCtx = document.getElementById('investmentTrendsChart').getContext('2d');
        
        // Monthly data
        const monthlyData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Investments (₹ Lakhs)',
                data: <?php echo !empty($monthlyData) && array_sum($monthlyData) > 0 ? json_encode($monthlyData) : json_encode([15, 25, 35, 30, 22, 28, 32, 38, 42, 45, 48, 50]); ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        };
        
        // Quarterly data
        const quarterlyData = {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [{
                label: 'Investments (₹ Lakhs)',
                data: <?php echo !empty($quarterlyData) && array_sum($quarterlyData) > 0 ? json_encode($quarterlyData) : json_encode([75, 80, 112, 143]); ?>,
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        };
        
        // Yearly data
        const yearlyData = {
            labels: <?php echo !empty($yearlyLabels) ? json_encode($yearlyLabels) : json_encode(['2019', '2020', '2021', '2022', '2023']); ?>,
            datasets: [{
                label: 'Investments (₹ Lakhs)',
                data: <?php echo !empty($yearlyValues) && array_sum($yearlyValues) > 0 ? json_encode($yearlyValues) : json_encode([85, 120, 145, 170, 189]); ?>,
                borderColor: '#36b9cc',
                backgroundColor: 'rgba(54, 185, 204, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        };
        
        // Create the trends chart with initial monthly data
        let trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: monthlyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value + ' L';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Investments: ₹' + context.raw + ' Lakhs';
                            }
                        }
                    }
                }
            }
        });
        
        // Toggle between different period views
        const chartPeriodButtons = document.querySelectorAll('.chart-period-btn');
        chartPeriodButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                chartPeriodButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Update chart data based on selected period
                const period = this.getAttribute('data-period');
                if (period === 'monthly') {
                    trendsChart.data = monthlyData;
                } else if (period === 'quarterly') {
                    trendsChart.data = quarterlyData;
                } else if (period === 'yearly') {
                    trendsChart.data = yearlyData;
                }
                
                trendsChart.update();
            });
        });
        
        // Modal functionality
        const modal = document.getElementById('investment-modal');
        const addInvestmentBtn = document.getElementById('add-investment-btn');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelBtn = document.querySelector('.cancel-btn');
        
        // Open modal
        if (addInvestmentBtn) {
            addInvestmentBtn.addEventListener('click', function() {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
        
        // Close modal functions
        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('investment-form').reset();
        }
        
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeModal);
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Edit button functionality
        const editButtons = document.querySelectorAll('.action-btn.edit');
        editButtons.forEach(button => {
            button.addEventListener('click', async function() {
                const projectId = this.getAttribute('data-id');
                try {
                    const response = await fetch(`get_investment.php?id=${projectId}`);
                    if (response.ok) {
                        const project = await response.json();
                        
                        // Update modal title and button text for edit mode
                        document.querySelector('.modal-header h2').textContent = 'Edit Investment Project';
                        const submitBtn = document.querySelector('.submit-btn');
                        submitBtn.textContent = 'Update Project';
                        submitBtn.name = 'add_investment'; // Add this line to ensure the name attribute is set
                        
                        // Fill the form with project data
                        document.getElementById('project-id').value = project.id;
                        document.getElementById('project-name').value = project.name;
                        document.getElementById('project-category').value = project.category;
                        document.getElementById('project-department').value = project.department;
                        document.getElementById('project-budget').value = project.budget;
                        document.getElementById('project-spent').value = project.spent || 0;
                        document.getElementById('project-start-date').value = project.start_date;
                        document.getElementById('project-end-date').value = project.end_date || '';
                        document.getElementById('project-roi').value = project.roi_percentage || '';
                        document.getElementById('project-status').value = project.status;
                        document.getElementById('project-description').value = project.description || '';
                        
                        // Show modal
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                } catch (error) {
                    console.error('Error fetching project data:', error);
                }
            });
        });
        
        // Delete button functionality
        const deleteButtons = document.querySelectorAll('.action-btn.delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', async function() {
                if (confirm('Are you sure you want to delete this investment project?')) {
                    const projectId = this.getAttribute('data-id');
                    try {
                        const response = await fetch(`delete_investment.php?id=${projectId}`, {
                            method: 'GET'
                        });
                        
                        if (response.ok) {
                            // Remove row from table
                            this.closest('tr').remove();
                            alert('Investment project deleted successfully!');
                        } else {
                            alert('Failed to delete investment project.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the project.');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>