<?php
/*
Template Name: Custom Template
*/

get_header(); ?>

<div id="primary" class="content-area">
    <main id="appsumo__gutenkit__licensing_form-container" class="site-main">
        <!-- wp page title -->
        <h1><?php the_title(); ?></h1>

        <!-- sign up form with, firstname, lastname, email and password -->
        <form action="" method="post" id="appsumo__gutenkit__licensing_form">
            <!-- display messages -->
            <?php
            $messages = Appsumo_PLG_Licensing\Util::get_messages();
            if ($messages) {
                foreach ($messages as $message) {
                    echo '<div class="message ' . $message['type'] . '">' . $message['message'] . '</div>';
                }
            }
            ?>

            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" required>
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" required>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <input type="submit" value="Continue">
        </form>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>