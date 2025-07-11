<?php
include_once(__DIR__ . '/../db.php'); // Safe include

function getStudentLogs($conn) {
    $sql = "SELECT logs.*, users.email AS actor_email 
            FROM logs 
            LEFT JOIN users ON logs.user_id = users.id 
            WHERE users.user_type = 'Student'
            ORDER BY logs.created_at DESC 
            LIMIT 50";

    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$logs = getStudentLogs($conn);
?>
<div class="logs-section">
    <h2>Student Activity Logs</h2>
    <div class="table-responsive">
        <table id="logsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Email</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $index => $log): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($log['actor_email'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
