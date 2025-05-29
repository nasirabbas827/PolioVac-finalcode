<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $child_id = $_POST['child_id'];
    $worker_id = $_POST['worker_id'];
    $vaccine_date = $_POST['vaccine_date'];
    $batch_number = $_POST['batch_number'];
    $schedule_id = $_POST['schedule_id'];
    $method = $_POST['method'];

    $stmt = mysqli_prepare($conn, "INSERT INTO vaccinations (child_id, worker_id, vaccine_date, batch_number, schedule_id, method, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "iissis", $child_id, $worker_id, $vaccine_date, $batch_number, $schedule_id, $method);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Vaccination marked successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to mark vaccination.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: view_children.php");
    exit;
}
?>
