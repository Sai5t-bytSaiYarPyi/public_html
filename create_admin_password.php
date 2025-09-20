<?php
// Enter the new password you want to use inside the quotes
$new_password = 'AdminPassword123';

// This will hash the password using BCRYPT, which is what your website uses
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Display the hashed password
echo '<h3>Your New Hashed Password:</h3>';
echo '<textarea rows="4" cols="80" readonly>' . htmlspecialchars($hashed_password) . '</textarea>';
echo '<p>Copy the text above and paste it into the password field in phpMyAdmin.</p>';
?>