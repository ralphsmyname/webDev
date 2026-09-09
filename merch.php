<?php
require_once 'includes/csrf.php';
require_once 'includes/functions.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Merch | Hinlo Airsoft Zone</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'partials/header.php'; ?>
    <?php include 'partials/text.php'; ?>

    <main>

        <section class="inner-hero">
            <div class="container">
                <h1>BUY OUR MERCH!</h1>

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a class="btn btn-light btn-auto" href="account/orders.php" style="margin-top:20px;">My Orders</a>
                <?php else: ?>
                    <a class="btn btn-light btn-auto" href="login.php" style="margin-top:20px;">Log In to Order</a>
                <?php endif; ?>
            </div>
        </section>


        <section class="page-section">
            <div class="container">

                <div class="cards merch-page-grid">

                    <article class="info-card">
                        <img src="assets/merch-bottle.png" alt="Bottle">
                        <h3>Water Bottle</h3>
                        <?php paragraph('merch_bottle.txt'); ?>
                        <a class="btn" href="order.php?product=bottle">ORDER</a>
                    </article>

                    <article class="info-card">
                        <img src="assets/merch-bag.png" alt="Bag">
                        <h3>Tote Bag</h3>
                        <?php paragraph('merch_bag.txt'); ?>
                        <a class="btn" href="order.php?product=bag">ORDER</a>
                    </article>

                    <article class="info-card">
                        <img src="assets/merch-hoodie.png" alt="Hoodie">
                        <h3>Hoodie</h3>
                        <?php paragraph('merch_hoodie.txt'); ?>
                        <a class="btn" href="order.php?product=hoodie">ORDER</a>
                    </article>

                    <article class="info-card">
                        <img src="assets/merch-balaclava.png" alt="Balaclava">
                        <h3>Balaclava</h3>
                        <?php paragraph('merch_balaclava.txt'); ?>
                        <a class="btn" href="order.php?product=balaclava">ORDER</a>
                    </article>

                </div>

            </div>
        </section>

    </main>

    <?php include 'partials/footer.php'; ?>
    <script src="js/main.js"></script>

</body>
</html>