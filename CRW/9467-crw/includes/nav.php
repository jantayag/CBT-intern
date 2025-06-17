<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<nav id="nav">
    <i class='bx bx-menu'></i>
    <div class="profile">
        <div class="admin-name">
            <h1><?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Guest'; ?></h1>
            <a href="php/logout.php">Logout</a>
        </div>
        <img src="img/goat.jpg" alt="admin-avatar">
    </div>
</nav>