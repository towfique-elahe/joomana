<?php
    /**
     * Theme Functions for Joomana
     * @package Joomana
     */

    // Dynamically load all PHP files in the 'functions' folder.
    foreach (glob(get_template_directory() . '/functions/*.php') as $file) {
        require_once $file;
    }