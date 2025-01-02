<?php

/* Template Name: Student | Footer */

?>

<footer class="footer">
    <p class="copyright">
        &copy;
        <?php 
            echo date("Y") . ' <span class="title">' . esc_html(get_bloginfo('name')) . '</span>'; 
        ?>
    </p>
</footer>

<?php wp_footer(); ?>

</div> <!-- Closing main container -->

</body>

</html>