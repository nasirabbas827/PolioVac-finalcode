<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];
$email = htmlspecialchars($_SESSION["email"]);

// Fetch children
$children = mysqli_query($conn, "SELECT * FROM children ORDER BY created_at DESC");

// Fetch last vaccinated date per child
$last_vaccinated = [];
$result = mysqli_query($conn, "
    SELECT child_id, MAX(vaccine_date) as last_vaccine_date 
    FROM vaccinations 
    GROUP BY child_id
");
while ($row = mysqli_fetch_assoc($result)) {
    $last_vaccinated[$row['child_id']] = $row['last_vaccine_date'];
}

// Fetch last unvaccinated report date per child
$last_reported = [];
$result = mysqli_query($conn, "
    SELECT child_id, MAX(report_date) as last_report_date 
    FROM unvaccinated_reports 
    GROUP BY child_id
");
while ($row = mysqli_fetch_assoc($result)) {
    $last_reported[$row['child_id']] = $row['last_report_date'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Worker Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h2 class="mb-4">Welcome, <?= $email ?></h2>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <a href="view_missed_vaccinations.php" class="btn btn-success btn-block">View Missed Vaccinations</a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="view_children.php" class="btn btn-primary btn-block">View Children</a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="reports.php" class="btn btn-warning btn-block">Generate Reports</a>
            </div>
        </div>

        <h4>Children Records</h4>
        <?php if (mysqli_num_rows($children) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Child Name</th>
                        <th>DOB</th>
                        <th>Gender</th>
                        <th>City</th>
                        <th>Last Vaccinated</th>
                        <th>Last Unvaccinated Report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($child = mysqli_fetch_assoc($children)): ?>
                        <tr>
                            <td><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></td>
                            <td><?= htmlspecialchars($child['date_of_birth']) ?></td>
                            <td><?= htmlspecialchars($child['gender']) ?></td>
                            <td><?= htmlspecialchars($child['city']) ?></td>
                            <td><?= isset($last_vaccinated[$child['child_id']]) ? htmlspecialchars($last_vaccinated[$child['child_id']]) : 'N/A' ?></td>
                            <td><?= isset($last_reported[$child['child_id']]) ? htmlspecialchars($last_reported[$child['child_id']]) : 'N/A' ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No children records found.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
