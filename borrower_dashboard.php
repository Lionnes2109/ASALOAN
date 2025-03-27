<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include database connection
include 'db.php';

// Comprehensive session and login verification
function validateSession() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // Clear any existing session data
        session_unset();
        session_destroy();
        
        // Redirect to login page
        header("Location: index.php");
        exit;
    }

    // Ensure only borrowers can access this page
    if ($_SESSION['role'] !== 'borrower') {
        // Redirect based on role or logout
        header("Location: index.php");
        exit;
    }
}

// Validate user session
validateSession();

// Initialize error message
$error_message = '';

// Fetch borrower's loan applications
try {
    // Verify database connection
    if (!$conn) {
        throw new Exception("Database connection is not established.");
    }

    // Detailed debugging: print user ID
    $borrower_id = $_SESSION['user_id'];
    error_log("Fetching loan applications for user ID: " . $borrower_id);

    // Prepare SQL statement with enhanced security and error checking
    $sql = "SELECT 
                id, 
                amount, 
                purpose, 
                status, 
                created_at 
            FROM loan_applications 
            WHERE borrower_id = ?";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        // Log detailed prepare error
        error_log("Prepare statement failed: " . $conn->error);
        throw new Exception("Failed to prepare SQL statement.");
    }

    // Bind borrower ID
    $stmt->bind_param("i", $borrower_id);
    
    // Execute query
    if (!$stmt->execute()) {
        // Log detailed execute error
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Failed to execute query.");
    }

    // Get results
    $loan_results = $stmt->get_result();

    // Additional error checking
    if ($loan_results === false) {
        throw new Exception("Failed to get query results.");
    }

} catch (Exception $e) {
    // Log the full error
    error_log("Loan Application Fetch Error: " . $e->getMessage());
    
    // User-friendly error message
    $error_message = "An error occurred while fetching your loan applications. Please contact support.";
    
    // Optionally, you could log the full error to a file for debugging
    file_put_contents('loan_application_errors.log', 
        date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 
        FILE_APPEND
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASA Philippines - Borrower Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --asa-blue: #1976D2;
            --asa-red: #D32F2F;
            --asa-yellow: #FFC107;
            --asa-brown: #8D6E63;
            --text-dark: #333;
            --background-light: #F5F5F5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .asa-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .asa-header {
            background-color: var(--asa-blue);
            color: white;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .asa-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .asa-logo {
            height: 50px;
            display: flex;
            align-items: center;
        }

        .asa-logo img {
            height: 100%;
            margin-right: 15px;
        }

        .asa-nav {
            display: flex;
            gap: 15px;
        }

        .asa-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .asa-nav a:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .asa-dashboard {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 20px;
            overflow: hidden;
        }

        .asa-dashboard-header {
            background-color: var(--asa-red);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .asa-loan-table {
            width: 100%;
            border-collapse: collapse;
        }

        .asa-loan-table th {
            background-color: var(--background-light);
            color: var(--text-dark);
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
        }

        .asa-loan-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .asa-loan-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-pending {
            background-color: var(--asa-yellow);
            color: var(--text-dark);
        }

        .status-approved {
            background-color: #4CAF50;
            color: white;
        }

        .status-rejected {
            background-color: var(--asa-red);
            color: white;
        }

        .asa-no-applications {
            text-align: center;
            padding: 50px 20px;
            background-color: #f9f9f9;
        }

        .asa-error-message {
            background-color: #ffdddd;
            border-left: 6px solid var(--asa-red);
            padding: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .asa-header-content {
                flex-direction: column;
                text-align: center;
            }

            .asa-nav {
                margin-top: 15px;
                flex-direction: column;
                align-items: center;
            }

            .asa-loan-table {
                font-size: 0.9em;
            }
        }

 body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--background-light);
    color: var(--text-dark);
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.asa-container {
    flex: 1;
}

footer {
    margin-top: auto;
    text-align: center;
    padding: 20px;
    background-color: var(--asa-blue);
    color: white;
}

    </style>
</head>
<body>
    <header class="asa-header">
        <div class="asa-header-content">
            <div class="asa-logo">
                <!-- Replace with actual ASA Philippines logo path -->
                <img src="asa-logo.png" alt="ASA Philippines Logo">
                <h1>ASA Philippines Microfinance</h1>
            </div>
            <nav class="asa-nav">
                <a href="borrower_apply.php">Apply for Loan</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>


        <div class="asa-dashboard">
            <div class="asa-dashboard-header">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            </div>

            <?php if(isset($loan_results) && $loan_results->num_rows > 0): ?>
                <table class="asa-loan-table">
                    <thead>
                        <tr>
                            <th>Loan Amount</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Application Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($loan = $loan_results->fetch_assoc()): ?>
                            <tr>
                                <td>₱<?php echo number_format($loan['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($loan['purpose']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($loan['status']); ?>">
                                        <?php echo htmlspecialchars($loan['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="asa-no-applications">
                    <h3>No Loan Applications Yet</h3>
                    <p>Start your financial journey with ASA Philippines. <a href="borrower_apply.php">Apply for your first loan</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="asa-footer">
    <p>&copy; <?php echo date('Y'); ?> ASA Philippines Microfinance. All Rights Reserved.</p>
</footer>
</body>
</html>