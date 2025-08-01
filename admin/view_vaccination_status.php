<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Search functionality
$search = "";
$children = [];
$vaccination_status = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search"])) {
    $search = trim($_GET["search"]);
    
    if (!empty($search)) {
        // Search for children matching name or ID
        $sql = "SELECT c.*, u.username as parent_username 
                FROM children c 
                JOIN users u ON c.parent_id = u.id 
                WHERE c.first_name LIKE ? OR c.last_name LIKE ? OR c.child_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        $search_param = "%$search%";
        mysqli_stmt_bind_param($stmt, "ssi", $search_param, $search_param, $search);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $children[] = $row;
        }
        
        // Get vaccination status for each child found
        foreach ($children as $child) {
            $child_id = $child['child_id'];
            
            // Get all vaccination records for this child
            $sql_vaccinations = "SELECT v.*, s.vaccine_date as scheduled_date, s.campaign_name, 
                                c.name as center_name, u.username as worker_name
                                FROM vaccinations v
                                JOIN vaccination_schedules s ON v.schedule_id = s.id
                                JOIN vaccination_centers c ON s.center_id = c.id
                                JOIN users u ON v.worker_id = u.id
                                WHERE v.child_id = ?";
            $stmt_v = mysqli_prepare($conn, $sql_vaccinations);
            mysqli_stmt_bind_param($stmt_v, "i", $child_id);
            mysqli_stmt_execute($stmt_v);
            $vaccinations = mysqli_stmt_get_result($stmt_v)->fetch_all(MYSQLI_ASSOC);
            
            // Get missed vaccinations
            $sql_missed = "SELECT m.*, u.username as reported_by_name
                           FROM missed_vaccinations m
                           JOIN users u ON m.reported_by = u.id
                           WHERE m.child_id = ?";
            $stmt_m = mysqli_prepare($conn, $sql_missed);
            mysqli_stmt_bind_param($stmt_m, "i", $child_id);
            mysqli_stmt_execute($stmt_m);
            $missed = mysqli_stmt_get_result($stmt_m)->fetch_all(MYSQLI_ASSOC);
            
            // Calculate age
            $dob = new DateTime($child['date_of_birth']);
            $now = new DateTime();
            $age = $now->diff($dob)->y;
            
            $vaccination_status[$child_id] = [
                'child_info' => $child,
                'age' => $age,
                'vaccinations' => $vaccinations,
                'missed' => $missed,
                'total_doses' => count($vaccinations),
                'missed_count' => count($missed)
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Vaccination Status</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .status-card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .vaccination-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .vaccination-item:last-child {
            border-bottom: none;
        }
        .badge-completed {
            background-color: #28a745;
        }
        .badge-missed {
            background-color: #dc3545;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>Child Vaccination Status</h2>
        </div>
        <div class="card-body">
            <form method="get" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by child name or ID" value="<?= htmlspecialchars($search) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($search) && empty($children)): ?>
                <div class="alert alert-warning">No children found matching your search.</div>
            <?php endif; ?>
            
            <?php foreach ($vaccination_status as $status): 
                $child = $status['child_info'];
                $vaccinations = $status['vaccinations'];
                $missed = $status['missed'];
            ?>
                <div class="card status-card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></h4>
                            <div>
                                <span class="badge badge-primary">ID: <?= $child['child_id'] ?></span>
                                <span class="badge badge-info">Age: <?= $status['age'] ?> years</span>
                                <span class="badge badge-success">Doses: <?= $status['total_doses'] ?></span>
                                <?php if ($status['missed_count'] > 0): ?>
                                    <span class="badge badge-danger">Missed: <?= $status['missed_count'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Parent:</strong> <?= htmlspecialchars($child['parent_username']) ?></p>
                                <p><strong>DOB:</strong> <?= htmlspecialchars($child['date_of_birth']) ?></p>
                                <p><strong>Gender:</strong> <?= htmlspecialchars($child['gender']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Address:</strong> <?= htmlspecialchars($child['address']) ?></p>
                                <p><strong>City:</strong> <?= htmlspecialchars($child['city']) ?>, <?= htmlspecialchars($child['province']) ?></p>
                                <?php if (!empty($child['special_notes'])): ?>
                                    <p><strong>Notes:</strong> <?= htmlspecialchars($child['special_notes']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Vaccination History</h5>
                        
                        <?php if (!empty($vaccinations)): ?>
                            <div class="list-group">
                                <?php foreach ($vaccinations as $vax): ?>
                                    <div class="list-group-item vaccination-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?= htmlspecialchars($vax['campaign_name']) ?></strong><br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($vax['center_name']) ?> - 
                                                    <?= htmlspecialchars($vax['vaccine_date']) ?>
                                                </small>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-completed">Completed</span><br>
                                                <small>Batch: <?= htmlspecialchars($vax['batch_number']) ?></small><br>
                                                <small>By: <?= htmlspecialchars($vax['worker_name']) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No vaccination records found for this child.</div>
                        <?php endif; ?>
                        
                        <?php if (!empty($missed)): ?>
                            <h5 class="mt-4 mb-3">Missed Vaccinations</h5>
                            <div class="list-group">
                                <?php foreach ($missed as $miss): ?>
                                    <div class="list-group-item vaccination-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>Missed Dose</strong><br>
                                                <small class="text-muted">
                                                    Reason: <?= htmlspecialchars($miss['reason']) ?><br>
                                                    Reported by: <?= htmlspecialchars($miss['reported_by_name']) ?> on <?= htmlspecialchars($miss['created_at']) ?>
                                                </small>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-missed">Missed</span><br>
                                                <small>Status: <?= htmlspecialchars(ucfirst($miss['status'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>