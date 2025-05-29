<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Approved user count
$user_status_sql = "SELECT status, COUNT(*) as count FROM Users GROUP BY status";
$user_status_result = mysqli_query($conn, $user_status_sql);
$user_status_data = [];
while ($row = mysqli_fetch_assoc($user_status_result)) {
    $user_status_data[$row['status']] = $row['count'];
}

// Vaccination count
$vacc_sql = "SELECT COUNT(*) as total FROM vaccinations";
$vacc_result = mysqli_query($conn, $vacc_sql);
$total_vaccinations = mysqli_fetch_assoc($vacc_result)['total'] ?? 0;

// Campaign count
$camp_sql = "SELECT COUNT(*) as total FROM vaccination_schedules";
$camp_result = mysqli_query($conn, $camp_sql);
$total_campaigns = mysqli_fetch_assoc($camp_result)['total'] ?? 0;

// Pending user approvals
$pending_users_sql = "SELECT id, username, email, phone, user_type FROM users WHERE status = 'pending'";
$pending_users_result = mysqli_query($conn, $pending_users_sql);

// Pie chart data: vaccinated vs unvaccinated
$total_children_sql = "SELECT COUNT(*) as total FROM children";
$total_children_result = mysqli_query($conn, $total_children_sql);
$total_children = mysqli_fetch_assoc($total_children_result)['total'] ?? 0;

$unvaccinated_sql = "SELECT COUNT(*) as total FROM children WHERE child_id NOT IN (SELECT DISTINCT child_id FROM vaccinations)";
$unvaccinated_result = mysqli_query($conn, $unvaccinated_sql);
$total_unvaccinated = mysqli_fetch_assoc($unvaccinated_result)['total'] ?? 0;

$total_vaccinated = $total_children - $total_unvaccinated;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        canvas{
            max-height:250px;
        }

    </style>

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2 class="text-center text-primary mb-4">Admin Dashboard</h2>

    <!-- Dashboard cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Approved Users</h5>
                    <p class="card-text display-4"><?= $user_status_data['approved'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Vaccinations</h5>
                    <p class="card-text display-4"><?= $total_vaccinations ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Campaigns</h5>
                    <p class="card-text display-4"><?= $total_campaigns ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="card mb-5">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Vaccination Coverage</h5>
        </div>
        <div class="card-body">
            <canvas id="vaccinationPieChart"></canvas>
        </div>
    </div>

    <!-- Pending users -->
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <h5>Pending User Approvals</h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($pending_users_result) > 0): ?>
                <table class="table table-bordered table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($pending_users_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['user_type']) ?></td>
                                <td>
                                    <form method="POST" action="approve_user.php" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-3 alert alert-info">No users awaiting approval.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Report Generator -->
    <div class="card mb-5">
        <div class="card-header bg-dark text-white">
            <h5>Generate Reports</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="generate_reports.php" class="form-inline justify-content-center">
                <label class="mr-2" for="report_type">Select Report:</label>
<select name="report_type" id="report_type" class="form-control mr-2">
    <option value="users">All Users</option>
    <option value="children">All Children</option>
    <option value="vaccinations">Vaccination Records</option>
    <option value="campaigns">Campaigns</option>
    <option value="unvaccinated">Unvaccinated Reports</option>
    <option value="missed">Missed Vaccinations</option>
</select>

                <button type="submit" class="btn btn-primary">Download CSV</button>
            </form>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('vaccinationPieChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Vaccinated', 'Unvaccinated'],
        datasets: [{
            data: [<?= $total_vaccinated ?>, <?= $total_unvaccinated ?>],
            backgroundColor: ['#28a745', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'Vaccinated vs Unvaccinated Children'
            }
        }
    }
});
</script>

</body>
</html>
