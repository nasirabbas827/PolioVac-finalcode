<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$worker_id = $_SESSION["id"];

$start_date = $end_date = "";
$report_data = [];
$error = "";

function generateReportData($conn, $start_date, $end_date) {
    // Get vaccinated counts per day
    $vaccinated_query = "
        SELECT vaccine_date, COUNT(*) as vaccinated_count 
        FROM vaccinations
        WHERE vaccine_date BETWEEN ? AND ?
        GROUP BY vaccine_date
    ";
    $stmt = mysqli_prepare($conn, $vaccinated_query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $vaccinated_counts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $vaccinated_counts[$row['vaccine_date']] = $row['vaccinated_count'];
    }
    mysqli_stmt_close($stmt);

    // Total children count
    $total_children_query = "SELECT COUNT(*) as total_count FROM children";
    $total_result = mysqli_query($conn, $total_children_query);
    $total_children_row = mysqli_fetch_assoc($total_result);
    $total_children = $total_children_row['total_count'];

    $report_data = [];
    $period = new DatePeriod(
        new DateTime($start_date),
        new DateInterval('P1D'),
        (new DateTime($end_date))->modify('+1 day')
    );

    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $vaccinated = $vaccinated_counts[$date_str] ?? 0;
        $pending = $total_children - $vaccinated;

        $report_data[] = [
            'date' => $date_str,
            'vaccinated' => $vaccinated,
            'pending' => $pending
        ];
    }
    return $report_data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (!$start_date || !$end_date) {
        $error = "Please select both start and end dates.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be later than end date.";
    } else {
        $report_data = generateReportData($conn, $start_date, $end_date);
    }
} else {
    // Default: last 7 days report on page load
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-2 days'));
    $report_data = generateReportData($conn, $start_date, $end_date);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vaccination Reports</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Vaccination Reports</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-inline no-print mb-4">
    <div class="form-group mr-2">
        <label for="start_date" class="mr-2">Start Date:</label>
        <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
    </div>
    <div class="form-group mr-2">
        <label for="end_date" class="mr-2">End Date:</label>
        <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Generate Report</button>
    <?php if ($report_data): ?>
        <button type="button" class="btn btn-secondary ml-2" onclick="window.print()">Print</button>
    <?php endif; ?>
</form>


    <?php if ($report_data): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Vaccinated Cases</th>
                    <th>Pending Cases</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['vaccinated']) ?></td>
                        <td><?= htmlspecialchars($row['pending']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
        <p>No data found for selected dates.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
