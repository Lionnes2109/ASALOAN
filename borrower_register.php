<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            line-height: 1.6;
        }

        .registration-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .registration-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .go-back-btn {
            display: inline-block;
            text-align: center;
            margin-top: 20px;
        }

        .go-back-btn button {
            padding: 10px 20px;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .go-back-btn button:hover {
            background-color: #4A90E2;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #4A90E2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #357ABD;
        }

        .error-message {
            color: #D8000C;
            background-color: #FFD2D2;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .success-message {
            color: #4CAF50;
            background-color: #DFF2BF;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2>Borrower Registration</h2>
        
        <?php
        include 'db.php';  // Include the database connection

        $error_message = '';
        $success_message = '';
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // More robust validation
            if (empty($username)) {
                $error_message = "Username cannot be empty.";
            } elseif (strlen($username) < 3) {
                $error_message = "Username must be at least 3 characters long.";
            } elseif (strlen($password) < 8) {
                $error_message = "Password must be at least 8 characters long.";
            } elseif ($password !== $confirm_password) {
                $error_message = "Passwords do not match!";
            } else {
                // Check if username already exists
                $check_username = "SELECT * FROM users WHERE username = ?";
                $stmt = $conn->prepare($check_username);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $error_message = "Username already exists. Please choose another.";
                } else {
                    // Hash the password before storing it
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Prepared statement for insertion
                    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'borrower')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $username, $hashed_password);

                    if ($stmt->execute()) {
                        $success_message = "Account created successfully! You can now <a href='index.php'>log in</a>.";
                    } else {
                        $error_message = "Error creating account: " . $conn->error;
                    }
                }
            }
        }
        ?>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       minlength="3" 
                       value="<?php echo htmlspecialchars($username); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>

            <button type="submit" class="btn">Register</button>
        </form>     
        <a href="borrower_dashboard.php" class="go-back-btn">
            <button type="button">Go Back</button>
        </a>

    </div>

    <script>
    function validateForm() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password.value !== confirmPassword.value) {
            alert('Passwords do not match!');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
