<?php
if (!isset($_SESSION['user_id'])) {
	header("Location: index.php");
	exit();
}
?>
<section id="sidebar">
	<a href="classes.php" class="brand">
		<?php
		if (isset($_SESSION['user_type'])) {
			if ($_SESSION['user_type'] === 'Admin') {
				echo '<img src="img/admin.png">';
			} elseif ($_SESSION['user_type'] === 'Faculty') {
				echo '<img src="img/faculty.png">';
			} elseif ($_SESSION['user_type'] === 'Student') {
				echo '<img src="img/student.png">';
			}
		}
		?>
	</a>
	<ul class="side-menu top">
		<li>
			<a href="classes.php">
				<i class='bx bxs-smile'></i>
				<span class="text">Home</span>
			</a>
		</li>
		<?php if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'Admin' || $_SESSION['user_type'] === 'Faculty')): ?>
		<li>
			<a href="questions.php">
				<i class='bx bxs-smile'></i>
				<span class="text">Questions</span>
			</a>
		</li>
		<li>
			<a href="assessments.php">
				<i class='bx bxs-smile'></i>
				<span class="text">Assessments</span>
			</a>
		</li>
		<?php endif; ?>
		<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'): ?>
		<li>
			<a href="users.php">
				<i class='bx bxs-smile'></i>
				<span class="text">Users</span>
			</a>
		</li>
		<?php endif; ?>
	</ul>

	<!-- Bottom logos -->
	<div class="bottom-logos">
		<img src="img\accountancy.png" alt="Accountancy Logo">
		<img src="img\slu.png" alt="SLU Logo">
	</div>
</section>
