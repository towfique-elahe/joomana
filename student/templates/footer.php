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

</div> <!-- Closing main container -->

<!-- scripts -->
<!-- Font Awesome CSS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<!-- custom JavaScript files -->
<script src="<?php echo get_template_directory_uri() . '/assets/js/sidebar.js'; ?>"></script>
<script src="<?php echo get_template_directory_uri() . '/assets/js/form-validation.js'; ?>"></script>
<script src="<?php echo get_template_directory_uri() . '/assets/js/image-upload.js'; ?>"></script>
<script src="<?php echo get_template_directory_uri() . '/assets/js/list-filter.js'; ?>"></script>
<script src="<?php echo get_template_directory_uri() . '/assets/js/modal.js'; ?>"></script>
<script src="<?php echo get_template_directory_uri() . '/assets/js/custom-calender.js'; ?>"></script>

</body>

</html>