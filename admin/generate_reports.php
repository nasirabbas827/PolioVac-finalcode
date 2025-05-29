<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

function export_csv($filename, $headers, $rows) {
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename.csv\"");
    $output = fopen("php://output", "w");
    fputcsv($output, $headers);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $report_type = $_POST['report_type'];
    $query = "";
    $headers = [];

    switch ($report_type) {
        case 'users':
            $query = "SELECT id, username, email, phone, user_type, status FROM users";
            $headers = ['ID', 'Username', 'Email', 'Phone', 'User Type', 'Status'];
            break;

        case 'children':
            $query = "SELECT child_id, parent_id, first_name, last_name, gender, city, province FROM children";
            $headers = ['Child ID', 'Parent ID', 'First Name', 'Last Name', 'Gender', 'City', 'Province'];
            break;

        case 'vaccinations':
            $query = "SELECT id, child_id, worker_id, vaccine_date, batch_number, method FROM vaccinations";
            $headers = ['Vaccination ID', 'Child ID', 'Worker ID', 'Vaccine Date', 'Batch No', 'Method'];
            break;

        case 'campaigns':
            $query = "SELECT id, center_id, vaccine_date, campaign_name FROM vaccination_schedules";
            $headers = ['Schedule ID', 'Center ID', 'Vaccine Date', 'Campaign Name'];
            break;

        case 'unvaccinated':
            $query = "SELECT id, child_id, worker_id, reason, report_date FROM unvaccinated_reports";
            $headers = ['Report ID', 'Child ID', 'Worker ID', 'Reason', 'Report Date'];
            break;

        case 'missed':
            $query = "SELECT id, child_id, reported_by, reason, preferred_date, status FROM missed_vaccinations";
            $headers = ['Missed ID', 'Child ID', 'Reported By', 'Reason', 'Preferred Date', 'Status'];
            break;

        default:
            exit("Invalid report type.");
    }

    $result = mysqli_query($conn, $query);
    if (!$result) {
        exit("Query failed: " . mysqli_error($conn));
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    export_csv($report_type . "_report", $headers, $rows);
}
?>
