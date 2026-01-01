<?php
session_start();
if($_SESSION['level'] != "user") header("Location: index.php");
?>
<div class="container mt-5">
    <h1>Halo User, <?php echo $_SESSION['nama']; ?>!</h1>
    <p>Ini adalah halaman dashboard standar.</p>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>