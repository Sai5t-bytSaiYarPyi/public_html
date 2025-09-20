<?php
session_start();
echo '<h1>Page 2</h1>';
if (isset($_SESSION['test_variable'])) {
    echo '<p style="font-size: 24px; color: green;">' . $_SESSION['test_variable'] . '</p>';
} else {
    echo '<p style="font-size: 24px; color: red;">Session FAILED! The variable was lost.</p>';
}
?>