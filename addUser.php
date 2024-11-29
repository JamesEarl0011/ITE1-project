<?php
session_start();
include("database/dbCon.php");
include("database/dbOperations.php");

$user = new Operations($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uid = $_POST['uid'];
    $upin = $_POST['upin'];
    $uname = $_POST['uname'];
    $udept_role = $_POST['udept_role'];
    $uconum = $_POST['uconum'];
    $address = $_POST['address'];

    // Call the addUser method to insert the new user
    if ($user->addUser($uid, $upin, $uname, $udept_role, $uconum, $address)) {
        $message = "User added successfully!";
    } else {
        $message = "Error adding user. UID already exists.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="icon" href="imgs/favicon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        input[type="text"], input[type="password"] {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 20px;
        }
        input[type="submit"], input[type="button"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .message {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2 style="margin-left: 110px;">Add New User : </h2>
<form action="" method="post">
    <label for="uid">Card Id Number:</label>
    <input type="text" id="uid" name="uid" required>

    <label for="upin">PIN:</label>
    <input type="password" id="upin" name="upin" maxlength="15" required>

    <label for="uname">Name:</label>
    <input type="text" id="uname" name="uname" required>

    <label for="udept_role">Department & Role:</label>
    <input type="text" id="udept_role" name="udept_role" required>

    <label for="uconum">Contact Number:</label>
    <input type="text" id="uconum" name="uconum" required>

    <label for="address">Address:</label>
    <input type="text" id="address" name="address" required>

    <center><input type="submit" value="Add User"></center>
</form>

<div class="message">
    <?php if (isset($message)) echo $message; ?>
</div>

<!-- Back button to redirect to adminInterface.php -->
<div style="text-align: center; margin-top: 20px;">
    <form action="adminInterface.php" method="get">
        <input style="background-color: #f44336;" type="button" value="Back to Admin Interface" onclick="window.location.href='adminInterface.php';">
    </form>
</div>

</body>
</html>
