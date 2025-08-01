<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
$data = [];
$headers = [];
$filename = '';

// Process report generation
if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($report_type)) {
    switch ($report_type) {
        case 'users':
            $filename = 'users_report_' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'Username', 'Email', 'Phone', 'Age', 'CNIC', 'User Type', 'Status'];
            
            $sql = "SELECT id, username, email, phone, age, cnic, user_type, status FROM users";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    $row['id'],
                    $row['username'],
                    $row['email'],
                    $row['phone'],
                    $row['age'],
                    $row['cnic'],
                    $row['user_type'],
                    $row['status']
                ];
            }
            break;
            
        case 'children':
            $filename = 'children_report_' . date('Y-m-d') . '.csv';
            $headers = ['Child ID', 'First Name', 'Last Name', 'DOB', 'Age', 'Gender', 'Parent ID', 'Parent Username', 'Address', 'City', 'Province', 'Postal Code', 'Special Notes'];
            
            $sql = "SELECT c.*, u.username as parent_username 
                    FROM children c 
                    JOIN users u ON c.parent_id = u.id";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $dob = new DateTime($row['date_of_birth']);
                $now = new DateTime();
                $age = $now->diff($dob)->y;
                
                $data[] = [
                    $row['child_id'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['date_of_birth'],
                    $age,
                    $row['gender'],
                    $row['parent_id'],
                    $row['parent_username'],
                    $row['address'],
                    $row['city'],
                    $row['province'],
                    $row['postal_code'],
                    $row['special_notes']
                ];
            }
            break;
            
        case 'vaccinations':
            $filename = 'vaccinations_report_' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'Child ID', 'Child Name', 'Parent ID', 'Parent Username', 'Worker ID', 'Worker Name', 
                       'Vaccine Date', 'Batch Number', 'Campaign Name', 'Center Name', 'Method', 'Recorded At'];
            
            $sql = "SELECT v.*, 
                    c.first_name as child_first, c.last_name as child_last, c.parent_id,
                    u1.username as parent_username,
                    u2.username as worker_name,
                    s.campaign_name,
                    cen.name as center_name
                    FROM vaccinations v
                    JOIN children c ON v.child_id = c.child_id
                    JOIN users u1 ON c.parent_id = u1.id
                    JOIN users u2 ON v.worker_id = u2.id
                    JOIN vaccination_schedules s ON v.schedule_id = s.id
                    JOIN vaccination_centers cen ON s.center_id = cen.id";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    $row['id'],
                    $row['child_id'],
                    $row['child_first'] . ' ' . $row['child_last'],
                    $row['parent_id'],
                    $row['parent_username'],
                    $row['worker_id'],
                    $row['worker_name'],
                    $row['vaccine_date'],
                    $row['batch_number'],
                    $row['campaign_name'],
                    $row['center_name'],
                    $row['method'],
                    $row['created_at']
                ];
            }
            break;
            
        case 'campaigns':
            $filename = 'campaigns_report_' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'Center Name', 'Center Location', 'Vaccine Date', 'Campaign Name', 'Notes'];
            
            $sql = "SELECT s.id, c.name as center_name, c.location, s.vaccine_date, s.campaign_name, s.notes
                    FROM vaccination_schedules s
                    JOIN vaccination_centers c ON s.center_id = c.id";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    $row['id'],
                    $row['center_name'],
                    $row['location'],
                    $row['vaccine_date'],
                    $row['campaign_name'],
                    $row['notes']
                ];
            }
            break;
            
        case 'unvaccinated':
            $filename = 'unvaccinated_report_' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'Child ID', 'Child Name', 'Worker ID', 'Worker Name', 'Reason', 'Report Date'];
            
            $sql = "SELECT u.*, 
                    c.first_name as child_first, c.last_name as child_last,
                    w.username as worker_name
                    FROM unvaccinated_reports u
                    JOIN children c ON u.child_id = c.child_id
                    JOIN users w ON u.worker_id = w.id";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    $row['id'],
                    $row['child_id'],
                    $row['child_first'] . ' ' . $row['child_last'],
                    $row['worker_id'],
                    $row['worker_name'],
                    $row['reason'],
                    $row['report_date']
                ];
            }
            break;
            
        case 'missed':
            $filename = 'missed_vaccinations_report_' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'Child ID', 'Child Name', 'Reported By ID', 'Reported By', 'Reason', 
                       'Preferred Date', 'Status', 'Reported At'];
            
            $sql = "SELECT m.*, 
                    c.first_name as child_first, c.last_name as child_last,
                    u.username as reported_by_name
                    FROM missed_vaccinations m
                    JOIN children c ON m.child_id = c.child_id
                    JOIN users u ON m.reported_by = u.id";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    $row['id'],
                    $row['child_id'],
                    $row['child_first'] . ' ' . $row['child_last'],
                    $row['reported_by'],
                    $row['reported_by_name'],
                    $row['reason'],
                    $row['preferred_date'],
                    $row['status'],
                    $row['created_at']
                ];
            }
            break;
    }
    
    // Handle CSV export
    if (isset($_GET['export']) && $_GET['export'] == 'csv' && !empty($data)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Reports Generator</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .report-container {
            margin-top: 30px;
        }
        .report-table {
            margin-top: 20px;
        }
        .report-actions {
            margin-bottom: 20px;
        }
        table {
            font-size: 14px;
        }
        th {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>Reports Generator</h2>
        </div>
        <div class="card-body">
            <form method="get" class="mb-4">
                <div class="form-row align-items-end">
                    <div class="col-md-6">
                        <label for="report_type">Select Report Type:</label>
                        <select name="report_type" id="report_type" class="form-control" required>
                            <option value="">-- Select Report --</option>
                            <option value="users" <?= $report_type == 'users' ? 'selected' : '' ?>>All Users</option>
                            <option value="children" <?= $report_type == 'children' ? 'selected' : '' ?>>All Children</option>
                            <option value="vaccinations" <?= $report_type == 'vaccinations' ? 'selected' : '' ?>>Vaccination Records</option>
                            <option value="campaigns" <?= $report_type == 'campaigns' ? 'selected' : '' ?>>Campaigns</option>
                            <option value="unvaccinated" <?= $report_type == 'unvaccinated' ? 'selected' : '' ?>>Unvaccinated Reports</option>
                            <option value="missed" <?= $report_type == 'missed' ? 'selected' : '' ?>>Missed Vaccinations</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                        <?php if (!empty($data)): ?>
                            <a href="?report_type=<?= $report_type ?>&export=csv" class="btn btn-success ml-2">Export as CSV</a>
                            <button type="button" class="btn btn-secondary ml-2" onclick="window.print()">Print Report</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($data)): ?>
                <div class="report-container">
                    <div class="report-actions">
                        <h4><?= ucfirst($report_type) ?> Report</h4>
                        <p class="text-muted">Generated on <?= date('Y-m-d H:i:s') ?></p>
                    </div>
                    
                    <div class="table-responsive report-table">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <?php foreach ($headers as $header): ?>
                                        <th><?= $header ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $cell): ?>
                                            <td><?= htmlspecialchars($cell) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif (!empty($report_type)): ?>
                <div class="alert alert-info">No data found for this report type.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>