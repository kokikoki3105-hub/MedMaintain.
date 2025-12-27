<?php
/* Initialize the session to access and destroy it */
session_start();

/* Clear all session variables */
$_SESSION = array();

/* Destroy the session cookie if it exists */
if (session_id() != "" || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 2592000, '/');
}

/* Completely destroy the session */
session_destroy();

/* Redirect directly to the main landing page */
header("Location: index.php");
exit;
?>