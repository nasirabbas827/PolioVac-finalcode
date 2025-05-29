<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

if (!isset($_GET['child_id']) || !is_numeric($_GET['child_id'])) {
    die("Invalid child ID.");
}

$child_id = intval($_GET['child_id']);

// Fetch child info
$sql_child = "SELECT * FROM children WHERE child_id = $child_id LIMIT 1";
$result_child = mysqli_query($conn, $sql_child);
if (!$result_child || mysqli_num_rows($result_child) == 0) {
    die("Child not found.");
}
$child = mysqli_fetch_assoc($result_child);

// Fetch all vaccination records for this child with joined data
$sql_vaccinations = "
    SELECT v.*, 
           vs.campaign_name, vs.vaccine_date AS schedule_date,
           vc.name AS center_name, vc.location AS center_location, vc.contact_number AS center_contact,
           u.username AS worker_name, u.email AS worker_email, u.phone AS worker_phone
    FROM vaccinations v
    JOIN vaccination_schedules vs ON v.schedule_id = vs.id
    JOIN vaccination_centers vc ON vs.center_id = vc.id
    JOIN users u ON v.worker_id = u.id
    WHERE v.child_id = $child_id
    ORDER BY v.vaccine_date DESC
";
$result_vaccinations = mysqli_query($conn, $sql_vaccinations);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vaccination Certificate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f5f8fa;
        }
        .certificate-container {
            max-width: 960px;
            margin: 40px auto;
            padding: 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .certificate-header {
            border-bottom: 2px solid #007bff;
            margin-bottom: 30px;
        }
        .certificate-header h2 {
            font-weight: 600;
        }
        .section-title {
            margin-top: 30px;
            font-weight: 600;
            color: #343a40;
        }
        .table th {
            background-color: #f1f1f1;
        }
        .btn-back {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container certificate-container">
    <div class="certificate-header text-center">
        <h2>Vaccination Certificate</h2>
        <p class="text-muted">Comprehensive Vaccination Record for Child</p>
    </div>

    <h5 class="section-title">Child Information</h5>
    <table class="table table-bordered">
        <tr>
            <th>Full Name</th>
            <td><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td><?= htmlspecialchars($child['date_of_birth']) ?></td>
        </tr>
        <tr>
            <th>Gender</th>
            <td><?= htmlspecialchars($child['gender']) ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= htmlspecialchars($child['address']) ?>, <?= htmlspecialchars($child['city']) ?>,
                <?= htmlspecialchars($child['province']) ?> - <?= htmlspecialchars($child['postal_code']) ?></td>
        </tr>
        <?php if (!empty($child['special_notes'])): ?>
            <tr>
                <th>Special Notes</th>
                <td><?= nl2br(htmlspecialchars($child['special_notes'])) ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <h5 class="section-title">Vaccination Records</h5>
    <?php if ($result_vaccinations && mysqli_num_rows($result_vaccinations) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Vaccination Date</th>
                    <th>Batch #</th>
                    <th>Method</th>
                    <th>Campaign</th>
                    <th>Schedule Date</th>
                    <th>Center</th>
                    <th>Location</th>
                    <th>Worker</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($vacc = mysqli_fetch_assoc($result_vaccinations)): ?>
                    <tr>
                        <td><?= htmlspecialchars($vacc['vaccine_date']) ?></td>
                        <td><?= htmlspecialchars($vacc['batch_number']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($vacc['method'])) ?></td>
                        <td><?= htmlspecialchars($vacc['campaign_name']) ?></td>
                        <td><?= htmlspecialchars($vacc['schedule_date']) ?></td>
                        <td><?= htmlspecialchars($vacc['center_name']) ?> <br>
                            <small><?= htmlspecialchars($vacc['center_contact']) ?></small></td>
                        <td><?= htmlspecialchars($vacc['center_location']) ?></td>
                        <td><?= htmlspecialchars($vacc['worker_name']) ?><br>
                            <small><?= htmlspecialchars($vacc['worker_email']) ?> <br>
                                <?= htmlspecialchars($vacc['worker_phone']) ?></small>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No vaccination records found for this child.</div>
    <?php endif; ?>

    <div class="text-center btn-back">
        <a href="parent_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        <a href="" onclick="window.print()" class="btn btn-dark">Print</a>
    </div>
</div>

</body>
</html>
