<?php
$config = [
    'local' => [
        'host' => 'localhost',
        'db' => 'career_tech_db',
        'user' => 'root',
        'password' => ''
    ],
    'production' => [
        'host' => 'sql106.infinityfree.com',
        'db' => 'if0_38834358_career_tech_db',
        'user' => 'if0_38834358',
        'password' => 'JZo1Uhd4c1cbF'
    ]
];

// Get the environment from the command line or default to local
$env = isset($argv[1]) ? $argv[1] : 'local';

if (!isset($config[$env])) {
    die("Invalid environment. Use 'local' or 'production'\n");
}

// Generate the config file
$configContent = "<?php
\$host = '{$config[$env]['host']}';      // Database host
\$db   = '{$config[$env]['db']}';  // Database name
\$user = '{$config[$env]['user']}';                 // Database username
\$password = '{$config[$env]['password']}';            // Database password

// Create connection
\$conn = new mysqli(\$host, \$user, \$password, \$db);

// Check connection
if (\$conn->connect_error) {
    die(\"Connection failed: \" . \$conn->connect_error);
}
?>";

// Write to config.php
file_put_contents('config.php', $configContent);

echo "Configuration switched to $env environment.\n";
?> 