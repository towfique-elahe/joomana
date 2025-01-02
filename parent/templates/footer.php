<?php

/* Template Name: Parent | Footer */

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

<script>
// JavaScript function to filter the teacher list dynamically
function filterUser() {
    const input = document.querySelector('.search-bar input');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#user-list tr');

    rows.forEach(row => {
        const name = row.querySelector('.user-name a').textContent.toLowerCase();
        if (name.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
</body>

</html>