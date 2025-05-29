<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$parent_id = $_SESSION["id"];
$message = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: view_children.php");
    exit;
}

$child_id = intval($_GET['id']);

// Fetch child data for editing
$stmt = mysqli_prepare($conn, "SELECT * FROM children WHERE child_id = ? AND parent_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $child_id, $parent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$child = mysqli_fetch_assoc($result);

if (!$child) {
    header("location: view_children.php");
    exit;
}

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

    $update_stmt = mysqli_prepare($conn, "UPDATE children SET 
        first_name=?, last_name=?, date_of_birth=?, gender=?, address=?, city=?, province=?, postal_code=?, special_notes=?, updated_at=NOW() 
        WHERE child_id=? AND parent_id=?");
    mysqli_stmt_bind_param($update_stmt, "sssssssssss", $first_name, $last_name, $dob, $gender, $address, $city, $province, $postal_code, $special_notes, $child_id, $parent_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $message = "Child details updated successfully.";
        // Refresh child data
        $child = [
            "first_name" => $first_name,
            "last_name" => $last_name,
            "date_of_birth" => $dob,
            "gender" => $gender,
            "address" => $address,
            "city" => $city,
            "province" => $province,
            "postal_code" => $postal_code,
            "special_notes" => $special_notes
        ];
    } else {
        $message = "Error updating child details: " . mysqli_error($conn);
    }
    mysqli_stmt_close($update_stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Child Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Edit Child Details</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>First Name:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($child['first_name']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Last Name:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($child['last_name']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Date of Birth:</label>
            <input type="date" name="date_of_birth" value="<?= htmlspecialchars($child['date_of_birth']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Gender:</label>
            <select name="gender" class="form-control" required>
                <option value="">Select</option>
                <option value="Male" <?= $child['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $child['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $child['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Address:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($child['address']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>City:</label>
            <input type="text" name="city" value="<?= htmlspecialchars($child['city']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Province:</label>
            <input type="text" name="province" value="<?= htmlspecialchars($child['province']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Postal Code:</label>
            <input type="text" name="postal_code" value="<?= htmlspecialchars($child['postal_code']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Special Notes (Medical Conditions / Allergies):</label>
            <textarea name="special_notes" class="form-control"><?= htmlspecialchars($child['special_notes']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="view_children.php" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
</div>
</div>

</body>
</html>
