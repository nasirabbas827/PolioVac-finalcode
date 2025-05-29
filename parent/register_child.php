<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$parent_id = $_SESSION["id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $dob = $_POST["date_of_birth"];
    $gender = $_POST["gender"];
    $address = $_POST["address"];
    $city = $_POST["city"];
    $province = $_POST["province"];
    $postal_code = $_POST["postal_code"];
    $special_notes = $_POST["special_notes"];

    $sql = "INSERT INTO children 
        (parent_id, first_name, last_name, date_of_birth, gender, address, city, province, postal_code, special_notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssssssss", $parent_id, $first_name, $last_name, $dob, $gender, $address, $city, $province, $postal_code, $special_notes);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Child registered successfully.";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Child</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Register Your Child</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>First Name:</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Last Name:</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Date of Birth:</label>
            <input type="date" name="date_of_birth" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Gender:</label>
            <select name="gender" class="form-control" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Address:</label>
            <input type="text" name="address" class="form-control" required>
        </div>
        <div class="form-group">
            <label>City:</label>
            <input type="text" name="city" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Province:</label>
            <input type="text" name="province" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Postal Code:</label>
            <input type="text" name="postal_code" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Special Notes (Medical Conditions / Allergies):</label>
            <textarea name="special_notes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Register</button>
        <a href="parent_dashboard.php" class="btn btn-secondary ml-2">Back</a>
    </form>
</div>
</div>
</div>

</body>
</html>
