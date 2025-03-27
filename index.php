<?php
// Start session and include database connection
session_start();
include 'db.php';  // Include the database connection

// Function to safely redirect
function safe_redirect($location) {
    header("Location: $location");
    exit;
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on user role
    switch ($_SESSION['role']) {
        case 'admin':
            safe_redirect('admin_dashboard.php');
            break;
        case 'borrower':
            safe_redirect('borrower_dashboard.php');
            break;
        default:
            session_unset();
            session_destroy();
            break;
    }
}

// Initialize error variable
$error = '';

// Check if login form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Prepare the query to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Clear previous session data
                session_unset();

                // Set new session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Redirect based on role
                if ($user['role'] == 'admin') {
                    $_SESSION['admin'] = true;  // Set the session for admin
                    safe_redirect('admin_dashboard.php'); // Redirect to admin dashboard
                } elseif ($user['role'] == 'borrower') {
                    safe_redirect('borrower_dashboard.php'); // Redirect to borrower dashboard
                } else {
                    $error = "Invalid user role.";
                }
                exit;
            } else {
                $error = "Invalid credentials!";
            }
        } else {
            $error = "User not found!";
        }

        // Close statement
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASA Philippines - Loan Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;      /* Changed to #4A90E2 */
            --secondary-color: #159895;    /* Teal accent */
            --accent-color: #57C5B6;       /* Light Teal */
            --background-light: #F8F9FA;   /* Light Gray Background */
            --text-color: #333;
            --border-color: #DEE2E6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(26, 95, 122, 0.1) 0%, rgba(21, 152, 149, 0.1) 100%);
        }

        .login-wrapper {
            display: flex;
            width: 900px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .login-image {
            flex: 1;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 810" preserveAspectRatio="xMinYMin slice"><path fill="%231A5F7A" d="M0 0h1440v810H0z"/><path d="M0 405c240 86.4 480 129.6 720 129.6S1200 491.4 1440 405v405H0V405z" fill="%2357C5B6" opacity=".5"/></svg>') no-repeat center center;
            background-size: cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
            color: white;
            text-align: center;
        }

        .login-logo {
            width: 200px;
            height: 200px;
            background-image: url('asa-logo.png'); /* Use your logo URL here */
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-bottom: 20px;
        }

        .login-container {
            flex: 1;
            background-color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .login-header p {
            color: var(--secondary-color);
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .error-message i {
            margin-right: 10px;
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }

        .login-form input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(26, 95, 122, 0.2);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            background-color: var(--secondary-color);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--text-color);
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                width: 100%;
                height: 100vh;
            }

            .login-image {
                display: none;
            }

            .login-container {
                justify-content: center;
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-image">
            <div class="login-logo"></div>
            <h2>ASA Philippines Microfinance</h2>
            <p>Empowering Communities, Transforming Lives</p>
        </div>
        
        <div class="login-container">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Login to your account</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form" autocomplete="off">
                <input type="text" name="username" placeholder="Username" required autocomplete="off">
                <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="borrower_register.php">Register here</a>
            </div>
        </div>
    </div>

    <script>
        // Optional: Add subtle form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.login-form input');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.classList.remove('focused');
                });
            });
        });
    </script>
</body>
</html>
