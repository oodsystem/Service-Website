<?php
$conn = new mysqli('localhost', 'root', '', 'service_website');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_number = $_GET['user_number'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_number = ?");
$stmt->bind_param("i", $user_number);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$dob = new DateTime($user['dob']);
$now = new DateTime();
$age = $now->diff($dob)->y;

// Fetch service requests for the customer
$stmt = $conn->prepare("SELECT sr.*, u.first_name, u.last_name FROM service_requests sr JOIN users u ON sr.provider_id = u.user_number WHERE sr.customer_id = ?");
$stmt->bind_param("i", $user_number);
$stmt->execute();
$requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
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
        <h2>Welcome, <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h2>
        <p><strong>Age:</strong> <?= $age ?> years old</p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

        <h3>Your Service Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Provider Name</th>
                    <th>Service Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($request = $requests->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></td>
                    <td><?= htmlspecialchars($request['service_type']) ?></td>
                    <td><?= htmlspecialchars($request['status']) ?></td> <!-- Assuming status is a column in your table -->
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <h3>Show Services</h3>
        <form action="" method="POST">
            <select name="service_type" required>
                <option value="">Select a service</option>
                <option value="AC_fixing">AC Fixing</option>
                <option value="Computer_fixing">Computer Fixing</option>
                <option value="Electrician">Electrician</option>
            </select>
            <button type="submit">Show Providers</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['service_type'])) {
            $service_type = $_POST['service_type'];

            $stmt = $conn->prepare("SELECT * FROM users WHERE user_type = 'provider' AND service_type = ? AND availability_status = 'available'");
            $stmt->bind_param("s", $service_type);
            $stmt->execute();
            $providers = $stmt->get_result();
            ?>

            <h4>Available Providers for <?= htmlspecialchars($service_type) ?>:</h4>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Service Charge</th>
                        <th>Rating</th>
                        <th>Request Service</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($provider = $providers->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) ?></td>
                        <td><?= htmlspecialchars($provider['service_charge']) ?> $</td>
                        <td><?= htmlspecialchars($provider['rating']) ?></td>
                        <td>
                            <form action="request_service.php" method="POST">
                                <input type="hidden" name="provider_id" value="<?= $provider['user_number'] ?>">
                                <input type="hidden" name="customer_id" value="<?= $user['user_number'] ?>">
                                <input type="hidden" name="service_type" value="<?= $service_type ?>">
                                <button type="submit">Request</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

        <?php
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>