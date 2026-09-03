<?php
require_once 'includes/csrf.php';
require_once 'includes/functions.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Join Us | Hinlo Airsoft Zone</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'partials/header.php'; ?>
    <?php include 'partials/text.php'; ?>

    <main>

        <section class="inner-hero">
            <div class="container">
                <h1>JOIN OUR COMMUNITY!</h1>
            </div>
        </section>


        <section class="page-section">
            <div class="container page-grid">

                <div>
                    <h2>REGISTER</h2>

                    <?php paragraph('join_intro.txt'); ?>

                    <form
                        class="contact-box"
                        data-demo-form
                        data-endpoint="actions/register.php"
                        method="post"
                        action="actions/register.php"
                        novalidate
                    >

                        <?= csrf_field() ?>

                        <input
                            required
                            name="full_name"
                            id="full_name"
                            maxlength="100"
                            placeholder="Full name"
                            value="<?= flash_old('full_name') ?>"
                        >
                        <span class="field-error" data-error-for="full_name"></span>

                        <input
                            required
                            type="email"
                            name="email"
                            id="email"
                            maxlength="150"
                            placeholder="Email address"
                            value="<?= flash_old('email') ?>"
                        >
                        <span class="field-error" data-error-for="email"></span>

                        <input
                            name="phone"
                            id="phone"
                            maxlength="30"
                            placeholder="Contact number"
                            value="<?= flash_old('phone') ?>"
                        >
                        <span class="field-error" data-error-for="phone"></span>

                        <button class="btn" type="submit">
                            ▣ Register
                        </button>

                        <p class="form-note"></p>

                    </form>
                </div>


                <div class="feature-image">
                    <img
                        src="assets/community-soldier.png"
                        alt="Airsoft player"
                    >
                </div>

            </div>
        </section>

    </main>


    <?php include 'partials/footer.php'; ?>

    <script src="js/main.js"></script>

</body>

</html>