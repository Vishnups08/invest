<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch audit data from database
$audits = array();
try {
    $query = "SELECT * FROM audits ORDER BY date DESC LIMIT 10";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $audits[] = $row;
        }
    }
} catch (Exception $e) {
    // Handle database error
    $error_message = "Database error: " . $e->getMessage();
}

// Count total audits
$total_audits = 0;
try {
    $query = "SELECT COUNT(*) as total FROM audits";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $total_audits = $row['total'];
    }
} catch (Exception $e) {
    // Handle error
}

// Count upcoming audits
$upcoming_audits = 0;
try {
    $query = "SELECT COUNT(*) as total FROM audits WHERE status = 'Upcoming'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $upcoming_audits = $row['total'];
    }
} catch (Exception $e) {
    // Handle error
}

// Get average compliance rate
$compliance_rate = 0;
try {
    $query = "SELECT AVG(compliance) as avg_compliance FROM audits WHERE status = 'Completed'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $compliance_rate = round($row['avg_compliance'], 1);
    }
} catch (Exception $e) {
    // Handle error
}

// Count issues found
$issues_found = 0;
try {
    $query = "SELECT SUM(issues) as total_issues FROM audits WHERE status = 'Completed'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $issues_found = $row['total_issues'] ?: 0;
    }
} catch (Exception $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Asset Management - Audits</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <li><a href="investments.php"><i class="fas fa-chart-line"></i> Investments</a></li>
            <li class="active"><a href="audits.php"><i class="fas fa-clipboard-check"></i> Audits</a></li>
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
                    <input type="text" placeholder="Search audits...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="user-profile">
                    <span class="notification-icon"><i class="fas fa-bell"></i></span>
                    <div class="profile-img">
                        <img src="profile.jpeg" alt="User Profile">
                    </div>
                    <span class="user-name"><?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'User'; ?></span>
                </div>
            </div>

            <!-- Audits Content -->
            <div class="dashboard-content">
                <div class="page-header">
                    <h1>Audit Management</h1>
                    <p>Schedule, track, and manage compliance audits for your IT assets</p>
                </div>

                <!-- Audit Summary Cards -->
                <div class="summary-cards">
                    <div class="card">
                        <div class="card-icon blue">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Audits</h3>
                            <p class="value"><?php echo $total_audits; ?></p>
                            <p class="change positive"><i class="fas fa-arrow-up"></i> 6 more than last quarter</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-info">
                            <h3>Compliance Rate</h3>
                            <p class="value"><?php echo $compliance_rate; ?>%</p>
                            <p class="change positive"><i class="fas fa-arrow-up"></i> 3.2% since last audit</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon orange">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="card-info">
                            <h3>Issues Found</h3>
                            <p class="value"><?php echo $issues_found; ?></p>
                            <p class="change negative"><i class="fas fa-arrow-up"></i> 3 more than last audit</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon purple">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-info">
                            <h3>Upcoming Audits</h3>
                            <p class="value"><?php echo $upcoming_audits; ?></p>
                            <p class="change neutral">Next in 7 days</p>
                        </div>
                    </div>
                </div>

                <!-- Audit Timeline Section with Enhanced UI -->
                <div class="audit-timeline-section">
                    <div class="section-header">
                        <h2><i class="fas fa-history"></i> Audit Timeline</h2>
                        <div class="timeline-filters">
                            <button class="timeline-filter-btn active" data-filter="all">
                                <i class="fas fa-list-ul"></i> All
                            </button>
                            <button class="timeline-filter-btn" data-filter="completed">
                                <i class="fas fa-check-circle"></i> Completed
                            </button>
                            <button class="timeline-filter-btn" data-filter="in-progress">
                                <i class="fas fa-spinner fa-pulse"></i> In Progress
                            </button>
                            <button class="timeline-filter-btn" data-filter="upcoming">
                                <i class="fas fa-calendar-day"></i> Upcoming
                            </button>
                        </div>
                    </div>
                    <div class="audit-timeline">
                        <?php
                        // Fetch timeline audits
                        try {
                            $query = "SELECT * FROM audits ORDER BY date DESC LIMIT 4";
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $date = new DateTime($row['date']);
                                    $status_class = strtolower(str_replace(' ', '-', $row['status']));
                                    ?>
                                    <div class="timeline-item <?php echo $status_class; ?>">
                                        <div class="timeline-date">
                                            <span class="date"><?php echo $date->format('d'); ?></span>
                                            <span class="month"><?php echo $date->format('M'); ?></span>
                                            <span class="year"><?php echo $date->format('Y'); ?></span>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                                <span class="badge <?php echo $status_class == 'completed' ? 'success' : ($status_class == 'in-progress' ? 'primary' : 'purple'); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                            </div>
                                            <p class="timeline-assignee"><i class="fas fa-user-check"></i> <?php echo $row['status'] == 'Completed' ? 'Completed by: ' : 'Assigned to: '; ?><?php echo htmlspecialchars($row['assignee']); ?></p>
                                            <div class="timeline-details">
                                                <?php if ($row['status'] == 'Completed') { ?>
                                                    <div class="timeline-metric success">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span><?php echo $row['compliance']; ?>% Compliance</span>
                                                    </div>
                                                    <div class="timeline-metric warning">
                                                        <i class="fas fa-exclamation-circle"></i>
                                                        <span><?php echo $row['issues']; ?> Issues</span>
                                                    </div>
                                                <?php } elseif ($row['status'] == 'In Progress') { ?>
                                                    <div class="timeline-metric primary">
                                                        <i class="fas fa-clock"></i>
                                                        <span>3 days remaining</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="timeline-metric purple">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <span>Starts in 14 days</span>
                                                    </div>
                                                <?php } ?>
                                                <button class="view-report-btn" data-id="<?php echo $row['id']; ?>"><i class="fas fa-file-alt"></i> View Report</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                // Display sample data if no records found
                                ?>
                                <div class="timeline-item completed">
                                    <div class="timeline-date">
                                        <span class="date">15</span>
                                        <span class="month">Mar</span>
                                        <span class="year">2023</span>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h3>Software License Audit</h3>
                                            <span class="badge success">Completed</span>
                                        </div>
                                        <p class="timeline-assignee"><i class="fas fa-user-check"></i> Completed by: Rahul Sharma</p>
                                        <div class="timeline-details">
                                            <div class="timeline-metric success">
                                                <i class="fas fa-check-circle"></i>
                                                <span>98% Compliance</span>
                                            </div>
                                            <div class="timeline-metric warning">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span>2 Issues</span>
                                            </div>
                                            <button class="view-report-btn"><i class="fas fa-file-alt"></i> View Report</button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } catch (Exception $e) {
                            echo '<p class="error-message">Error loading timeline: ' . $e->getMessage() . '</p>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Enhanced Audit Records Table -->
                <div class="table-section">
                    <div class="table-header">
                        <div class="header-left">
                            <h2><i class="fas fa-clipboard-list"></i> Audit Records</h2>
                            <span class="records-count">Showing <?php echo count($audits); ?> of <?php echo $total_audits; ?> records</span>
                        </div>
                        <div class="table-actions">
                            <button class="add-btn" id="add-audit-btn"><i class="fas fa-plus"></i> Schedule Audit</button>
                        </div>
                    </div>
                    
                    <!-- Enhanced Table with Visual Improvements -->
                    <div class="table-container">
                        <table class="audits-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-tag"></i> Type</th>
                                    <th><i class="fas fa-clipboard-check"></i> Name</th>
                                    <th><i class="fas fa-user"></i> Assignee</th>
                                    <th><i class="fas fa-calendar-alt"></i> Date</th>
                                    <th><i class="fas fa-chart-pie"></i> Compliance</th>
                                    <th><i class="fas fa-info-circle"></i> Status</th>
                                    <th><i class="fas fa-cog"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($audits)) {
                                    foreach ($audits as $audit) {
                                        $status_class = strtolower(str_replace(' ', '-', $audit['status']));
                                        $progress_class = $status_class == 'completed' ? '' : ($status_class == 'in-progress' ? 'in-progress' : 'upcoming');
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($audit['audit_id']); ?></td>
                                            <td><?php echo htmlspecialchars($audit['type']); ?></td>
                                            <td><?php echo htmlspecialchars($audit['name']); ?></td>
                                            <td><?php echo htmlspecialchars($audit['assignee']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($audit['date'])); ?></td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress <?php echo $progress_class; ?>" style="width: <?php echo $audit['compliance']; ?>%"></div>
                                                    <span><?php echo $audit['compliance']; ?>%</span>
                                                </div>
                                            </td>
                                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($audit['status']); ?></span></td>
                                            <td>
                                                <button class="action-btn view" title="View Details" data-id="<?php echo $audit['id']; ?>"><i class="fas fa-eye"></i></button>
                                                <?php if ($audit['status'] == 'Completed') { ?>
                                                    <button class="action-btn report" title="View Report" data-id="<?php echo $audit['id']; ?>"><i class="fas fa-file-alt"></i></button>
                                                <?php } else { ?>
                                                    <button class="action-btn edit" title="Edit" data-id="<?php echo $audit['id']; ?>"><i class="fas fa-edit"></i></button>
                                                    <?php if ($audit['status'] == 'Upcoming') { ?>
                                                        <button class="action-btn delete" title="Delete" data-id="<?php echo $audit['id']; ?>"><i class="fas fa-trash"></i></button>
                                                    <?php } ?>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    // Sample data if no records found
                                    ?>
                                    <tr>
                                        <td>AUD-001</td>
                                        <td>Software</td>
                                        <td>Software License Audit</td>
                                        <td>Rahul Sharma</td>
                                        <td>15 Mar 2023</td>
                                        <td>
                                            <div class="progress-bar">
                                                <div class="progress" style="width: 98%"></div>
                                                <span>98%</span>
                                            </div>
                                        </td>
                                        <td><span class="status-badge completed">Completed</span></td>
                                        <td>
                                            <button class="action-btn view" title="View Details"><i class="fas fa-eye"></i></button>
                                            <button class="action-btn report" title="View Report"><i class="fas fa-file-alt"></i></button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Enhanced Pagination -->
                    <div class="pagination">
                        <div class="pagination-controls">
                            <button class="page-btn disabled" title="Previous Page"><i class="fas fa-chevron-left"></i></button>
                            <button class="page-btn active">1</button>
                            <button class="page-btn">2</button>
                            <button class="page-btn">3</button>
                            <button class="page-btn">4</button>
                            <button class="page-btn">5</button>
                            <button class="page-btn" title="Next Page"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Audit Modal -->
    <div class="modal" id="audit-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Schedule New Audit</h2>
                <button class="close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="audit-form" action="save_audit.php" method="post">
                    <input type="hidden" id="audit-id" name="audit_id" value="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="audit-name">Audit Name</label>
                            <input type="text" id="audit-name" name="audit_name" required>
                        </div>
                        <div class="form-group">
                            <label for="audit-type-select">Audit Type</label>
                            <select id="audit-type-select" name="audit_type" required>
                                <option value="">Select Type</option>
                                <option value="Software">Software License</option>
                                <option value="Hardware">Hardware Inventory</option>
                                <option value="Security">Security Compliance</option>
                                <option value="Network">Network Infrastructure</option>
                                <option value="Data">Data Protection</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="audit-assignee-select">Assignee</label>
                            <select id="audit-assignee-select" name="assignee" required>
                                <option value="">Select Assignee</option>
                                <?php
                                // Fetch assignees from database
                                try {
                                    $query = "SELECT id, name FROM users WHERE role = 'auditor' OR role = 'admin'";
                                    $result = $conn->query($query);
                                    
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                        }
                                    } else {
                                        // Sample options if no records found
                                        echo '<option value="Rahul Sharma">Rahul Sharma</option>';
                                        echo '<option value="Priya Patel">Priya Patel</option>';
                                        echo '<option value="Vikram Singh">Vikram Singh</option>';
                                        echo '<option value="Ananya Desai">Ananya Desai</option>';
                                    }
                                } catch (Exception $e) {
                                    // Sample options if error
                                    echo '<option value="Rahul Sharma">Rahul Sharma</option>';
                                    echo '<option value="Priya Patel">Priya Patel</option>';
                                    echo '<option value="Vikram Singh">Vikram Singh</option>';
                                    echo '<option value="Ananya Desai">Ananya Desai</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="audit-scope">Scope</label>
                            <select id="audit-scope" name="scope" required>
                                <option value="">Select Scope</option>
                                <option value="Full">Full Organization</option>
                                <option value="Department">Specific Department</option>
                                <option value="Asset Group">Asset Group</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="audit-start-date">Start Date</label>
                            <input type="date" id="audit-start-date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="audit-end-date">End Date</label>
                            <input type="date" id="audit-end-date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="audit-description">Description & Objectives</label>
                        <textarea id="audit-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="audit-checklist">Audit Checklist</label>
                        <div class="checklist-items">
                            <div class="checklist-item">
                                <input type="checkbox" id="check1" name="checklist[]" value="tag_verification" checked>
                                <label for="check1">Verify all assets are properly tagged</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="check2" name="checklist[]" value="license_verification" checked>
                                <label for="check2">Confirm software licenses match installations</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="check3" name="checklist[]" value="security_compliance" checked>
                                <label for="check3">Check security compliance on all devices</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="check4" name="checklist[]" value="location_verification">
                                <label for="check4">Verify asset location matches records</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="check5" name="checklist[]" value="asset_accounting">
                                <label for="check5">Confirm all assets are accounted for</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" class="submit-btn">Schedule Audit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="audits.js"></script>
</body>
</html>