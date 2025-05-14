<?php
session_start();
include('config.php');
if (!isset($_SESSION['email'])) { 
    header("Location: login.php");
    exit();
}

// Fetch summary data for cards for this user
$user_id = $_SESSION['user_id'];

// Total Investments: sum of all investments (budget) for this user
$investmentQuery = "SELECT SUM(budget) as total_investment FROM investments WHERE user_id = ?";
$investmentStmt = $conn->prepare($investmentQuery);
$investmentStmt->bind_param('i', $user_id);
$investmentStmt->execute();
$investmentResult = $investmentStmt->get_result();
$investmentData = $investmentResult->fetch_assoc();
$totalInvestment = number_format($investmentData['total_investment'] ?? 0);

// Total Assets value: sum of all assets (cost) for this user
$assetsValueQuery = "SELECT SUM(cost) as total_assets_value FROM assets WHERE user_id = ?";
$assetsValueStmt = $conn->prepare($assetsValueQuery);
$assetsValueStmt->bind_param('i', $user_id);
$assetsValueStmt->execute();
$assetsValueResult = $assetsValueStmt->get_result();
$assetsValueData = $assetsValueResult->fetch_assoc();
$totalAssetsValue = number_format($assetsValueData['total_assets_value'] ?? 0);

$assetsQuery = "SELECT COUNT(*) as total_assets FROM assets WHERE user_id = ?";
$assetsStmt = $conn->prepare($assetsQuery);
$assetsStmt->bind_param('i', $user_id);
$assetsStmt->execute();
$assetsResult = $assetsStmt->get_result();
$assetsData = $assetsResult->fetch_assoc();
$totalAssets = $assetsData['total_assets'];

$auditsQuery = "SELECT COUNT(*) as pending_audits FROM audits WHERE status = 'Pending'";
$auditsResult = $conn->query($auditsQuery);
$auditsData = $auditsResult->fetch_assoc();
$pendingAudits = $auditsData['pending_audits'];

// Fetch assets for the table
$assetsTableQuery = "SELECT * FROM assets WHERE user_id = ? ORDER BY id DESC LIMIT 6";
$stmt = $conn->prepare($assetsTableQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assetsTableResult = $stmt->get_result();

$end_date = $_POST['end_date'] ?? null;
if (!empty($end_date) && preg_match('/^\\d{4}$/', $end_date)) {
    $end_date = $end_date . '-12-31';
}
?>
<!DOCTYPE HTML>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-laptop-code"></i> Asset & Invest Manager</h2>
        </div>
        <ul class="nav-links">
            <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
            <li><a href="investments.php"><i class="fas fa-chart-line"></i> Investments</a></li>
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
                    <input type="text" placeholder="Search">
                    <i class="fas fa-search"></i>
                </div>
                <div class="user-profile">
                    <span class="notification-icon"><i class="fas fa-bell"></i></span>
                    <div class="profile-img">
                        <img src="profile.jpeg" alt="User Profile">
                    </div>
                    <span class="user-name"><?php echo $_SESSION['full_name'];?> </span>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Financial Summary Cards -->
                <div class="summary-cards">
                    <div class="card">
                        <div class="card-icon blue">
                            <i class= "fa-solid fa-sack-dollar"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Investments</h3>
                            <p class="value">₹<?php echo $totalInvestment; ?></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon green">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Assets value</h3>
                            <p class="value">₹<?php echo $totalAssetsValue; ?></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon purple">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Assets</h3>
                            <p class="value"><?php echo $totalAssets; ?></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon orange">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="card-info">
                            <h3>Pending Audits</h3>
                            <p class="value"><?php echo $pendingAudits; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Assets Table Section -->
                <div class="table-section">
                    <div class="table-header">
                        <h2>IT Assets</h2>
                    </div>
                    <div class="table-container">
                        <table class="assets-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Cost</th>
                                    <th>Purchase Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($asset = $assetsTableResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $asset['asset_id']; ?></td>
                                    <td><?php echo $asset['name']; ?></td>
                                    <td><?php echo $asset['type']; ?></td>
                                    <td>₹<?php echo number_format($asset['cost']); ?></td>
                                    <td><?php echo $asset['purchase_date']; ?></td>
                                    <td>
                                        <button class="action-btn delete" data-id="<?php echo $asset['id']; ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if($assetsTableResult->num_rows == 0): ?>
                                <tr>
                                    <td colspan="6" class="no-data">No assets found</td>
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

    <script src="script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete button clicks
        const deleteButtons = document.querySelectorAll('.action-btn.delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                const assetId = this.getAttribute('data-id');
                const assetCost = this.closest('tr').querySelector('td:nth-child(4)').textContent.replace('₹', '').replace(/,/g, '');
                
                if (confirm('Are you sure you want to delete this asset?')) {
                    try {
                        const response = await fetch(`delete_asset.php?id=${assetId}&cost=${assetCost}`, {
                            method: 'GET'
                        });
                        
                        if (response.ok) {
                            // Remove the row from the table
                            this.closest('tr').remove();
                            
                            // Update the total assets count
                            const totalAssetsElement = document.querySelector('.card:nth-child(3) .value');
                            let totalAssets = parseInt(totalAssetsElement.textContent) - 1;
                            totalAssetsElement.textContent = totalAssets;
                            
                            // Update investment and valuation
                            const totalInvestmentElement = document.querySelector('.card:nth-child(1) .value');
                            const totalValuationElement = document.querySelector('.card:nth-child(2) .value');
                            
                            let currentInvestment = parseFloat(totalInvestmentElement.textContent.replace('₹', '').replace(/,/g, ''));
                            let currentValuation = parseFloat(totalValuationElement.textContent.replace('₹', '').replace(/,/g, ''));
                            
                            currentInvestment -= parseFloat(assetCost);
                            currentValuation -= parseFloat(assetCost);
                            
                            totalInvestmentElement.textContent = '₹' + currentInvestment.toLocaleString();
                            totalValuationElement.textContent = '₹' + currentValuation.toLocaleString();
                            
                            // Show success message
                            alert('Asset deleted successfully!');
                        } else {
                            throw new Error('Failed to delete asset');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error deleting asset. Please try again.');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>
