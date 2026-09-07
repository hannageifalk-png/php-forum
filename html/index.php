<?php
session_start();
require 'includes/menu.php';
?>

<section class="hero">
    <div class="hero-content">
    <h1>Let's talk football.</h1>

    <p>
        Talk football, join supporter communities and share your
        opinions with fans who love the game as much as you do.
    </p>

    <?php if (!isset($_SESSION['user_id'])): ?>

        <a href="/create-account.php" class="hero-button">
            Create account
        </a>

        <a href="/login.php" class="hero-button">
            Log in
        </a>

    <?php else: ?>

        <a href="/groups.php" class="hero-button">
            Explore groups
        </a>

    <?php endif; ?>

</div>

<div class="hero-image">
    <img src="/img/hero.png" alt="Football supporters in a stadium">
</div>

</section>



<section class="why-join">
    <h2>More than 90 minutes</h2>

    <div class="benefits">

        <div class="benefit-card">
            <h3>Talk football</h3>
            <p>
                Discuss matches, players, transfers and everything
                happening in the world of football.
            </p>
        </div>

        <div class="benefit-card">
            <h3>Find your supporters</h3>
            <p>
                Join groups for your favourite clubs, leagues and
                football topics and connect with other fans.
            </p>
        </div>

        <div class="benefit-card">
            <h3>Start your own community</h3>
            <p>
                Create a group for your club or football interest
                and start discussions with other supporters.
            </p>
        </div>

    </div>
</section>

<section class="cta">
    <h2>Ready to join the community?</h2>
    <p>Create an account and start exploring groups today.</p>

    <a href="/create-account.php" class="cta-button">
        Get started
    </a>
</section>