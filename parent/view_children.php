<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$parent_id = $_SESSION["id"];
$message = "";

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Ensure the child belongs to this parent for security
    $stmt = mysqli_prepare($conn, "DELETE FROM children WHERE child_id = ? AND parent_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $delete_id, $parent_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Child record deleted successfully.";
    } else {
        $message = "Failed to delete child record.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch children of logged-in parent
$stmt = mysqli_prepare($conn, "SELECT * FROM children WHERE parent_id = ?");
mysqli_stmt_bind_param($stmt, "i", $parent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Registered Children</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <script>
    function confirmDelete(child_id) {
        if (confirm("Are you sure you want to delete this child record?")) {
            window.location.href = "view_children.php?delete_id=" + child_id;
        }
    }
    </script>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Your Registered Children</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <a href="register_child.php" class="btn btn-primary mb-3">Register New Child</a>

    <?php if (mysqli_num_rows($result) > 0): ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>D.O.B</th>
                <th>Gender</th>
                <th>City</th>
                <th>Province</th>
                <th>Postal Code</th>
                <th>Special Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($child = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($child['first_name']) ?></td>
                    <td><?= htmlspecialchars($child['last_name']) ?></td>
                    <td><?= htmlspecialchars($child['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($child['gender']) ?></td>
                    <td><?= htmlspecialchars($child['city']) ?></td>
                    <td><?= htmlspecialchars($child['province']) ?></td>
                    <td><?= htmlspecialchars($child['postal_code']) ?></td>
                    <td><?= htmlspecialchars($child['special_notes']) ?></td>
                    <td>
                        <a href="edit_child.php?id=<?= $child['child_id'] ?>" class="mb-2 btn btn-sm btn-warning">Edit</a>
                        <button onclick="confirmDelete(<?= $child['child_id'] ?>)" class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No children registered yet.</p>
    <?php endif; ?>

</div>
</body>
</html>
