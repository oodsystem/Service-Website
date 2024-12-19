<?php 
$conn = new mysqli('localhost', 'root', '', 'service_website');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle sign-up
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];
    $service_type = ($user_type == 'provider') ? $_POST['service_type'] : NULL;

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "User already exists with this email.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, dob, password, user_type, service_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $dob, $password, $user_type, $service_type);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Redirect based on user type
            if ($user_type == 'customer') {
                header("Location: customer_profile.php?user_number=" . $stmt->insert_id);
            } else {
                header("Location: provider_profile.php?user_number=" . $stmt->insert_id);
            }
            exit();
        } else {
            echo "Error: Unable to sign up.";
        }
    }
}

// Handle sign-in
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin'])) {
    $user_number = $_POST['user_number'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_number = ?");
    $stmt->bind_param("i", $user_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Redirect based on user type
            if ($user['user_type'] == 'customer') {
                header("Location: customer_profile.php?user_number=" . $user['user_number']);
            } else {
                header("Location: provider_profile.php?user_number=" . $user['user_number']);
            }
            exit();
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "User not found!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Website</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { max-width: 400px; margin: 20px auto; padding: 10px; border: 1px solid #ccc; }
        label { display: block; margin: 10px 0 5px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<h2>Sign Up</h2>
<form action="" method="POST">
    <input type="hidden" name="signup" value="1">
    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" required>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="dob">Date of Birth:</label>
    <input type="date" id="dob" name="dob" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <label for="user_type">User Type:</label>
    <select id="user_type" name="user_type" required onchange="toggleServiceType(this.value)">
        <option value="customer">Customer</option>
        <option value="provider">Service Provider</option>
    </select>

    <div id="service_type_section" style="display: none;">
        <label for="service_type">Service Type:</label>
        <select id="service_type" name="service_type">
            <option value="AC_fixing">AC Fixing</option>
            <option value="Computer_fixing">Computer Fixing</option>
            <option value="Electrician">Electrician</option>
        </select>
    </div>

    <button type="submit">Sign Up</button>
</form>

<h2>Sign In</h2>
<form action="" method="POST">
    <input type="hidden" name="signin" value="1">
    <label for="user_number">User Number:</label>
    <input type="number" id="user_number" name="user_number" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Sign In</button>
</form>

<script>
    function toggleServiceType(userType) {
        const serviceTypeSection = document.getElementById('service_type_section');
        serviceTypeSection.style.display = userType === 'provider' ? 'block' : 'none';
    }
</script>

</body>
</html>