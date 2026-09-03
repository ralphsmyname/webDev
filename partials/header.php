<?php $page = basename($_SERVER['PHP_SELF']); ?>
<header class="site-header">
  <div class="header-inner container">
    <a class="brand" href="index.php" aria-label="Hinlo Airsoft Zone home">
      <img src="assets/logo.png" alt="Hinlo Airsoft Zone logo">
      <span>HINLO AIRSOFT ZONE</span>
    </a>
    <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false"><span></span><span></span><span></span></button>
    <nav class="main-nav">
      <a class="<?= $page==='index.php'?'active':'' ?>" href="index.php">HOME</a>
      <a class="<?= $page==='about.php'?'active':'' ?>" href="about.php">ABOUT US</a>
      <a class="<?= $page==='join.php'?'active':'' ?>" href="join.php">JOIN US</a>
      <a class="<?= $page==='contacts.php'?'active':'' ?>" href="contacts.php">CONTACTS</a>
      <a class="nav-cta" href="join.php">JOIN US</a>
    </nav>
  </div>
</header>
