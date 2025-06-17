<?php
include('php/db.php');

function getUsers() {
    global $conn;
    $loggedInUser = $_SESSION['user_id'];

    $sql = "SELECT * FROM users WHERE users.id != ?";
    $params = array($loggedInUser);
    $types = "i";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $_GET['search'];
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm);
        $types .= "sss";
    }

    if (isset($_GET['filter']) && $_GET['filter'] !== 'default') {
        $filter = $_GET['filter'];
        $sql .= " AND LOWER(user_type) = ?";
        array_push($params, ucfirst($filter));
        $types .= "s";
    }
    
    if (isset($_GET['sort']) && $_GET['sort'] !== 'default') {
        switch ($_GET['sort']) {
            case 'lastName (A-Z)':
                $sql .= " ORDER BY last_name ASC";
                break;
            case 'lastName (Z-A)':
                $sql .= " ORDER BY last_name DESC";
                break;
            case 'firstName (A-Z)':
                $sql .= " ORDER BY first_name ASC";
                break;
            case 'firstName (Z-A)':
                $sql .= " ORDER BY first_name DESC";
                break;
        }
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    if (isset($_GET['sort'])) {
        if ($_GET['sort'] === 'desc') {
            $users = array_reverse($users);
        }
    }
    
    return $users;
}

function displayUsers($users) {
    $currentSearch = htmlspecialchars($_GET['search'] ?? '');
    $currentFilter = $_GET['filter'] ?? 'default';
    $currentSort = $_GET['sort'] ?? 'default';
    ?>
    <div class="heading"><h1>User Management</h1>
        <div class="filter-section">
            <form action="" method="get">
                <input type="text" name="search" class="search-bar" 
                       placeholder="Search user..." value="<?php echo $currentSearch; ?>">

                <select name="filter" id="sortnfilter">
                    <option value="default" <?php echo $currentFilter === 'default' ? 'selected' : ''; ?>>
                        Filter by: Default
                    </option>
                    <option value="admin" <?php echo $currentFilter === 'admin' ? 'selected' : ''; ?>>
                        Filter by: Type (Admin)
                    </option>
                    <option value="student" <?php echo $currentFilter === 'student' ? 'selected' : ''; ?>>
                        Filter by: Type (Student)
                    </option>
                    <option value="faculty" <?php echo $currentFilter === 'faculty' ? 'selected' : ''; ?>>
                        Filter by: Type (Faculty)
                    </option>
                </select>
                <select name="sort" id="sortnfilter">
                    <option value="default" <?php echo $currentSort === 'default' ? 'selected' : ''; ?>>
                        Sort by: Default
                    </option>
                    <option value="lastName (A-Z)" <?php echo $currentSort === 'lastName (A-Z)' ? 'selected' : ''; ?>>
                        Sort by: Last Name (A-Z)
                    </option>
                    <option value="lastName (Z-A)" <?php echo $currentSort === 'lastName (Z-A)' ? 'selected' : ''; ?>>
                        Sort by: Last Name (Z-A)
                    </option>
                    <option value="firstName (A-Z)" <?php echo $currentSort === 'firstName (A-Z)' ? 'selected' : ''; ?>>
                        Sort by: First Name (A-Z)
                    </option>
                    <option value="firstName (Z-A)" <?php echo $currentSort === 'firstName (Z-A)' ? 'selected' : ''; ?>>
                        Sort by: First Name (Z-A)
                    </option>
                    <option value="asc" <?php echo $currentSort === 'asc' ? 'selected' : ''; ?>>
                        Sort by: # (ASC)
                    </option>
                    <option value="desc" <?php echo $currentSort === 'desc' ? 'selected' : ''; ?>>
                        Sort by: # (DESC)
                    </option>
                </select>
                <button type="submit" class="action-btn">Apply Filters</button>        
            </form>
            <div class="question-actions">
                <button class="action-btn" onclick="showUserCreator()">Create User</button>
            </div>
        </div>
    </div>
    <?php if (!empty($users)): ?>
    <div id="users-container">
        <div class="table-responsive">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <?php
                    $totalUsers = count($users);
                    $isDescending = isset($_GET['sort']) && $_GET['sort'] === 'desc';
                    
                    foreach($users as $index => $user): 
                        $count = $isDescending ? $totalUsers - $index : $index + 1;
                    ?>
                        <tr id="users-<?php echo $user['id']; ?>">
                            <td><?php echo $count; ?></td>
                            <td><?php echo htmlspecialchars($user['first_name']).' '.htmlspecialchars($user['last_name']) ?></td>
                            <td><?php echo htmlspecialchars($user['user_type']) ?></td>
                            <td><?php echo htmlspecialchars($user['email']) ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                                <button class="del-btn" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
        <div class="no-questions">No users found.</div>
    <?php endif; ?>
<?php
}

$users = getUsers();
displayUsers($users);
?>