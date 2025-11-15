

<?php
require __DIR__ . '/db.php';
if ($conn && $conn->ping()) {
    echo "<h3 style='color:green;'>✅ DB Connection Active: {$db_name}</h3>";
} else {
    echo "<h3 style='color:red;'>❌ Connection Failed.</h3>";
}



/*
<?php
include 'config/db.php';
if ($conn) {
    echo "<h3 style='color:green;'>✅ DB Connection Active: " . $database . "</h3>";
} else {
    echo "<h3 style='color:red;'>❌ Connection Failed: " . $conn->connect_error . "</h3>";
}
?>
*/