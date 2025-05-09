<?php
session_start();
include('config.php');
if (!isset($_SESSION['email'])) { 
    header("Location: login.php");
    exit();
}

error_log('Current user_id: ' . $_SESSION['user_id']);
error_log('Dashboard user_id: ' . $_SESSION['user_id']);

// Handle form submission for adding new asset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_asset'])) {
        try {
            // Get the last asset ID from the database
            $getLastIdQuery = "SELECT asset_id FROM assets ORDER BY id DESC LIMIT 1";
            $lastIdResult = $conn->query($getLastIdQuery);
            
            if ($lastIdResult && $lastIdResult->num_rows > 0) {
                $lastAsset = $lastIdResult->fetch_assoc();
                $lastId = intval(substr($lastAsset['asset_id'], 4)); // Remove 'AST-' prefix and convert to number
                $newId = $lastId + 1;
            } else {
                $newId = 1; // If no assets exist, start with 1
            }
            
            // Format the new asset ID with leading zeros
            $asset_id = 'AST-' . str_pad($newId, 3, '0', STR_PAD_LEFT);

            $user_id = $_SESSION['user_id'];
            $name = $_POST['name'];
            $type = $_POST['type'];
            $location = $_POST['location'];
            $status = $_POST['status'];
            $cost = $_POST['cost'];
            $purchase_date = $_POST['purchase_date'];
            
            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO assets (asset_id, name, type, location, status, cost, purchase_date, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssssdss", $asset_id, $name, $type, $location, $status, $cost, $purchase_date, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['asset_success'] = "Asset added successfully with ID: " . $asset_id;
                error_log("Asset added successfully with ID: " . $asset_id);
            } else {
                throw new Exception("Error adding asset: " . $stmt->error);
            }
            
            $stmt->close();
            header("Location: assets.php");
            exit();
            
        } catch (Exception $e) {
            error_log("Error in asset submission: " . $e->getMessage());
            $_SESSION['asset_error'] = $e->getMessage();
            header("Location: assets.php");
            exit();
        }
    } elseif (isset($_POST['edit_asset'])) {
        try {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $type = $_POST['type'];
            $location = $_POST['location'];
            $status = $_POST['status'];
            $cost = $_POST['cost'];
            $purchase_date = $_POST['purchase_date'];
            $user_id = $_SESSION['user_id']; // or $user_email = $_SESSION['email'];
            
            $stmt = $conn->prepare("UPDATE assets SET name=?, type=?, location=?, status=?, cost=?, purchase_date=?, user_id=? WHERE id=?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ssssdsii", $name, $type, $location, $status, $cost, $purchase_date, $user_id, $id);
            
            if ($stmt->execute()) {
                $_SESSION['asset_success'] = "Asset updated successfully!";
            } else {
                throw new Exception("Error updating asset: " . $stmt->error);
            }
            
            $stmt->close();
            header("Location: assets.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['asset_error'] = $e->getMessage();
            header("Location: assets.php");
            exit();
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['asset_success'] = "Asset deleted successfully!";
        } else {
            throw new Exception("Error deleting asset: " . $stmt->error);
        }
        
        $stmt->close();
        header("Location: assets.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['asset_error'] = $e->getMessage();
        header("Location: assets.php");
        exit();
    }
}

// Add user_id for filtering
$user_id = $_SESSION['user_id'];

// Build the WHERE clause based on filters
$whereConditions = ["user_id = ?"];
$params = [$user_id];
$types = 'i';

// Get unique departments for both filter and form dropdown
$departmentsQuery = "SELECT DISTINCT location as department FROM assets WHERE user_id = ? AND location IS NOT NULL AND location != '' ORDER BY location ASC";
$departmentsStmt = $conn->prepare($departmentsQuery);
$departmentsStmt->bind_param('i', $user_id);
$departmentsStmt->execute();
$departmentsResult = $departmentsStmt->get_result();

if (isset($_GET['type']) && $_GET['type'] !== 'all') {
    $whereConditions[] = "type = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}

if (isset($_GET['status']) && $_GET['status'] !== 'all') {
    $whereConditions[] = "status = ?";
    $params[] = ucfirst($_GET['status']);
    $types .= 's';
}

if (isset($_GET['department']) && $_GET['department'] !== 'all') {
    $whereConditions[] = "location = ?";
    $params[] = $_GET['department'];
    $types .= 's';
}

if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
    $whereConditions[] = "purchase_date >= ?";
    $params[] = $_GET['date_from'];
    $types .= 's';
}

// Construct the final query
$assetsQuery = "SELECT * FROM assets WHERE ".implode(" AND ", $whereConditions)." ORDER BY id DESC";
$stmt = $conn->prepare($assetsQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$assetsResult = $stmt->get_result();

if (!$assetsResult) {
    throw new Exception("Error fetching assets: " . $conn->error);
}

// Count total assets for this user
$totalAssetsQuery = "SELECT COUNT(*) as total FROM assets WHERE user_id = ?";
$totalAssetsStmt = $conn->prepare($totalAssetsQuery);
$totalAssetsStmt->bind_param('i', $user_id);
$totalAssetsStmt->execute();
$totalAssetsResult = $totalAssetsStmt->get_result();

if (!$totalAssetsResult) {
    throw new Exception("Error counting assets: " . $conn->error);
}

$totalAssetsData = $totalAssetsResult->fetch_assoc();
$totalAssets = $totalAssetsData['total'];

// Get unique types for filter (for this user)
$typesQuery = "SELECT DISTINCT type FROM assets WHERE user_id = ? AND type IS NOT NULL";
$typesStmt = $conn->prepare($typesQuery);
$typesStmt->bind_param('i', $user_id);
$typesStmt->execute();
$typesResult = $typesStmt->get_result();

if (!$typesResult) {
    throw new Exception("Error fetching types: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assets</title>
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
            <li class="active"><a href="assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
            <li><a href="investments.php"><i class="fas fa-chart-line"></i> Investments</a></li>
            <li><a href="audits.html"><i class="fas fa-clipboard-check"></i> Audits</a></li>
            <li><a href="reports.html"><i class="fas fa-file-alt"></i> Reports</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
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
                    <input type="text" placeholder="Search assets...">
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

            <!-- Assets Content -->
            <div class="dashboard-content">
                <div class="page-header">
                    <h1>Asset Management</h1>
                    <p>View, add, and manage all IT assets in your organization</p>
                </div>

                <!-- Display success/error messages -->
                <?php if(isset($_SESSION['asset_success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['asset_success']; ?>
                    <?php unset($_SESSION['asset_success']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['asset_error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['asset_error']; ?>
                    <?php unset($_SESSION['asset_error']); ?>
                </div>
                <?php endif; ?>

                <!-- Enhanced Asset Filters -->
                <div class="filter-section">
                    <div class="filter-header">
                        <h3><i class="fas fa-filter"></i> Filter Assets</h3>
                        <button class="collapse-filters"><i class="fas fa-chevron-up"></i></button>
                    </div>
                    
                    <div class="filter-body">
                        <div class="filter-group">
                            <label for="asset-type">
                                <i class="fas fa-laptop"></i> Asset Type
                            </label>
                            <select id="asset-type" class="filter-select">
                                <option value="all">All Types</option>
                                <?php if($typesResult): while($type = $typesResult->fetch_assoc()): ?>
                                <option value="<?php echo strtolower($type['type']); ?>" <?php echo (isset($_GET['type']) && $_GET['type'] === strtolower($type['type'])) ? 'selected' : ''; ?>><?php echo $type['type']; ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="asset-status">
                                <i class="fas fa-info-circle"></i> Status
                            </label>
                            <!-- Update status select -->
                            <select id="asset-status" class="filter-select">
                                <option value="all">All Status</option>
                                <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="maintenance" <?php echo (isset($_GET['status']) && $_GET['status'] === 'maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                                <option value="retired" <?php echo (isset($_GET['status']) && $_GET['status'] === 'retired') ? 'selected' : ''; ?>>Retired</option>
                                <option value="lost" <?php echo (isset($_GET['status']) && $_GET['status'] === 'lost') ? 'selected' : ''; ?>>Lost/Stolen</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="asset-department">
                                <i class="fas fa-building"></i> Department
                            </label>
                            <select id="asset-department" class="filter-select">
                                <option value="all">All Departments</option>
                                <?php if($departmentsResult): while($dept = $departmentsResult->fetch_assoc()): ?>
                                <option value="<?php echo strtolower($dept['department']); ?>" <?php echo (isset($_GET['department']) && $_GET['department'] === strtolower($dept['department'])) ? 'selected' : ''; ?>><?php echo $dept['department']; ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="date-range">
                                <i class="fas fa-calendar-alt"></i> Purchase Date
                            </label>
                            <div class="date-range">
                                <input type="date" id="date-from" class="date-input" value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>" placeholder="From">
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button class="reset-btn"><i class="fas fa-undo"></i> Reset</button>
                        <button class="filter-btn"><i class="fas fa-filter"></i> Apply Filters</button>
                    </div>
                </div>

                <!-- Assets Table Section -->
                <div class="table-section">
                    <div class="table-header">
                        <h2>IT Assets Inventory (<?php echo $totalAssets; ?> total)</h2>
                        <div class="table-actions">
                            <button class="add-btn" id="add-asset-btn"><i class="fas fa-plus"></i> Add Asset</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="assets-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Cost</th>
                                    <th>Purchase Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($assetsResult && $assetsResult->num_rows > 0): ?>
                                <?php while($asset = $assetsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $asset['asset_id']; ?></td>
                                    <td><?php echo $asset['name']; ?></td>
                                    <td><?php echo $asset['type']; ?></td>
                                    <td><?php echo $asset['location'] ?? 'N/A'; ?></td>
                                    <td><span class="status-badge <?php echo strtolower($asset['status']); ?>"><?php echo $asset['status']; ?></span></td>
                                    <td>₹<?php echo number_format($asset['cost']); ?></td>
                                    <td><?php echo $asset['purchase_date']; ?></td>
                                    <td>
                                        <button class="action-btn edit" title="Edit" data-id="<?php echo $asset['id']; ?>"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete" title="Delete" data-id="<?php echo $asset['id']; ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">No assets found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Asset Modal -->
    <div id="add-asset-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Asset</h2>
                <button class="close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="add-asset-form" method="POST" action="assets.php" onsubmit="return validateForm()">
                    <input type="hidden" name="add_asset" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="asset_id">Asset ID</label>
                            <input type="text" id="asset_id" name="asset_id" readonly value="Auto-generated">
                        </div>
                        <div class="form-group">
                            <label for="name">Asset Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Asset Type</label>
                            <select id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="Laptop">Laptop</option>
                                <option value="Desktop">Desktop</option>
                                <option value="Monitor">Monitor</option>
                                <option value="Printer">Printer</option>
                                <option value="Server">Server</option>
                                <option value="Network">Network Equipment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Department/Location</label>
                            <select id="location" name="location" required>
                                <option value="">Select Department</option>
                                <option value="IT">IT Department</option>
                                <option value="Finance">Finance Department</option>
                                <option value="HR">Human Resources</option>
                                <option value="Marketing">Marketing Department</option>
                                <option value="Operations">Operations</option>
                                <option value="Sales">Sales Department</option>
                                <option value="Research">Research & Development</option>
                                <option value="Customer Service">Customer Service</option>
                                <option value="Administration">Administration</option>
                                <option value="Server Room">Server Room</option>
                                <option value="Headquarters">Headquarters</option>
                                <option value="Branch Office">Branch Office</option>
                                <?php if($departmentsResult): while($dept = $departmentsResult->fetch_assoc()): ?>
                                    <?php if(!in_array($dept['department'], ['IT', 'Finance', 'HR', 'Marketing', 'Operations', 'Sales', 'Research', 'Customer Service', 'Administration', 'Server Room', 'Headquarters', 'Branch Office'])): ?>
                                        <option value="<?php echo htmlspecialchars($dept['department']); ?>"><?php echo htmlspecialchars($dept['department']); ?></option>
                                    <?php endif; ?>
                                <?php endwhile; endif; ?>
                                <option value="other">Other (Specify)</option>
                            </select>
                            <input type="text" id="other_location" name="other_location" style="display: none; margin-top: 5px;" placeholder="Enter Department Name">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Maintenance">Under Maintenance</option>
                                <option value="Retired">Retired</option>
                                <option value="Lost">Lost/Stolen</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cost">Cost</label>
                            <input type="number" id="cost" name="cost" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="purchase_date">Purchase Date</label>
                            <input type="date" id="purchase_date" name="purchase_date" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" name="add_asset" class="submit-btn">Add Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Asset Modal -->
    <div id="edit-asset-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Asset</h2>
                <button class="close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="edit-asset-form" method="POST" action="assets.php">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_asset_id">Asset ID</label>
                            <input type="text" id="edit_asset_id" readonly>
                        </div>
                        <div class="form-group">
                            <label for="edit_name">Asset Name</label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_type">Asset Type</label>
                            <select id="edit_type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="Laptop">Laptop</option>
                                <option value="Desktop">Desktop</option>
                                <option value="Monitor">Monitor</option>
                                <option value="Printer">Printer</option>
                                <option value="Server">Server</option>
                                <option value="Network">Network Equipment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_location">Department/Location</label>
                            <select id="edit_location" name="location" required>
                                <option value="">Select Department</option>
                                <option value="IT">IT Department</option>
                                <option value="Finance">Finance Department</option>
                                <option value="HR">Human Resources</option>
                                <option value="Marketing">Marketing Department</option>
                                <option value="Operations">Operations</option>
                                <option value="Sales">Sales Department</option>
                                <option value="Research">Research & Development</option>
                                <option value="Customer Service">Customer Service</option>
                                <option value="Administration">Administration</option>
                                <option value="Server Room">Server Room</option>
                                <option value="Headquarters">Headquarters</option>
                                <option value="Branch Office">Branch Office</option>
                                <?php if($departmentsResult): while($dept = $departmentsResult->fetch_assoc()): ?>
                                    <?php if(!in_array($dept['department'], ['IT', 'Finance', 'HR', 'Marketing', 'Operations', 'Sales', 'Research', 'Customer Service', 'Administration', 'Server Room', 'Headquarters', 'Branch Office'])): ?>
                                        <option value="<?php echo htmlspecialchars($dept['department']); ?>"><?php echo htmlspecialchars($dept['department']); ?></option>
                                    <?php endif; ?>
                                <?php endwhile; endif; ?>
                                <option value="other">Other (Specify)</option>
                            </select>
                            <input type="text" id="edit_other_location" name="other_location" style="display: none; margin-top: 5px;" placeholder="Enter Department Name">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Maintenance">Under Maintenance</option>
                                <option value="Retired">Retired</option>
                                <option value="Lost">Lost/Stolen</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_cost">Cost</label>
                            <input type="number" id="edit_cost" name="cost" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_purchase_date">Purchase Date</label>
                            <input type="date" id="edit_purchase_date" name="purchase_date" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" name="edit_asset" class="submit-btn">Update Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function validateForm() {
        const assetId = document.getElementById('asset_id').value;
        const name = document.getElementById('name').value;
        const type = document.getElementById('type').value;
        const location = document.getElementById('location').value;
        const otherLocation = document.getElementById('other_location').value;
        const status = document.getElementById('status').value;
        const cost = document.getElementById('cost').value;
        const purchaseDate = document.getElementById('purchase_date').value;

        if (!assetId || !name || !type || !status || !cost || !purchaseDate) {
            alert('Please fill in all required fields');
            return false;
        }

        if (location === 'other' && !otherLocation) {
            alert('Please specify the department name');
            return false;
        }

        if (isNaN(cost) || cost <= 0) {
            alert('Please enter a valid cost');
            return false;
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modal = document.getElementById('add-asset-modal');
        const addBtn = document.getElementById('add-asset-btn');
        const closeBtn = document.querySelector('.close-modal');
        const cancelBtn = document.querySelector('.cancel-btn');

        // Open modal
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });
        }

        // Close modal functions
        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto'; // Enable scrolling
            document.getElementById('add-asset-form').reset(); // Reset form
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
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

        // Handle department dropdown change
        const locationSelect = document.getElementById('location');
        const otherLocationInput = document.getElementById('other_location');

        locationSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                otherLocationInput.style.display = 'block';
                otherLocationInput.required = true;
            } else {
                otherLocationInput.style.display = 'none';
                otherLocationInput.required = false;
            }
        });

        // Modify form submission to handle other location
        const form = document.getElementById('add-asset-form');
        form.addEventListener('submit', function(e) {
            if (locationSelect.value === 'other') {
                locationSelect.value = otherLocationInput.value;
            }
        });

        // Edit functionality
        const editButtons = document.querySelectorAll('.action-btn.edit');
        const editModal = document.getElementById('edit-asset-modal');
        const editForm = document.getElementById('edit-asset-form');
        const editLocationSelect = document.getElementById('edit_location');
        const editOtherLocationInput = document.getElementById('edit_other_location');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const row = this.closest('tr');
                
                // Populate form with current values
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_asset_id').value = row.cells[0].textContent;
                document.getElementById('edit_name').value = row.cells[1].textContent;
                document.getElementById('edit_type').value = row.cells[2].textContent;
                document.getElementById('edit_location').value = row.cells[3].textContent;
                document.getElementById('edit_status').value = row.cells[4].querySelector('.status-badge').textContent.trim();
                document.getElementById('edit_cost').value = parseFloat(row.cells[5].textContent.replace('₹', '').replace(/,/g, ''));
                document.getElementById('edit_purchase_date').value = row.cells[6].textContent;
                
                editModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        // Delete functionality
        const deleteButtons = document.querySelectorAll('.action-btn.delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this asset?')) {
                    const id = this.getAttribute('data-id');
                    window.location.href = `assets.php?delete=${id}`;
                }
            });
        });

        // Handle edit location dropdown change
        editLocationSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                editOtherLocationInput.style.display = 'block';
                editOtherLocationInput.required = true;
            } else {
                editOtherLocationInput.style.display = 'none';
                editOtherLocationInput.required = false;
            }
        });

        // Close edit modal
        const closeEditModal = editModal.querySelector('.close-modal');
        const cancelEditBtn = editModal.querySelector('.cancel-btn');
        
        function closeEditModalFunc() {
            editModal.classList.remove('active');
            document.body.style.overflow = 'auto';
            editForm.reset();
        }
        
        closeEditModal.addEventListener('click', closeEditModalFunc);
        cancelEditBtn.addEventListener('click', closeEditModalFunc);

        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                closeEditModalFunc();
            }
        });
    });
    </script>

    <script src="assets.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Existing code...

        // Filter collapse functionality
        const collapseBtn = document.querySelector('.collapse-filters');
        const filterBody = document.querySelector('.filter-body');
        const filterActions = document.querySelector('.filter-actions');
        
        collapseBtn.addEventListener('click', function() {
            filterBody.classList.toggle('collapsed');
            filterActions.classList.toggle('collapsed');
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (filterBody.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        });
    });
    </script>

    <script>
    const filterBtn = document.querySelector('.filter-btn');
    const resetBtn = document.querySelector('.reset-btn');
    const assetTypeSelect = document.getElementById('asset-type');
    const assetStatusSelect = document.getElementById('asset-status');
    const assetDepartmentSelect = document.getElementById('asset-department');
    const dateFromInput = document.getElementById('date-from');
    
    filterBtn.addEventListener('click', function() {
        const filters = {
            type: assetTypeSelect.value,
            status: assetStatusSelect.value,
            department: assetDepartmentSelect.value,
            date_from: dateFromInput.value
        };
    
        // Build query string
        const queryString = Object.entries(filters)
            .filter(([_, value]) => value && value !== 'all')
            .map(([key, value]) => `${key}=${encodeURIComponent(value)}`)
            .join('&');
    
        // Redirect with filters
        window.location.href = `assets.php${queryString ? '?' + queryString : ''}`;
    });
    
    // Reset filters
    resetBtn.addEventListener('click', function() {
        assetTypeSelect.value = 'all';
        assetStatusSelect.value = 'all';
        assetDepartmentSelect.value = 'all';
        dateFromInput.value = '';
        window.location.href = 'assets.php';
    });
    </script>
</body>
</html>