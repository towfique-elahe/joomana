<?php

/* Template Name: Admin | Footer */

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
<script src="<?php echo get_template_directory_uri() . '/assets/js/teacher-selection.js'; ?>"></script>
<script src="<?php echo get_template_directory_uri() . '/assets/js/custom-calender.js'; ?>"></script>

<!-- Initialize TinyMCE -->
<script>
tinymce.init({
    selector: '#description', // Target the textarea with id="description"
    height: 300, // Set the height of the editor
    menubar: false, // Disable the menu bar
    plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount', // Add plugins
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help', // Customize the toolbar
    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }', // Customize the content style
});
</script>

</body>

</html>