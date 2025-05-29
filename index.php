<?php
include('config.php');

// Query to fetch vaccination centers
$center_query = "SELECT * FROM vaccination_centers";
$center_result = mysqli_query($conn, $center_query);

// Handle search for vaccination centers
$search_query = "";
if (isset($_GET['center_search']) && !empty($_GET['center_search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['center_search']);
    $center_query = "SELECT * FROM vaccination_centers WHERE name LIKE '%$search_term%' OR location LIKE '%$search_term%'";
    $center_result = mysqli_query($conn, $center_query);
}

// Query to fetch vaccination schedules
$schedule_query = "SELECT vs.*, vc.name AS center_name FROM vaccination_schedules vs JOIN vaccination_centers vc ON vs.center_id = vc.id";
$schedule_result = mysqli_query($conn, $schedule_query);

// Sample status data for the chart (replace with actual data if available)
$status_data = [
    'Vaccinated' => 10,
    'Pending Vaccinations' => 15,
    'Total Child' => 25,
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polio Vaccination Portal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --accent: #ff6f61;
            --highlight: #6f42c1;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, #e0e7ff 100%);
            color: var(--dark);
        }
        .navbar {
            background-color: var(--dark);
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .navbar-brand:hover, .nav-link:hover {
            color: var(--accent) !important;
        }
        .hero-section {
            height: 600px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('./images/hotel.jpg') center/cover no-repeat;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 0 0 30px 30px;
            animation: fadeIn 1.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .hero-section .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px 30px;
            font-size: 1.2rem;
            transition: transform 0.3s, background-color 0.3s;
        }
        .hero-section .btn-primary:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
            transform: scale(1.05);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: white;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .search-form {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            margin-bottom: 40px;
            border-left: 5px solid var(--accent);
        }
        .section {
            padding: 80px 0;
        }
        .section-title {
            color: var(--highlight);
            margin-bottom: 40px;
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 60px;
            height: 4px;
            background-color: var(--accent);
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        .feature-icon {
            font-size: 3em;
            color: var(--accent);
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .card:hover .feature-icon {
            color: var(--highlight);
        }
        .chart-container {
            max-width: 450px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        footer {
            background-color: var(--dark);
            color: white;
            padding: 30px 0;
            border-top: 4px solid var(--accent);
        }
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        .btn-success:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px rgba(255, 111, 97, 0.3);
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="display-4 fw-bold">Polio Vaccination Portal</h1>
        <p class="lead mb-4">Access Vaccination Centers and Schedules</p>
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-2"></i>Login to Explore</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <!-- About Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">About the Platform</h2>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="lead">The Polio Vaccination Portal is designed to provide easy access to information about polio vaccination centers and their schedules. It enables users to find nearby centers, check vaccination dates, and stay informed about ongoing campaigns.</p>
                    <p>Our mission is to support public health by ensuring communities have access to critical vaccination resources, fostering awareness and participation in polio eradication efforts.</p>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4 class="text-center mb-4">Vaccination Status Distribution</h4>
                        <canvas id="submissionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center section-title">Key Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-map-marker-alt feature-icon"></i>
                            <h5 class="card-title">Find Centers</h5>
                            <p class="card-text">Locate polio vaccination centers near you with detailed location and contact information.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-calendar-alt feature-icon"></i>
                            <h5 class="card-title">View Schedules</h5>
                            <p class="card-text">Check upcoming vaccination dates and campaign details for each center.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-info-circle feature-icon"></i>
                            <h5 class="card-title">Stay Informed</h5>
                            <p class="card-text">Access notes and updates about polio vaccination campaigns and health guidelines.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vaccination Centers Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">Vaccination Centers</h2>
            <form method="get" class="search-form mb-5">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="center_search" class="form-control" placeholder="Search by center name or location" value="<?= htmlspecialchars($_GET['center_search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Search</button>
                    </div>
                </div>
            </form>

            <?php if (mysqli_num_rows($center_result) > 0): ?>
                <div class="row">
                    <?php while ($center = mysqli_fetch_assoc($center_result)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($center['name']) ?></h5>
                                    <p class="card-text">
                                        <strong>Location:</strong> <?= htmlspecialchars($center['location']) ?><br>
                                        <strong>Contact:</strong> <?= htmlspecialchars($center['contact_number']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No vaccination centers found for the given search criteria.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vaccination Schedules Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center section-title">Vaccination Schedules</h2>
            <?php if (mysqli_num_rows($schedule_result) > 0): ?>
                <div class="row">
                    <?php while ($schedule = mysqli_fetch_assoc($schedule_result)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($schedule['campaign_name']) ?></h5>
                                    <p class="card-text">
                                        <strong>Center:</strong> <?= htmlspecialchars($schedule['center_name']) ?><br>
                                        <strong>Date:</strong> <?= date('Y-m-d', strtotime($schedule['vaccine_date'])) ?><br>
                                        <strong>Notes:</strong> <?= nl2br(htmlspecialchars($schedule['notes'] ?? 'No notes available')) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No vaccination schedules found.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 Polio Vaccination Portal. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Submission Status Pie Chart
        const ctx = document.getElementById('submissionChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    'Vaccinated',
                    'Pending Vaccinations',
                    'Total Child',
                ],
                datasets: [{
                    data: [
                        <?= $status_data['Vaccinated'] ?>,
                        <?= $status_data['Pending Vaccinations'] ?>,
                        <?= $status_data['Total Child'] ?>,
                    ],
                    backgroundColor: [
                        '#6c757d',  // --secondary (gray)
                        '#0dcaf0',  // --info (cyan)
                        '#dc3545',  // --danger (red)
                    ],
                    borderColor: 'rgba(255,255,255,0.8)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14,
                                family: 'Segoe UI'
                            },
                            color: 'var(--dark)'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'var(--dark)',
                        titleFont: { size: 16 },
                        bodyFont: { size: 14 }
                    }
                }
            }
        });
    </script>
</body>
</html>