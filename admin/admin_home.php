<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Approved user count
$user_status_sql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
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

// Total children
$total_children_sql = "SELECT COUNT(*) as total FROM children";
$total_children_result = mysqli_query($conn, $total_children_sql);
$total_children = mysqli_fetch_assoc($total_children_result)['total'] ?? 0;

// Vaccination centers count
$centers_sql = "SELECT COUNT(*) as total FROM vaccination_centers";
$centers_result = mysqli_query($conn, $centers_sql);
$total_centers = mysqli_fetch_assoc($centers_result)['total'] ?? 0;

// Missed vaccinations count
$missed_sql = "SELECT COUNT(*) as total FROM missed_vaccinations WHERE status = 'pending'";
$missed_result = mysqli_query($conn, $missed_sql);
$total_missed = mysqli_fetch_assoc($missed_result)['total'] ?? 0;

// Unvaccinated children
$unvaccinated_sql = "SELECT COUNT(*) as total FROM children WHERE child_id NOT IN (SELECT DISTINCT child_id FROM vaccinations)";
$unvaccinated_result = mysqli_query($conn, $unvaccinated_sql);
$total_unvaccinated = mysqli_fetch_assoc($unvaccinated_result)['total'] ?? 0;
$total_vaccinated = $total_children - $total_unvaccinated;

// Vaccination trend data (last 6 months)
$trend_sql = "SELECT DATE_FORMAT(vaccine_date, '%Y-%m') as month, COUNT(*) as count 
              FROM vaccinations 
              WHERE vaccine_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
              GROUP BY month 
              ORDER BY month";
$trend_result = mysqli_query($conn, $trend_sql);
$trend_labels = [];
$trend_data = [];
while ($row = mysqli_fetch_assoc($trend_result)) {
    $trend_labels[] = $row['month'];
    $trend_data[] = $row['count'];
}

// Missed vaccination status
$missed_status_sql = "SELECT status, COUNT(*) as count FROM missed_vaccinations GROUP BY status";
$missed_status_result = mysqli_query($conn, $missed_status_sql);
$missed_status_data = ['pending' => 0, 'rescheduled' => 0, 'resolved' => 0];
while ($row = mysqli_fetch_assoc($missed_status_result)) {
    $missed_status_data[$row['status']] = $row['count'];
}

// Recent activity (last 5 vaccinations and missed reports)
$recent_activity_sql = "
    SELECT 'Vaccination' as type, v.created_at, c.first_name, c.last_name, vc.name as center_name
    FROM vaccinations v
    JOIN children c ON v.child_id = c.child_id
    JOIN vaccination_schedules vs ON v.schedule_id = vs.id
    JOIN vaccination_centers vc ON vs.center_id = vc.id
    UNION
    SELECT 'Missed Report' as type, mv.created_at, c.first_name, c.last_name, NULL as center_name
    FROM missed_vaccinations mv
    JOIN children c ON mv.child_id = c.child_id
    ORDER BY created_at DESC LIMIT 5";
$recent_activity_result = mysqli_query($conn, $recent_activity_sql);

// Pending user approvals
$pending_users_sql = "SELECT id, username, email, phone, user_type FROM users WHERE status = 'pending'";
$pending_users_result = mysqli_query($conn, $pending_users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        canvas {
            max-height: 250px;
        }
        .quick-links a {
            margin-right: 10px;
        }
        .card-header {
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2 class="text-center text-primary mb-4">Admin Dashboard</h2>

    <!-- Quick Links -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">Quick Links</div>
        <div class="card-body quick-links">
            <a href="add_center.php" class="btn btn-sm btn-primary">Add Vaccination Center</a>
            <a href="view_centers.php" class="btn btn-sm btn-primary">View Centers</a>
            <a href="view_vaccination_status.php" class="btn btn-sm btn-primary">Vaccination Status</a>
            <a href="view_schedules.php" class="btn btn-sm btn-primary">Manage Schedules</a>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Approved Users</h5>
                    <p class="card-text display-4"><?= $user_status_data['approved'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Vaccinations</h5>
                    <p class="card-text display-4"><?= $total_vaccinations ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Campaigns</h5>
                    <p class="card-text display-4"><?= $total_campaigns ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pending Missed</h5>
                    <p class="card-text display-4"><?= $total_missed ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Children</h5>
                    <p class="card-text display-4"><?= $total_children ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Vaccination Centers</h5>
                    <p class="card-text display-4"><?= $total_centers ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Vaccination Coverage</h5>
                </div>
                <div class="card-body">
                    <canvas id="vaccinationPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Vaccination Trend (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="vaccinationTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Missed Vaccination Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="missedStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <h5>Recent Activity</h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($recent_activity_result) > 0): ?>
                <table class="table table-bordered table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Type</th>
                            <th>Child</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($recent_activity_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['type']) ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><?= $row['type'] === 'Vaccination' ? 'Vaccinated at ' . htmlspecialchars($row['center_name']) : 'Missed vaccination reported' ?></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-3 alert alert-info">No recent activity.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Users -->
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
</div>

<script>
const pieCtx = document.getElementById('vaccinationPieChart').getContext('2d');
new Chart(pieCtx, {
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

const trendCtx = document.getElementById('vaccinationTrendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($trend_labels) ?>,
        datasets: [{
            label: 'Vaccinations',
            data: <?= json_encode($trend_data) ?>,
            borderColor: '#007bff',
            fill: false,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'Vaccination Trend'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

const missedCtx = document.getElementById('missedStatusChart').getContext('2d');
new Chart(missedCtx, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Rescheduled', 'Resolved'],
        datasets: [{
            label: 'Missed Vaccinations',
            data: [<?= $missed_status_data['pending'] ?>, <?= $missed_status_data['rescheduled'] ?>, <?= $missed_status_data['resolved'] ?>],
            backgroundColor: ['#dc3545', '#ffc107', '#28a745']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'Missed Vaccination Status'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>