<?php
  session_start();
  session_unset();
  session_destroy();
  
  // Redirect to the sign-in page
  header("Location: signin.php");
  exit();
?>