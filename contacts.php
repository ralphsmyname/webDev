<?php
require_once 'includes/csrf.php';
require_once 'includes/functions.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contacts | Hinlo Airsoft Zone</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'partials/header.php'; ?>
    <?php include 'partials/text.php'; ?>

    <main>

        <section class="inner-hero">
            <div class="container">
                <h1>CONTACTS</h1>
            </div>
        </section>


        <section class="page-section light">
            <div class="container page-grid">

                <div>
                    <h2>GET IN TOUCH</h2>

                    <?php paragraph('contact_intro.txt'); ?>

                    <p>
                        <strong>Phone:</strong> 091234567890<br>
                        <strong>Email:</strong> hinloairsoftzone@gmail.com<br>
                        <strong>Facebook:</strong> Hinloasz
                    </p>
                </div>


                <form
                    class="contact-box"
                    data-demo-form
                    data-endpoint="actions/contact.php"
                    method="post"
                    action="actions/contact.php"
                    novalidate
                >

                    <?= csrf_field() ?>

                    <input
                        required
                        name="name"
                        id="name"
                        maxlength="100"
                        placeholder="Name"
                        value="<?= flash_old('name') ?>"
                    >
                    <span class="field-error" data-error-for="name"></span>

                    <input
                        required
                        type="email"
                        name="email"
                        id="email"
                        maxlength="150"
                        placeholder="Email"
                        value="<?= flash_old('email') ?>"
                    >
                    <span class="field-error" data-error-for="email"></span>

                    <textarea
                        required
                        name="message"
                        id="message"
                        maxlength="5000"
                        placeholder="Message"
                    ><?= flash_old('message') ?></textarea>
                    <span class="field-error" data-error-for="message"></span>

                    <button class="btn" type="submit">
                        SEND MESSAGE
                    </button>

                    <p class="form-note"></p>

                </form>

            </div>
        </section>

    </main>


    <?php include 'partials/footer.php'; ?>

    <script src="js/main.js"></script>

</body>

</html>