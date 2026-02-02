<?php
// Use a strong password
$password = 'password123';

// Generate the hash
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Output the hash
echo $hashed_password;
?>