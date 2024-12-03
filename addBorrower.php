<?php 
    session_start();

    require __DIR__ . "/database/db_connection.php";
    require_once __DIR__ . "/operations/admin_operations.php";
    require_once __DIR__ . "/operations/borrower_operations.php";

    // Instantiate the Admin_operations class
    $admin = new Admin_operations($conn);

    // PHP for user registration
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['register'])) {
            $uid = $_POST['uid'];
            $upin = $_POST['upin'];
            $uname = $_POST['uname'];
            $udept_role = $_POST['udept_role'];
            $uconum = $_POST['uconum'];
            $address = $_POST['address'];

            $addUserResult = $admin->addUser($uid, $upin, $uname, $udept_role, $uconum, $address);

            if ($addUserResult) {
                echo 
                "
                    <div style='position: fixed; top: 5%; left: 50%; transform: translateX(-50%);background:#A1CCA5;height: 7vh;
                    width: 30vw; border: 3px solid #111D13; border-radius: 20px;color: #111D13; z-index: 1000;padding-bottom: 30px;'>
                        <center><h2 style='color: #e85d04'>SUCCESSFUL</h2><br><h3>Account registered successfully.</h3></center>
                    </div>
                ";
                header("refresh: 2;");
            } else {
                echo 
                "
                    <div style='position: fixed; top: 5%; left: 50%; transform: translateX(-50%);background:#A1CCA5;height: 7vh;
                    width: 30vw; border: 3px solid #111D13; border-radius: 20px;color: #111D13; z-index: 1000;padding-bottom: 30px;'>
                        <center><h3 style='color: #e63946'>FAILURE</h3><br><h4>Account registration failed.</h4></center>
                    </div>
                ";
                header("refresh: 2;"); 
            }
        }
        if (isset($_POST["back"])) {
            header("location: adminInterface.php");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Account</title>
    <link rel="icon" href="imgs/favicon.png">  
</head>
<style>
    @font-face {
        font-family: "Rockwell";
        src: url(fonts/ROCK.TTF) format('truetype');
    }
    *{
        margin: 0;
        padding: 0;
        font-family: 'Rockwell';
    }
    body{
        background: #A1CCA5;
    }
    .container{
        display: inline-flex;
        position: fixed;
        top: 10%;
        left: 50%;
        transform: translateX(-50%);
        gap: 5%;
    }
    .left{
        background-color: #709775;
        border: 1px solid black;
        border-radius: 20px;
        width: 40vw;
    }
    .left h3{
        margin: 20px 0 50px 0;
    }
    .left, .right label, input{
        font-size: 1.3rem;
    }
    .left input {
        width: 50%;
        outline: none;
        border: none;
        border-bottom: 2px solid black;
        background: none;
    }
    .form-group{
        margin: 0 0 20px 30px;
        border-radius: 10px;
    }
    .subbtn{
        background: #A1CCA5;
        padding: 1em 2em;
        font-size: 1rem;
        font-weight: bolder;
        border-radius: 10px;
        margin: 80px 0 10px 0;
    }
    .back{
        position: absolute;
        bottom: 0;
        right: 1%;
        background: #1f3322;
        color: white;
        padding: 1em 2em;
        font-size: 1rem;
        font-weight: bolder;
        border-radius: 10px;
        margin: 80px 0 10px 0;
    }
</style>
<body>
    <center><h1>Add new account</h1></center>
    <div class="container">
        <form method="post"><button class="back" type="submit"  name="back">Go Back</button></form>
        <form class="left" method="post">
            <center><h3>Register here</h3></center>
            <div class="form-group">
                <label for="uid">Card Id Number:</label>
                <input type="text" id="uid" name="uid" style="margin-left: 58px;" required>
            </div>

            <div class="form-group">
                <label for="upin">PIN:</label>
                <input type="password" id="upin" name="upin" maxlength="15" style="margin-left: 180px;" required>
            </div>

            <div class="form-group">
                <label for="uname">Name:</label>
                <input type="text" id="uname" name="uname" style="margin-left: 157px;" required>
            </div>

            <div class="form-group">
                <label for="udept_role">Department & Role:</label>
                <input type="text" id="udept_role" name="udept_role" style="margin-left: 30px;" required>
            </div>

            <div class="form-group">
                <label for="uconum">Contact Number:</label>
                <input type="text" id="uconum" name="uconum" style="margin-left: 54px;" required>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" style="margin-left: 133px;" required>
            </div>

            <center><button class="subbtn" type="submit" name="register">Register Account</button></center>
        </form>
    </div>
</body>
</html>