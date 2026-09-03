<?php
require_once 'includes/csrf.php';
require_once 'includes/functions.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hinlo Airsoft Zone</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'partials/header.php'; ?>
    <?php include 'partials/text.php'; ?>

    <main>

        <section class="section news">
            <div class="news-grid">

                <div class="news-copy fade-up">
                    <h1>WHAT’S NEW?</h1>

                    <?php paragraph('home_news.txt'); ?>
                </div>

                <div class="news-image fade-in"></div>

            </div>
        </section>


        <section class="section community">
            <div class="community-grid">

                <div class="community-image"></div>

                <div class="community-copy fade-up">
                    <h2>JOIN OUR COMMUNITY!</h2>

                    <?php paragraph('home_community.txt'); ?>

                    <a class="btn" href="join.php">
                        <span>▣</span>
                        Register
                    </a>
                </div>

            </div>
        </section>


        <section class="section merch">
            <div class="container merch-inner">

                <h2>BUY OUR MERCH!</h2>

                <div class="merch-grid">

                    <a class="merch-card" href="merch.php">
                        <img src="assets/merch-bottle.png" alt="Hinlo bottle">
                    </a>

                    <a class="merch-card" href="merch.php">
                        <img src="assets/merch-bag.png" alt="Hinlo tote bag">
                    </a>

                    <a class="merch-card" href="merch.php">
                        <img src="assets/merch-hoodie.png" alt="Hinlo hoodie">
                    </a>

                    <a class="merch-card" href="merch.php">
                        <img src="assets/merch-balaclava.png" alt="Hinlo balaclava">
                    </a>

                </div>

                <div class="merch-action">
                    <a class="btn btn-light" href="merch.php">
                        <span class="arrow">→</span>
                        ORDER NOW!
                    </a>
                </div>

            </div>
        </section>


        <section class="section about">
            <div class="about-grid">

                <div class="about-image"></div>

                <div class="about-copy fade-up">
                    <h2>ABOUT US</h2>

                    <?php paragraph('home_about.txt'); ?>

                    <a class="learn" href="about.php">
                        → &nbsp;Learn More
                    </a>
                </div>

            </div>
        </section>


        <section class="section reviews">
            <div class="container reviews-grid">

                <form
                    class="review-form"
                    data-demo-form
                    data-endpoint="actions/review.php"
                    method="post"
                    action="actions/review.php"
                    novalidate
                >
                    <?= csrf_field() ?>

                    <label for="reviewer_name">Your name:</label>
                    <input
                        type="text"
                        id="reviewer_name"
                        name="reviewer_name"
                        maxlength="100"
                        required
                    >
                    <span class="field-error" data-error-for="reviewer_name"></span>

                    <label for="rating">Rating:</label>
                    <select id="rating" name="rating">
                        <option value="5">★★★★★</option>
                        <option value="4">★★★★☆</option>
                        <option value="3">★★★☆☆</option>
                        <option value="2">★★☆☆☆</option>
                        <option value="1">★☆☆☆☆</option>
                    </select>

                    <label for="review">Write a review:</label>
                    <textarea id="review" name="review" maxlength="2000" required></textarea>
                    <span class="field-error" data-error-for="review"></span>

                    <button class="btn" type="submit">Submit review</button>

                    <p class="form-note"></p>
                </form>


                <article class="review-card">
                    <span class="stars">★★★★★</span>

                    <h3>AMAZING!</h3>

                    <?php paragraph('review_amazing.txt'); ?>

                    <div class="reviewer">
                        <img src="assets/reviewer.png" alt="Reviewer">

                        <span>
                            Reviewer name<br>
                            <small>Date</small>
                        </span>
                    </div>
                </article>


                <article class="review-card">
                    <span class="stars">★★★★☆</span>

                    <h3>GOOD!</h3>

                    <?php paragraph('review_good.txt'); ?>

                    <div class="reviewer">
                        <img src="assets/reviewer.png" alt="Reviewer">

                        <span>
                            Reviewer name<br>
                            <small>Date</small>
                        </span>
                    </div>
                </article>

            </div>
        </section>

    </main>


    <?php include 'partials/footer.php'; ?>

    <script src="js/main.js"></script>

</body>

</html>