<?php 
session_start();
include('config.php');
error_reporting(0);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id=$_POST['id']; // Fixed variable name from $POST to $_POST
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO users (id,full_name, email,password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss",$id, $full_name, $email, $password);

    if ($stmt->execute()) {
        // Set success message in session
        $_SESSION['signup_success'] = "Account created successfully! Please login.";
        // Redirect to login page
        header("Location: login.php");
        exit();
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - IT Asset Manager</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container signup">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-circle">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h1>Create an account</h1>
                <p>Enter your details to create your account.</p>
            </div>
            
            <?php if(isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <form action="signup.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="hi@yourcompany.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the Terms and Conditions</label>
                    </div>
                </div>

                <button type="submit" name="signup" class="btn btn-primary" id="submitt">Create Account</button>

                <p class="auth-switch">
                    Already have an account? <a href="login.php">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
