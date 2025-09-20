<?php
session_start();
$_SESSION['test_variable'] = 'Session is working!';
echo '<h1>Page 1</h1>';
echo '<p>Session variable has been set.</p>';
echo '<a href="/admin/test_session2.php">Click here to go to Page 2</a>';
?>