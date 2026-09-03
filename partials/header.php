<?php
$page = basename($_SERVER['PHP_SELF']);
$base = $basePath ?? '';
?>
<header class="site-header">
  <div class="header-inner container">
    <a class="brand" href="<?= $base ?>index.php" aria-label="Hinlo Airsoft Zone home">
      <img src="<?= $base ?>assets/logo.png" alt="Hinlo Airsoft Zone logo">
      <span>HINLO AIRSOFT ZONE</span>
    </a>
    <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false"><span></span><span></span><span></span></button>
    <nav class="main-nav">
      <a class="<?= $page==='index.php'?'active':'' ?>" href="<?= $base ?>index.php">HOME</a>
      <a class="<?= $page==='about.php'?'active':'' ?>" href="<?= $base ?>about.php">ABOUT US</a>
      <a class="<?= $page==='join.php'?'active':'' ?>" href="<?= $base ?>join.php">JOIN US</a>
      <a class="<?= $page==='contacts.php'?'active':'' ?>" href="<?= $base ?>contacts.php">CONTACTS</a>
      <a class="nav-cta" href="<?= $base ?>join.php">JOIN US</a>
    </nav>
  </div>
</header>