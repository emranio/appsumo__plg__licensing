<?php
/*
Template Name: Custom Template
*/


get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <h1>Custom Template Page</h1>
        <p>This is a custom template page.</p>

        <!-- sign up form with, firstname, lastname, email and password -->
        <form action="" method="post" id="appsumo__plg__licensing_form">
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" required>
            <br>
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" required>
            <br>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <br>
            <input type="submit" value="Continue">
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>