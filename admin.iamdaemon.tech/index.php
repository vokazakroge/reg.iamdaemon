<?php
require_once __DIR__ . '/../reg.iamdaemon.tech/config.php';
requireAdmin(); // Только для vokazakroge

$db = getDb();
$users = $db->query("SELECT id, username, email, verified, created_at, status FROM users ORDER BY created_at DESC")->fetchArray(SQLITE3_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN - DAEMON</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container">
        <header>
            <span class="logo">🛡️ ADMIN PANEL</span>
            <div style="display: flex; align-items: center; gap: 12px">
                <span class="user-badge">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="https://reg.iamdaemon.tech/logout.php" class="logout">Logout</a>
            </div>
        </header>

        <h2>📊 Статистика</h2>
        <div class="stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($users); ?></span>
                <span class="stat-label">Пользователей</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">
                    <?php 
                    $verified = 0;
                    foreach ($users as $u) { if ($u['verified']) $verified++; }
                    echo $verified;
                    ?>
                </span>
                <span class="stat-label">Активных</span>
            </div>
        </div>

        <h2>👥 Пользователи</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Verified</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTable">
                    <?php foreach ($users as $u): ?>
                    <tr data-id="<?php echo $u['id']; ?>">
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['verified'] ? 'success' : 'muted'; ?>">
                                <?php echo $u['verified'] ? '✓' : '✗'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d.m.Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <span class="badge <?php echo $u['status'] === 'active' ? 'success' : 'error'; ?>">
                                <?php echo htmlspecialchars($u['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-action ban" data-username="<?php echo htmlspecialchars($u['username']); ?>" data-status="<?php echo $u['status']; ?>">
                                <?php echo $u['status'] === 'active' ? '🔒 Ban' : '🔓 Unban'; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/admin.js" defer></script>
</body>
</html>