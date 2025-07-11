<?php
include('php/session_management.php');
include('php/db.php');

// Get assessment ID
$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

// Fetch assessment title
$assessmentTitle = '';
$stmt = $conn->prepare("SELECT title FROM assessments WHERE id = ?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$stmt->bind_result($assessmentTitle);
$stmt->fetch();
$stmt->close();

// Get total points for the assessment
$totalPoints = 0;
$pointsQuery = $conn->prepare("
    SELECT SUM(q.points) 
    FROM assessment_questions aq 
    JOIN questions q ON aq.question_id = q.id 
    WHERE aq.assessment_id = ?
");
$pointsQuery->bind_param("i", $assessment_id);
$pointsQuery->execute();
$pointsQuery->bind_result($totalPoints);
$pointsQuery->fetch();
$pointsQuery->close();


// Fetch results
$results = [];
$sql = "
    SELECT u.id AS student_id, u.first_name, u.last_name, ar.score
    FROM assessment_results ar
    JOIN users u ON ar.student_id = u.id
    WHERE ar.assessment_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
   $row['percentage'] = $totalPoints > 0 ? round(($row['score'] / $totalPoints) * 100, 2) : 0;
    $results[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessment Statistics</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <section id="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </section>

    <section id="content">
        <?php include 'includes/nav.php'; ?>
        
        <main id="main">
            <div class="heading2">
                <h1>Statistics for: <?php echo htmlspecialchars($assessmentTitle); ?></h1>
                <img id="return-btn" src="img/return-button.svg" alt="return to classes page" onclick="history.back()">    
            </div>
            
                

            <?php if (empty($results)): ?>
                <div class="no-results">No student results found for this assessment.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Score</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr onclick="window.location.href='view_assessment.php?assessment_id=<?php echo $assessment_id; ?>&student_id=<?php echo $row['student_id']; ?>'" style="cursor: pointer;">
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['score']) . ' / ' . $totalPoints; ?> pts</td>
                                    <td><?php echo htmlspecialchars($row['percentage']) . '%'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </section>
</body>
</html>
