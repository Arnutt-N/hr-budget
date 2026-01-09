<?php
/**
 * Front Controller - Redirect to public/index.php
 * 
 * This file exists to allow the application to run from the root directory
 * when the server cannot be configured to point to /public as the document root.
 */

// Load the main application from public/index.php
require __DIR__ . '/public/index.php';
