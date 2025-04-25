<?php
// Password to hash
$password = 'rahinatu4R*';

// Generate hash
$hash = password_hash($password, PASSWORD_DEFAULT);

// Output the hash
echo $hash;
?> 