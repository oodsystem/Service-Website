<?php
$conn = new mysqli('localhost', 'root', '', 'service_website');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $provider_id = $_POST['provider_id'] ?? '';
    $customer_id = $_POST['customer_id'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $add_ress = $_POST['add_ress'] ?? '';

    // Validate required fields
    if (!empty($phone_number) && !empty($add_ress) && !empty($customer_id) && !empty($provider_id)) {
        // Check if customer exists
        $checkCustomer = $conn->prepare("SELECT * FROM users WHERE user_number = ?");
        $checkCustomer->bind_param("i", $customer_id);
        $checkCustomer->execute();
        $result = $checkCustomer->get_result();

        if ($result->num_rows > 0) {
            // Insert service request into the database
            $stmt = $conn->prepare("INSERT INTO service_requests (provider_id, customer_id, service_type, phone_number, add_ress, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("iisss", $provider_id, $customer_id, $service_type, $phone_number, $add_ress);

            if ($stmt->execute()) {
                $success_message = "Service request submitted successfully.";
            } else {
                $error_message = "Error executing query: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Customer ID does not exist in the users table.";
        }
        $checkCustomer->close();
    } else {
        $error_message = "All fields (Phone number and address) are required.";
    }
}

// Prepopulate provider and customer info from hidden inputs or GET parameters
$provider_id = $_POST['provider_id'] ?? $_GET['provider_id'] ?? '';
$customer_id = $_POST['customer_id'] ?? $_GET['customer_id'] ?? '';
$service_type = $_POST['service_type'] ?? $_GET['service_type'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Service</title>
</head>
<body>
    <h2>Request Service</h2>
    
    <?php if (isset($success_message)): ?>
        <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="provider_id" value="<?= htmlspecialchars($provider_id) ?>">
        <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer_id) ?>">
        <input type="hidden" name="service_type" value="<?= htmlspecialchars($service_type) ?>">

        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number" required>
        <br><br>

        <label for="add_ress">Address:</label>
        <input type="text" id="add_ress" name="add_ress" required>
        <br><br>

        <button type="submit">Submit Request</button>
    </form>
</body>
</html>

<?php
$conn->close();
?>