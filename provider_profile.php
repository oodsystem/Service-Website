<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'service_website');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_number = $_GET['user_number'];

// Fetch provider data based on user number
$stmt = $conn->prepare("SELECT * FROM users WHERE user_number = ?");
$stmt->bind_param("i", $user_number);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();

// Fetch service requests for the provider
$stmt = $conn->prepare("SELECT sr.*, u.first_name, u.last_name, u.email, u.dob, u.user_number FROM service_requests sr JOIN users u ON sr.customer_id = u.user_number WHERE sr.provider_id = ?");
$stmt->bind_param("i", $user_number);
$stmt->execute();
$requests = $stmt->get_result();

// Calculate provider's age
$date_of_birth = new DateTime($provider['dob']);
$age = $date_of_birth->diff(new DateTime('today'))->y;

// Handle request confirmation or decline
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE service_requests SET status = ? WHERE request_id = ?");
    $update_stmt->bind_param("si", $status, $request_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Refresh the page to show updated status
    header("Location: provider_profile.php?user_number=" . $user_number);
    exit();
}

// Fetch service charge for the provider
$stmt = $conn->prepare("SELECT service_charge FROM users WHERE user_number = ?");
$stmt->bind_param("i", $user_number);
$stmt->execute();
$charge_result = $stmt->get_result();
$service_charge = $charge_result->fetch_assoc()['service_charge'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .profile {
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="profile">
        <h2>Welcome, <?= htmlspecialchars($provider['first_name']) ?> <?= htmlspecialchars($provider['last_name']) ?></h2>
        <p><strong>Email:</strong> <?= htmlspecialchars($provider['email']) ?></p>
        <p><strong>Age:</strong> <?= $age ?> years</p>
        <p><strong>Registered Service Type:</strong> <?= htmlspecialchars($provider['service_type']) ?></p>
        <p><strong>Service Charge:</strong> $<?= htmlspecialchars($service_charge) ?></p>

        <h3>Your Service Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($request = $requests->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></td>
                    <td><?= htmlspecialchars($request['phone_number']) ?></td>
                    <td><?= htmlspecialchars($request['add_ress']) ?></td>
                    <td><?= htmlspecialchars($request['status']) ?></td>
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                            <select name="status" required>
                                <option value="Confirmed" <?= $request['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirm</option>
                                <option value="Declined" <?= $request['status'] == 'Declined' ? 'selected' : '' ?>>Decline</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>