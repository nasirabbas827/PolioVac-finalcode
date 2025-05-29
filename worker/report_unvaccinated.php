<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $child_id = $_POST['child_id'];
    $worker_id = $_POST['worker_id'];
    $reason = $_POST['reason'];
    $report_date = $_POST['report_date'];

    $stmt = mysqli_prepare($conn, "INSERT INTO unvaccinated_reports (child_id, worker_id, reason, report_date) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiss", $child_id, $worker_id, $reason, $report_date);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Unvaccinated report submitted!";
        $_SESSION['message_type'] = "warning";
    } else {
        $_SESSION['message'] = "Failed to submit unvaccinated report.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: view_children.php");
    exit;
}
?>
