<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search !== "") {
    $search_param = "%".$search."%";
    $stmt = mysqli_prepare($conn, "SELECT * FROM vaccination_centers WHERE name LIKE ? OR location LIKE ? ORDER BY name ASC");
    mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM vaccination_centers ORDER BY name ASC");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vaccination Centers</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Vaccination Centers</h2>

    <form method="get" class="form-inline mb-3">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or location" class="form-control mr-2" style="width: 300px;">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="view_centers.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Center Name</th>
                    <th>Location</th>
                    <th>Contact Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($center = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($center['name']) ?></td>
                        <td><?= htmlspecialchars($center['location']) ?></td>
                        <td><?= htmlspecialchars($center['contact_number']) ?></td>
                        <td>
                            <a href="view_schedules.php?center_id=<?= $center['id'] ?>" class="btn btn-info btn-sm">View Schedules</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No centers found.</p>
    <?php endif; ?>
</div>
</body>
</html>
