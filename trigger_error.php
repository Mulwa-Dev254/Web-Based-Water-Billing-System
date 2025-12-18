<?php
// This will deliberately generate a warning and write to the log
echo $nonExistentVariable; // Using an undefined variable
// Or alternatively:
trigger_error("This is a test error message to create the log file", E_USER_WARNING);
?>