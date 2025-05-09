<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset_name = htmlspecialchars(trim($_POST['asset_name']));
    $asset_type = htmlspecialchars(trim($_POST['asset_type']));
    $department = htmlspecialchars(trim($_POST['department']));
    $cost = floatval($_POST['cost']);
    $purchase_date = $_POST['purchase_date'];
    $status = htmlspecialchars(trim($_POST['status']));

    // Generate new asset ID
    $result = mysqli_query($conn, "SELECT asset_id FROM assets ORDER BY id DESC LIMIT 1");
    $new_id_num = 1;
    if ($row = mysqli_fetch_assoc($result)) {
        $last_id = $row['asset_id'];
        $last_num = intval(substr($last_id, 4));
        $new_id_num = $last_num + 1;
    }

    $asset_id = "AST-" . str_pad($new_id_num, 3, "0", STR_PAD_LEFT);

    $sql = "INSERT INTO assets (asset_id, asset_name, asset_type, department, cost, purchase_date, status)
            VALUES ('$asset_id', '$asset_name', '$asset_type', '$department', $cost, '$purchase_date', '$status')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('✅ Asset Saved Successfully!\\nAsset ID: $asset_id');
                window.location.href = 'index.html';
              </script>";
    } else {
        echo "<script>
                alert('❌ Error saving asset: " . mysqli_error($conn) . "');
                window.location.href = 'index.html';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid request method.');
            window.location.href = 'index.html';
          </script>";
}

mysqli_close($conn);
?>









