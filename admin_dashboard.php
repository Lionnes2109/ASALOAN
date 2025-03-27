<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");  // Redirect to login if not logged in
    exit;
}

// Handle the page content dynamically based on button click
$section = isset($_GET['section']) ? $_GET['section'] : 'view_applications';  // Default to viewing loan applications

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ASA Loan System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007B8D;    /* Blue color from the image */
            --secondary-color:  #4A90E2;  /* Darker Teal */
            --background-color: #E1F3F8; /* Light Teal background */
            --text-color: #333;
            --white: #ffffff;
            --green: #28a745;
            --red: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            padding: 20px;
            color: var(--white);
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar .logo img {
            max-width: 120px;
        }

        .sidebar-menu {
            list-style-type: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            color: var(--white);
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.2);
        }

        .main-content {
            flex-grow: 1;
            padding: 30px;
            background-color: var(--background-color);
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 24px;
            font-weight: 600;
        }

        .logout-btn {
            background-color: var(--red);
            color: var(--white);
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .data-table {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .data-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background-color: #f8f9fa;
            color: var(--secondary-color);
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .action-links a {
            display: inline-block;
            padding: 6px 12px;
            margin-right: 5px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
        }

        .review-btn {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .review-section {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .review-field {
            margin-bottom: 15px;
        }

        .review-field label {
            display: block;
            margin-bottom: 5px;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .review-field input, .review-field select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .approve-btn, .reject-btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: var(--white);
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .approve-btn {
            background-color: var(--green);
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: var(--red);
        }

        .reject-btn:hover {
            background-color: #bd2130;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <div class="logo">
            <img src="asa-logo.png" alt="ASA Philippines Logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="?section=view_applications" class="<?php echo ($section == 'view_applications') ? 'active' : ''; ?>">View Loan Applications</a></li>
            <li><a href="?section=disburse_loans" class="<?php echo ($section == 'disburse_loans') ? 'active' : ''; ?>">Disburse Loans</a></li>
            <li><a href="?section=loan_reports" class="<?php echo ($section == 'loan_reports') ? 'active' : ''; ?>">Generate Loan Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Welcome, Admin</h2>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        

        <?php if ($section == 'view_applications'): ?>
    <div class="data-table">
        <h3>Loan Applications</h3>
        <?php
        // Fetch loan applications with specific columns
        $sql = "SELECT loan_applications.id AS loan_id, loan_applications.amount, loan_applications.purpose, loan_applications.status, users.username 
                FROM loan_applications 
                JOIN users ON loan_applications.borrower_id = users.id";
        $result = $conn->query($sql);
        ?>

        <table>
            <thead>
                <tr>
                    <th>Borrower</th>
                    <th>Loan Amount</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="action-links">
                            <a href="?section=review_application&id=<?php echo $row['loan_id']; ?>" class="review-btn">Review</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

            <?php elseif ($section == 'review_application'): ?>
    <div class="review-section">
        <h3>Detailed Application Review</h3>
        <?php
        // Check if ID is provided
        if (isset($_GET['id'])) {
            $loan_id = $_GET['id'];

            // Use prepared statement to prevent SQL injection
            $sql = "SELECT loan_applications.*, users.username 
                    FROM loan_applications 
                    JOIN users ON loan_applications.borrower_id = users.id 
                    WHERE loan_applications.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $loan_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $application = $result->fetch_assoc();

            // Check if application exists
            if ($application) {
        ?>
                <div class="review-field">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['first_name'] . ' ' . ($application['middle_name'] ?? '') . ' ' . $application['last_name']); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Gender</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['gender']); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Date of Birth</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['birthdate']); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Civil Status</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['civil_status']); ?>" readonly>
                </div>

                <h4>Contact Information</h4>
                <div class="review-field">
                    <label>Mobile Number</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['mobile_number']); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Email</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['email']); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Address</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['address'] . ', ' . $application['barangay'] . ', ' . $application['city'] . ', ' . $application['province']); ?>" readonly>
                </div>

                <h4>Employment Details</h4>
                <div class="review-field">
                    <label>Employment Type</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['employment_type']); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Monthly Income</label>
                    <input type="text" value="<?php echo htmlspecialchars(number_format($application['monthly_income'], 2)); ?>" readonly>
                </div>

                <h4>Loan Details</h4>
                <div class="review-field">
                    <label>Loan Amount</label>
                    <input type="text" value="<?php echo htmlspecialchars(number_format($application['amount'], 2)); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Loan Purpose</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['purpose']); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Identification Type</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['valid_id_type'] ?? 'Not Provided'); ?>" readonly>
                </div>
                <div class="review-field">
                    <label>Current Status</label>
                    <input type="text" value="<?php echo htmlspecialchars($application['status']); ?>" readonly>
                </div>

                <div class="action-buttons">
                    <a href="?section=approve_loan&id=<?php echo $loan_id; ?>&action=approve" class="approve-btn">Approve Application</a>
                    <a href="?section=approve_loan&id=<?php echo $loan_id; ?>&action=reject" class="reject-btn">Reject Application</a>
                </div>
        <?php
            } else {
                echo "<p>No application found with ID: " . htmlspecialchars($loan_id) . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p>No application ID provided.</p>";
        }
        ?>
    </div>

        <?php elseif ($section == 'disburse_loans'): ?>
            <div class="data-table">
                <h3>Disburse Loans</h3>
                <?php
                // Fetch loan applications with "approved" status
                $sql = "SELECT loan_applications.*, users.username FROM loan_applications JOIN users ON loan_applications.borrower_id = users.id WHERE loan_applications.status = 'approved'";
                $result = $conn->query($sql);
                ?>

                <table>
                    <thead>
                        <tr>
                            <th>Borrower</th>
                            <th>Loan Amount</th>
                            <th>Purpose</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['amount']; ?></td>
                                <td><?php echo $row['purpose']; ?></td>
                                <td class="action-links">
                                    <a href="?section=disburse_loan&id=<?php echo $row['id']; ?>" class="review-btn">Disburse Loan</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($section == 'loan_reports'): ?>
            <div class="data-table">
                <h3>Loan Application Reports</h3>
                <?php
                // Fetch loan status counts (approved, pending, rejected)
                $sql = "SELECT status, COUNT(*) as count FROM loan_applications GROUP BY status";
                $result = $conn->query($sql);
                ?>

                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo $row['count']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($section == 'approve_loan'): ?>
            <div class="review-section">
                <h3>Approve/Reject Loan</h3>
                <?php
                if (isset($_GET['id']) && isset($_GET['action'])) {
                    $loan_id = $_GET['id'];
                    $action = $_GET['action'];
                    $status = ($action == 'approve') ? 'approved' : 'rejected';

                    // Update loan status
                    $sql = "UPDATE loan_applications SET status = '$status' WHERE id = $loan_id";
                    if ($conn->query($sql) === TRUE) {
                        echo "<p>Loan $action successfully!</p>";
                        header("Location: ?section=view_applications");
                    } else {
                        echo "<p>Error updating loan status: " . $conn->error . "</p>";
                    }
                }
                ?>
            </div>

        <?php elseif ($section == 'disburse_loan'): ?>
            <div class="review-section">
                <h3>Disburse Loan</h3>
                <?php
                if (isset($_GET['id'])) {
                    $loan_id = $_GET['id'];

                    // Update repayment status to 'disbursed'
                    $sql = "UPDATE loan_applications SET repayment_status = 'disbursed' WHERE id = $loan_id";
                    if ($conn->query($sql) === TRUE) {
                        echo "<p>Loan disbursed successfully!</p>";
                        header("Location: ?section=disburse_loans");
                    } else {
                        echo "<p>Error disbursing loan: " . $conn->error . "</p>";
                    }
                }
                ?>
            </div>

        <?php else: ?>
            <p>Select a section to begin.</p> 
        <?php endif; ?>
    </div>
</div>

</body>
</html>
