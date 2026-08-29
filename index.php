<?php
$paragraphFiles = [
    'new' => __DIR__ . '/paragraph/new copy text.txt',
    'community' => __DIR__ . '/paragraph/community copy text.txt',
    'about' => __DIR__ . '/paragraph/about copy text.txt',
];

$paragraphContent = [];
foreach ($paragraphFiles as $key => $filePath) {
    $paragraphContent[$key] = file_exists($filePath)
        ? htmlspecialchars(file_get_contents($filePath), ENT_QUOTES, 'UTF-8')
        : '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hinlo Airsoft Zone</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>




    <header class="topbar">

        <a class="brand" href="#home">

            <img src="images/logo.png" alt="Hinlo Airsoft Zone Logo">

            <span>HINLO AIRSOFT ZONE</span>

        </a>


        <nav>

            <a class="active" href="#home">
                HOME
            </a>

            <a href="#about">
                ABOUT US
            </a>

            <a href="#community">
                JOIN US
            </a>

            <a href="#contact">
                CONTACTS
            </a>

            <a class="join-pill" href="#community">
                <span>→</span>
                JOIN US
            </a>

        </nav>

    </header>



    <main id="home">


        

        <section class="new-section">


            <div class="new-copy">

                <h1>
                    WHAT’S NEW?
                </h1>


                <p>
                    <?php echo $paragraphContent['new']; ?>
                </p>

            </div>


            <div class="new-photo"></div>


        </section>



        

        <section class="community" id="community">


            <div class="community-photo"></div>


            <div class="community-copy">

                <h2>
                    JOIN OUR COMMUNITY!
                </h2>


                <p>
                    <?php echo $paragraphContent['community']; ?>
                </p>


                <a class="black-pill" href="#contact">

                    <span>▣</span>

                    Register

                </a>

            </div>


        </section>



       

        <section class="merch" id="merch">


            <div class="merch-overlay"></div>


            <div class="merch-inner">


                <h2>
                    BUY OUR MERCH!
                </h2>


                <div class="products">


                    <div class="product">

                        <img
                            src="images/bottle.png"
                            alt="Hinlo Airsoft Zone Bottle"
                        >

                    </div>


                    <div class="product">

                        <img
                            src="images/bag.png"
                            alt="Hinlo Airsoft Zone Bag"
                        >

                    </div>


                    <div class="product">

                        <img
                            src="images/hoodie.png"
                            alt="Hinlo Airsoft Zone Hoodie"
                        >

                    </div>


                    <div class="product">

                        <img
                            src="images/balaclava.png"
                            alt="Hinlo Airsoft Zone Balaclava"
                        >

                    </div>


                </div>


                <a class="white-pill" href="#contact">

                    <span>→</span>

                    ORDER NOW!

                </a>


            </div>


        </section>



        \

        <section class="about" id="about">


            <div class="about-photo"></div>


            <div class="about-copy">


                <h2>
                    ABOUT US
                </h2>


                <p>
                    <?php echo $paragraphContent['about']; ?>
                </p>


                <a class="learn" href="#contact">

                    <span>→</span>

                    Learn More

                </a>


            </div>


        </section>



       

        <section class="reviews">


           

            <div class="review-form">

                <label for="review">
                    Write a review:
                </label>


                <textarea
                    id="review"
                    placeholder="">
                </textarea>

            </div>



            

            <article class="review-card">


                <div class="stars">
                    ★★★★★
                </div>


                <h3>
                    AMAZING!
                </h3>


                <p>
                    Good community, amazing staff and
                    overall good vibes.
                </p>


                <div class="reviewer">

                    <div class="avatar"></div>


                    <div>

                        <strong>
                            Reviewer name
                        </strong>

                        <small>
                            Date
                        </small>

                    </div>

                </div>


            </article>



            <!-- REVIEW 2 -->

            <article class="review-card">


                <div class="stars">
                    ★★★★☆
                </div>


                <h3>
                    GOOD!
                </h3>


                <p>
                    Good customer service and amazing
                    community, just wish they do
                    midnight battles.
                </p>


                <div class="reviewer">

                    <div class="avatar"></div>


                    <div>

                        <strong>
                            Reviewer name
                        </strong>

                        <small>
                            Date
                        </small>

                    </div>

                </div>


            </article>


        </section>


    </main>

    <footer id="contact">


        <div class="footer-brand">


            <div class="footer-logo">

                <img
                    src="images/logo.png"
                    alt="Hinlo Airsoft Zone"
                >

                <span>
                    HINLO AIRSOFT ZONE
                </span>

            </div>


            <div class="contact-list">


                <p>
                    <b>⌕</b>
                    091234567890
                </p>


                <p>
                    <b>✉</b>
                    hinloairsoftzone@gmail.com
                </p>


                <p>
                    <b>f</b>
                    Hinloasz
                </p>


            </div>


        </div>



        <div class="options">


            <h3>
                Options:
            </h3>


            <a href="#merch">
                Rent Gun
            </a>


            <a href="#merch">
                Merch
            </a>


            <a href="#community">
                Join Community
            </a>


        </div>


    </footer>


</body>

</html>