<?php
require_once 'db.php';
requireLogin();

$stmtWorkers = $pdo->query('SELECT COUNT(*) FROM workers');
$workersCount = $stmtWorkers->fetchColumn();

$stmtReports = $pdo->query('SELECT COUNT(*) FROM reports');
$reportsCount = $stmtReports->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - KOPELADAR Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>KOPELADAR</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="workers.php"><i class="fas fa-users"></i> Manage Workers</a>
            <a href="reports.php"><i class="fas fa-file-pdf"></i> Annual Reports</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1>Dashboard Overview</h1>
            <div class="admin-profile">
                <div class="name">Administrator</div>
                <i class="fas fa-user-circle" style="font-size: 2.5rem; color: var(--primary);"></i>
            </div>
        </div>

        <div class="card-grid">
            <div class="card stat-card">
                <div class="stat-info">
                    <h3>Total Staff & Board</h3>
                    <p class="value"><?php echo $workersCount; ?></p>
                </div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="card stat-card">
                <div class="stat-info">
                    <h3>Annual Reports</h3>
                    <p class="value"><?php echo $reportsCount; ?></p>
                </div>
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            </div>
        </div>
    </main>

</body>

</html>