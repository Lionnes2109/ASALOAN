<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include database connection
include 'db.php';

// Validate user session
function validateBorrowerSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    }

    if ($_SESSION['role'] !== 'borrower') {
        header("Location: index.php");
        exit;
    }
}

// Validate borrower session
validateBorrowerSession();

// Initialize variables
$error = '';
$success = '';

// Process loan application
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Detailed logging of input
    error_log("Comprehensive Loan Application Attempt - User ID: " . $_SESSION['user_id']);

    // Sanitize and validate inputs
// Sanitize and validate inputs
$loan_amount = filter_input(INPUT_POST, 'loan_amount', FILTER_VALIDATE_FLOAT);
$loan_purpose = filter_input(INPUT_POST, 'loan_purpose', FILTER_SANITIZE_SPECIAL_CHARS);
$first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
$last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
$middle_name = filter_input(INPUT_POST, 'middle_name', FILTER_SANITIZE_SPECIAL_CHARS);
$birthdate = filter_input(INPUT_POST, 'birthdate', FILTER_SANITIZE_SPECIAL_CHARS);
$gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS);
$civil_status = filter_input(INPUT_POST, 'civil_status', FILTER_SANITIZE_SPECIAL_CHARS);
$mobile_number = filter_input(INPUT_POST, 'mobile_number', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
$province = filter_input(INPUT_POST, 'province', FILTER_SANITIZE_SPECIAL_CHARS);
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS);
$barangay = filter_input(INPUT_POST, 'barangay', FILTER_SANITIZE_SPECIAL_CHARS);
$employment_type = filter_input(INPUT_POST, 'employment_type', FILTER_SANITIZE_SPECIAL_CHARS);
$monthly_income = filter_input(INPUT_POST, 'monthly_income', FILTER_VALIDATE_FLOAT);

    // Validate inputs
    $validation_errors = [];

    if ($loan_amount === false || $loan_amount <= 0) {
        $validation_errors[] = "Invalid loan amount. Please enter a positive number.";
    }
    if (empty($loan_purpose)) {
        $validation_errors[] = "Loan purpose is required.";
    }
    if (empty($first_name)) {
        $validation_errors[] = "First name is required.";
    }
    if (empty($last_name)) {
        $validation_errors[] = "Last name is required.";
    }
    if (empty($birthdate)) {
        $validation_errors[] = "Birthdate is required.";
    }
    if (empty($gender)) {
        $validation_errors[] = "Gender is required.";
    }
    if (empty($civil_status)) {
        $validation_errors[] = "Civil status is required.";
    }
    if (empty($mobile_number)) {
        $validation_errors[] = "Mobile number is required.";
    }
    if ($email === false) {
        $validation_errors[] = "Invalid email address.";
    }
    if (empty($address)) {
        $validation_errors[] = "Address is required.";
    }
    if (empty($province)) {
        $validation_errors[] = "Province is required.";
    }
    if (empty($city)) {
        $validation_errors[] = "City is required.";
    }
    if (empty($barangay)) {
        $validation_errors[] = "Barangay is required.";
    }
    if (empty($employment_type)) {
        $validation_errors[] = "Employment type is required.";
    }
    if ($monthly_income === false || $monthly_income <= 0) {
        $validation_errors[] = "Invalid monthly income.";
    }

    // If no validation errors, proceed with application
    if (empty($validation_errors)) {
        try {
            // Verify database connection
            if (!$conn) {
                throw new Exception("Database connection is not established.");
            }

            // Prepare SQL statement for comprehensive loan application
            $sql = "INSERT INTO loan_applications (
                        borrower_id, 
                        first_name, 
                        last_name, 
                        middle_name, 
                        birthdate, 
                        gender, 
                        civil_status, 
                        mobile_number, 
                        email, 
                        address, 
                        province, 
                        city, 
                        barangay, 
                        employment_type, 
                        monthly_income, 
                        amount, 
                        purpose, 
                        status, 
                        created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW()
                    )";
            
            // Prepare statement
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                error_log("Prepare statement failed: " . $conn->error);
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            // Bind parameters
            $borrower_id = $_SESSION['user_id'];
            $stmt->bind_param(
                "issssssssssssssds", // Make sure types correspond to the variables
                $borrower_id, 
                $first_name, 
                $last_name, 
                $middle_name, 
                $birthdate, 
                $gender, 
                $civil_status, 
                $mobile_number, 
                $email, 
                $address, 
                $province, 
                $city, 
                $barangay, 
                $employment_type, 
                $monthly_income, 
                $loan_amount, 
                $loan_purpose
            );
            
            // Execute statement
            if ($stmt->execute()) {
                $success = "Loan application submitted successfully!";
                error_log("Comprehensive Loan Application Successful - User ID: $borrower_id");
            } else {
                error_log("Execute failed: " . $stmt->error);
                throw new Exception("Failed to submit loan application: " . $stmt->error);
            }
            
            // Close statement
            $stmt->close();
        } catch (Exception $e) {
            // Log full error details
            error_log("Loan Application Error: " . $e->getMessage());
            
            // User-friendly error
            $error = "An error occurred while submitting your application. Please try again.";
            
            // Log to file for support debugging
            file_put_contents('loan_application_errors.log', 
                date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 
                FILE_APPEND
            );
        }
    } else {
        // Collect validation errors
        $error = implode("<br>", $validation_errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Loan Application</title>
    <style>
        :root {
            --primary-color: #007BFF;
            --background-light: #f4f4f4;
            --text-color: #333;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .loan-application-container {
            background-color: var(--background-light);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row > .form-group {
            flex: 1;
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .submit-btn:hover {
            background-color: #0056b3;
        }
        
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .nav-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary-color);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="loan-application-container">
        <h2>Comprehensive Loan Application</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <h3>Loan Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="loan_amount">Loan Amount (₱)</label>
                    <input type="number" id="loan_amount" name="loan_amount" min="1000" max="500000" step="0.01" required 
                           placeholder="Enter loan amount">
                </div>
                <div class="form-group">
                    <label for="loan_purpose">Loan Purpose</label>
                    <select id="loan_purpose" name="loan_purpose" required>
                        <option value="">Select Loan Purpose</option>
                        <option value="Business">Business Loan</option>
                        <option value="Personal">Personal Loan</option>
                        <option value="Education">Education Loan</option>
                        <option value="Home Improvement">Home Improvement</option>
                        <option value="Agricultural">Agricultural Loan</option>
                        <option value="Vehicle">Vehicle Loan</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <h3>Personal Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required placeholder="Enter first name">
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" placeholder="Enter middle name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required placeholder="Enter last name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="civil_status">Civil Status</label>
                    <select id="civil_status" name="civil_status" required>
                        <option value="">Select Civil Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>
            </div>

            <h3>Contact Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="mobile_number">Mobile Number</label>
                    <input type="text" id="mobile_number" name="mobile_number" required 
                           placeholder="Enter mobile number (09xxxxxxxxx)" 
                           pattern="^(09\d{9})$">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter email address">
                </div>
            </div>

            <h3>Address Details</h3>
            <div class="form-group">
                <label for="address">Complete Address</label>
                <input type="text" id="address" name="address" required 
                       placeholder="House/Unit No., Street, Subdivision">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province" required placeholder="Enter province">
                </div>
                <div class="form-group">
                    <label for="city">City/Municipality</label>
                    <input type="text" id="city" name="city" required placeholder="Enter city">
                </div>
                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <input type="text" id="barangay" name="barangay" required placeholder="Enter barangay">
                </div>
            </div>

            <h3>Employment Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="employment_type">Employment Type</label>
                    <select id="employment_type" name="employment_type" required>
                        <option value="">Select Employment Type</option>
                        <option value="Employed">Employed</option>
                        <option value="Self-Employed">Self-Employed</option>
                        <option value="Business Owner">Business Owner</option>
                        <option value="Overseas Worker">Overseas Worker</option>
                        <option value="Unemployed">Unemployed</option>
                        <option value="Retired">Retired</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="monthly_income">Monthly Income (₱)</label>
                    <input type="number" id="monthly_income" name="monthly_income" min="0" step="0.01" 
                           required placeholder="Enter monthly income">
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Submit Loan Application</button>
        </form>
        
        <a href="borrower_dashboard.php" class="nav-link">Back to Dashboard</a>
    </div>
</body>
</html>
