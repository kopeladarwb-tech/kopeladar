<?php
require_once 'db.php';
requireLogin();

// Directories for uploads
$uploadDir = '../uploads/images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Get image path to delete the file
    $stmt = $pdo->prepare('SELECT image_path FROM workers WHERE id = ?');
    $stmt->execute([$id]);
    $worker = $stmt->fetch();

    if ($worker && $worker['image_path'] && file_exists('../' . $worker['image_path'])) {
        unlink('../' . $worker['image_path']);
    }

    // Delete record from database
    $stmt = $pdo->prepare('DELETE FROM workers WHERE id = ?');
    if ($stmt->execute([$id])) {
        $message = 'Worker deleted successfully.';
    }
}

// Handle Add/Edit Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $category = $_POST['category'] ?? 'board';
    $id = $_POST['id'] ?? null;

    // Handle File Upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $destination)) {
            $imagePath = 'uploads/images/' . $fileName;
        }
    }

    if ($id) {
        if ($imagePath) {
            $stmt = $pdo->prepare('SELECT image_path FROM workers WHERE id = ?');
            $stmt->execute([$id]);
            $oldWorker = $stmt->fetch();
            if ($oldWorker && $oldWorker['image_path'] && file_exists('../' . $oldWorker['image_path'])) {
                unlink('../' . $oldWorker['image_path']);
            }
            $stmt = $pdo->prepare('UPDATE workers SET name = ?, position = ?, category = ?, image_path = ? WHERE id = ?');
            $stmt->execute([$name, $position, $category, $imagePath, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE workers SET name = ?, position = ?, category = ? WHERE id = ?');
            $stmt->execute([$name, $position, $category, $id]);
        }
        $message = 'Worker updated successfully.';
    } else {
        if ($name && $position) {
            $stmt = $pdo->prepare('INSERT INTO workers (name, position, category, image_path) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $position, $category, $imagePath]);
            $message = 'Worker added successfully.';
        } else {
            $message = 'Please provide name and position.';
        }
    }
}

$stmt = $pdo->query('SELECT * FROM workers ORDER BY created_at DESC');
$workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$editWorker = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM workers WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $editWorker = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Workers - KOPELADAR Admin</title>
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
            <a href="workers.php" class="active"><i class="fas fa-users"></i> Manage Workers</a>
            <a href="reports.php"><i class="fas fa-file-pdf"></i> Annual Reports</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1>Manage Workers</h1>
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
                    <i class="fas fa-user-edit" style="color: var(--accent);"></i>
                    <?php echo $editWorker ? 'Edit Worker' : 'Add New Worker'; ?>
                </h2>
                <form method="post" action="workers.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $editWorker['id'] ?? ''; ?>">

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required
                            value="<?php echo htmlspecialchars($editWorker['name'] ?? ''); ?>"
                            placeholder="e.g. John Doe">
                    </div>

                    <div class="form-group">
                        <label>Position / Title</label>
                        <input type="text" name="position" required
                            value="<?php echo htmlspecialchars($editWorker['position'] ?? ''); ?>"
                            placeholder="e.g. Board Member">
                    </div>

                    <div class="form-group">
                        <label>Department / Category</label>
                        <select name="category" required>
                            <?php $selectedCategory = $editWorker['category'] ?? ''; ?>
                            <option value="board" <?php echo $selectedCategory === 'board' ? 'selected' : ''; ?>>Lembaga
                                Kopeladar (Board)</option>
                            <option value="management" <?php echo $selectedCategory === 'management' ? 'selected' : ''; ?>>Barisan Pengurusan (Management)</option>
                            <option value="org" <?php echo $selectedCategory === 'org' ? 'selected' : ''; ?>>Struktur
                                Organisasi (Org Chart)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Profile Image</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if ($editWorker && $editWorker['image_path']): ?>
                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                                <img src="../<?php echo htmlspecialchars($editWorker['image_path']); ?>" class="thumb-img">
                                <span style="font-size: 0.8rem; color: #64748b;">Current Image</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-save"></i> <?php echo $editWorker ? 'Update' : 'Save'; ?>
                        </button>
                        <?php if ($editWorker): ?>
                            <a href="workers.php" class="btn btn-danger"><i class="fas fa-times"></i> Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="card">
                <h2 style="margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-list-ul" style="color: var(--accent);"></i>
                    Workers Directory
                </h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name / Position</th>
                                <th>Category</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workers as $w): ?>
                                <tr>
                                    <td>
                                        <?php if ($w['image_path']): ?>
                                            <img src="../<?php echo htmlspecialchars($w['image_path']); ?>" class="thumb-img">
                                        <?php else: ?>
                                            <div class="thumb-img"
                                                style="background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--primary);">
                                            <?php echo htmlspecialchars($w['name']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                                            <?php echo htmlspecialchars($w['position']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo htmlspecialchars($w['category']); ?>">
                                            <?php
                                            if ($w['category'] == 'board')
                                                echo 'Board';
                                            elseif ($w['category'] == 'management')
                                                echo 'Management';
                                            else
                                                echo 'Org Chart';
                                            ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="workers.php?edit=<?php echo $w['id']; ?>" class="btn btn-primary btn-sm"
                                            title="Edit"><i class="fas fa-pen"></i></a>
                                        <a href="workers.php?delete=<?php echo $w['id']; ?>" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this worker?');"
                                            title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($workers)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No
                                        workers found. Add one on the left.</td>
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