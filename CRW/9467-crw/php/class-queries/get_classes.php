<?php
include('php/db.php');

function getClasses() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return array(); 
    }

    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $semester = isset($_GET['semester']) && is_numeric($_GET['semester']) ? intval($_GET['semester']) : null;

    if ($_SESSION['user_type'] === 'Faculty' || $_SESSION['user_type'] === 'Admin') {
        return getFacultyClasses($search, $semester);
    } elseif ($_SESSION['user_type'] === 'Student') {
        return getStudentClasses($search,$semester);
    }
    
    return array();
}


function getFacultyClasses($search = null, $semester = null) {
    global $conn;
    $faculty_id = $_SESSION['user_id'];

    $sql = "SELECT c.id, c.class_code, c.sem, c.AY, 
                p.name as program_name, 
                u.first_name, u.last_name
            FROM classes c
            LEFT JOIN programs p ON c.program_id = p.id
            LEFT JOIN users u ON c.faculty_id = u.id
            WHERE c.faculty_id = ?";
            
    $params = ["i", $faculty_id];

    if ($search) {
        $sql .= " AND (c.class_code LIKE ? OR p.name LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[0] .= "ss";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($semester) {
        $sql .= " AND c.sem = ?";
        $params[0] .= "i";
        $params[] = $semester;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


function getStudentClasses($search = null, $semester = null) {
    global $conn;
    $student_id = $_SESSION['user_id'];

    $sql = "SELECT c.id, c.class_code, c.sem, c.AY, 
                p.name as program_name, 
                u.first_name, u.last_name
            FROM student_class sc
            INNER JOIN classes c ON sc.class_code = c.class_code
            LEFT JOIN programs p ON c.program_id = p.id
            LEFT JOIN users u ON c.faculty_id = u.id
            WHERE sc.student_id = ?";
            
    $params = ["i", $student_id];

    if ($search) {
        $sql .= " AND (c.class_code LIKE ? OR p.name LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[0] .= "ss";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($semester) {
        $sql .= " AND c.sem = ?";
        $params[0] .= "i";
        $params[] = $semester;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


function getSemesterText($sem) {
    switch($sem) {
        case 1:
            return "1st Semester";
        case 2:
            return "2nd Semester";
        case 3:
            return "Short Term";
        default:
            return "Unknown Semester";
    }
}

function displayClasses($classes) {
    if (empty($classes)) {
        echo "<h1>No classes found.</h1>";
        return;
    }

    $user_type = $_SESSION['user_type'];
    $background_image = '';

    if ($user_type === 'Faculty') {
        $background_image = 'faculty_card_bg.jpg';
    } elseif ($user_type === 'Admin') {
        $background_image = 'admin_card_bg.jpg'; 
    } else {
        $background_image = 'student_card_bg.jpg'; 
    }

    foreach ($classes as $class) {
        $semester = getSemesterText($class['sem']);
        ?>
        <div class="card">
            <div class="card-clickable" onclick="window.location.href='class.php?id=<?php echo htmlspecialchars($class['class_code']); ?>'">
                <div class="card-header" style="background: url('img/<?php echo $background_image; ?>') no-repeat; background-position: center; background-size: cover;" data-user-type="<?php echo htmlspecialchars($_SESSION['user_type']); ?>">
                    <h1 id="class-code"><?php echo htmlspecialchars($class['class_code']) . " | " . htmlspecialchars($class['program_name']); ?></h1>
                    <h2 class="sem-and-ay"><?php echo $semester . ", A.Y. " . htmlspecialchars($class['AY']); ?></h2>
                    <h3 class="teacher-name"><?php echo htmlspecialchars($class['first_name']) . " " . htmlspecialchars($class['last_name']); ?></h3>
                </div>
                <div class="card-body">
                </div>
            </div>
            <?php if ($_SESSION['user_type'] === 'Faculty' || $_SESSION['user_type'] === 'Admin'): ?>
            <div class="card-footer">
                <img src="img/edit-button.svg" 
                     alt="edit class" 
                     class="edit-button"
                     data-class-id="<?php echo htmlspecialchars($class['id']); ?>">
                <img src="img/delete-button.svg" 
                     alt="delete class" 
                     class="delete-button"
                     data-class-id="<?php echo htmlspecialchars($class['id']); ?>">
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>