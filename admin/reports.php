<?php
require_once 'db.php';
requireLogin();

// Directories for uploads
$uploadDir = '../uploads/reports/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Get file path to delete the file
    $stmt = $pdo->prepare('SELECT file_path FROM reports WHERE id = ?');
    $stmt->execute([$id]);
    $report = $stmt->fetch();

    if ($report && file_exists('../' . $report['file_path'])) {
        unlink('../' . $report['file_path']);
    }

    // Delete record from database
    $stmt = $pdo->prepare('DELETE FROM reports WHERE id = ?');
    if ($stmt->execute([$id])) {
        $message = 'Report deleted successfully.';
    }
}

// Handle Add/Edit Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $year = $_POST['year'] ?? date('Y');
    $id = $_POST['id'] ?? null;

    // Handle File Upload
    $filePath = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['file']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $destination)) {
            $filePath = 'uploads/reports/' . $fileName; // Path to save in DB relative to frontend
        }
    }

    if ($id) {
        // Update
        if ($filePath) {
            // Delete old file
            $stmt = $pdo->prepare('SELECT file_path FROM reports WHERE id = ?');
            $stmt->execute([$id]);
            $oldReport = $stmt->fetch();
            if ($oldReport && file_exists('../' . $oldReport['file_path'])) {
                unlink('../' . $oldReport['file_path']);
            }
            // Update with new file
            $stmt = $pdo->prepare('UPDATE reports SET title = ?, year = ?, file_path = ? WHERE id = ?');
            $stmt->execute([$title, $year, $filePath, $id]);
        } else {
            // Update without changing file
            $stmt = $pdo->prepare('UPDATE reports SET title = ?, year = ? WHERE id = ?');
            $stmt->execute([$title, $year, $id]);
        }
        $message = 'Report updated successfully.';
    } else {
        // Insert
        if ($title && $year && $filePath) {
            $stmt = $pdo->prepare('INSERT INTO reports (title, year, file_path) VALUES (?, ?, ?)');
            $stmt->execute([$title, $year, $filePath]);
            $message = 'Report uploaded successfully.';
        } elseif (!$filePath) {
            $message = 'Please upload a file for the new report.';
        } else {
            $message = 'Please provide a title and year.';
        }
    }
}

// Fetch all reports
$stmt = $pdo->query('SELECT * FROM reports ORDER BY year DESC, created_at DESC');
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch report for editing
$editReport = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM reports WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $editReport = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Annual Reports - KOPELADAR Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>KOPELADAR</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="workers.php"><i class="fas fa-users"></i> Manage Workers</a>
            <a href="reports.php" class="active"><i class="fas fa-file-pdf"></i> Annual Reports</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1>Annual Reports</h1>
            <div class="admin-profile">
                <div class="name">Administrator</div>
                <i class="fas fa-user-circle" style="font-size: 2.5rem; color: var(--primary);"></i>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">

            <!-- Form Section -->
            <div class="card" style="align-self: start;">
                <h2 style="margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-upload" style="color: var(--accent);"></i>
                    <?php echo $editReport ? 'Edit Report' : 'Upload Report'; ?>
                </h2>
                <form method="post" action="reports.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $editReport['id'] ?? ''; ?>">

                    <div class="form-group">
                        <label>Report Title</label>
                        <input type="text" name="title" required
                            value="<?php echo htmlspecialchars($editReport['title'] ?? ''); ?>"
                            placeholder="e.g. Annual Report 2024">
                    </div>

                    <div class="form-group">
                        <label>Financial Year</label>
                        <input type="number" name="year" required
                            value="<?php echo htmlspecialchars($editReport['year'] ?? date('Y')); ?>">
                    </div>

                    <div class="form-group">
                        <label>Document (PDF/DOC)</label>
                        <input type="file" name="file" accept=".pdf,.doc,.docx">
                        <?php if ($editReport && $editReport['file_path']): ?>
                            <div style="margin-top: 10px; font-size: 0.9rem;">
                                <a href="../<?php echo htmlspecialchars($editReport['file_path']); ?>" target="_blank"
                                    style="color: var(--accent); text-decoration: none; font-weight: 500;">
                                    <i class="fas fa-external-link-alt"></i> View Current File
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-save"></i> <?php echo $editReport ? 'Update' : 'Upload'; ?>
                        </button>
                        <?php if ($editReport): ?>
                            <a href="reports.php" class="btn btn-danger"><i class="fas fa-times"></i> Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="card">
                <h2 style="margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-folder-open" style="color: var(--accent);"></i>
                    Document Archive
                </h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Report Name</th>
                                <th>File</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td><span
                                            style="font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($r['year']); ?></span>
                                    </td>
                                    <td style="font-weight: 500;"><?php echo htmlspecialchars($r['title']); ?></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($r['file_path']); ?>" target="_blank"
                                            class="badge"
                                            style="background: rgba(234, 179, 8, 0.1); color: var(--accent-hover); text-decoration: none;">
                                            <i class="fas fa-file-pdf"></i> View
                                        </a>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="reports.php?edit=<?php echo $r['id']; ?>" class="btn btn-primary btn-sm"
                                            title="Edit"><i class="fas fa-pen"></i></a>
                                        <a href="reports.php?delete=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this report?');"
                                            title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No
                                        reports uploaded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>

</html>