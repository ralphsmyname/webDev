<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>About Us | Hinlo Airsoft Zone</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'partials/header.php'; ?>
    <?php include 'partials/text.php'; ?>

    <main>

        <section class="inner-hero">
            <div class="container">
                <h1>ABOUT US</h1>
            </div>
        </section>


        <section class="page-section">
            <div class="container page-grid">

                <div class="feature-image">
                    <img src="assets/business-cards.png" alt="Hinlo Airsoft Zone cards">
                </div>

                <div>
                    <h2>BUILT FOR PLAYERS.</h2>

                    <?php paragraph('about_intro.txt'); ?>
                    <?php paragraph('about_details.txt'); ?>
                </div>

            </div>
        </section>

    </main>


    <?php include 'partials/footer.php'; ?>

    <script src="js/main.js"></script>

</body>

</html>