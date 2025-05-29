<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$worker_id = $_SESSION["id"];

// Fetch children
$children = mysqli_query($conn, "SELECT * FROM children ORDER BY created_at DESC");

// Fetch campaigns with center name
$campaigns = mysqli_query($conn, "
    SELECT 
        vs.id, 
        vs.campaign_name, 
        vs.vaccine_date, 
        vc.name AS center_name 
    FROM vaccination_schedules vs
    JOIN vaccination_centers vc ON vs.center_id = vc.id
    ORDER BY vs.vaccine_date DESC
");
$campaigns_array = [];
while ($row = mysqli_fetch_assoc($campaigns)) {
    $campaigns_array[] = $row;
}

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
<html>
<head>
    <title>View Children</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .modal-header { background-color: #007bff; color: white; }
        small { display: block; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Children Records</h2>

    <!-- Session Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span>&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <?php if (mysqli_num_rows($children) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Child Name</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>City</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($child = mysqli_fetch_assoc($children)): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?>
                            <p><small>Last Vaccinated: <?= isset($last_vaccinated[$child['child_id']]) ? htmlspecialchars($last_vaccinated[$child['child_id']]) : 'N/A' ?></small></p>
                            <p><small>Last Unvaccinated Report: <?= isset($last_reported[$child['child_id']]) ? htmlspecialchars($last_reported[$child['child_id']]) : 'N/A' ?></small></p>
                        </td>
                        <td><?= htmlspecialchars($child['date_of_birth']) ?></td>
                        <td><?= htmlspecialchars($child['gender']) ?></td>
                        <td><?= htmlspecialchars($child['city']) ?></td>
                        <td>
                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#vaccinateModal<?= $child['child_id'] ?>">Mark Vaccinated</button>
                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#unvaccinatedModal<?= $child['child_id'] ?>">Report Unvaccinated</button>
                        </td>
                    </tr>

                    <!-- Vaccinate Modal -->
                    <div class="modal fade" id="vaccinateModal<?= $child['child_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="post" action="mark_vaccinated.php">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Mark as Vaccinated - <?= htmlspecialchars($child['first_name']) ?></h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="child_id" value="<?= $child['child_id'] ?>">
                                        <input type="hidden" name="worker_id" value="<?= $worker_id ?>">

                                        <div class="form-group">
                                            <label>Vaccine Date</label>
                                            <input type="date" name="vaccine_date" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Batch Number</label>
                                            <input type="text" name="batch_number" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Schedule/Campaign</label>
                                            <select name="schedule_id" class="form-control" required>
                                                <option value="">-- Select Campaign --</option>
                                                <?php foreach ($campaigns_array as $row): ?>
                                                    <option value="<?= $row['id'] ?>">
                                                        <?= htmlspecialchars($row['campaign_name']) ?> - <?= htmlspecialchars($row['vaccine_date']) ?> @ <?= htmlspecialchars($row['center_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Method</label>
                                            <select name="method" class="form-control" required>
                                                <option value="center">Center</option>
                                                <option value="home">Home</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Submit</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Unvaccinated Modal -->
                    <div class="modal fade" id="unvaccinatedModal<?= $child['child_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="post" action="report_unvaccinated.php">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger">
                                        <h5 class="modal-title">Report Unvaccinated - <?= htmlspecialchars($child['first_name']) ?></h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="child_id" value="<?= $child['child_id'] ?>">
                                        <input type="hidden" name="worker_id" value="<?= $worker_id ?>">

                                        <div class="form-group">
                                            <label>Reason (Refusal/Absent/etc.)</label>
                                            <input type="text" name="reason" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Report Date</label>
                                            <input type="date" name="report_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Submit Report</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

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
