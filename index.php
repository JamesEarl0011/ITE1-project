<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="icon" href="imgs/favicon.png">
</head>
<style>
    *{
        margin: 0;
        padding: 0;
        font-family: "Rockwell";
    }
    body{
        background: #A1CCA5;
    }
    h1{
        font-size: 5em;
        margin: 1% 0 10% 0;
    }
    h3{
        font-size: 2rem;
        margin-bottom: 10%;
    }
    .container{
        background: #709775;
        height: fit-content;
        width: fit-content;
        border: 3px solid black;
        border-radius: 10px;
    }
    .options button{
        background: #A1CCA5;
        margin-top: .5%;
        width: 49%;
    }
    .log{
        margin-top: .5%;
        padding: 20px 80px;
    }
    .log input,label{
        font-size: 1.5rem;
    }
    .log input{
        background: none;
        outline: none;
        border: none;
        border-bottom: 1px solid black;
    }
    .top{
        margin-bottom: 5%;
    }
    .bottom{
        margin-bottom: 10%;
    }
    button{
        background: #A1CCA5;
        padding: 1em 2em;
        font-size: 1rem;
        font-weight: bolder;
        border-radius: 10px;
    }
</style>
<body>
    <center>
        <h1>Welcome to the Library</h1>
        <div class="container">
            <form class="options" method="post">
                <button type="submit"name="notR">Not Registered?</button>
                <button type="submit"name="uscan">Scan Id?</button>
            </form>
            <form class="log" method="post">
                <h3>Sign in to your account</h3>
                <div class="top">
                    <label for="uid">ID number : </label>
                    <input type="text" name="uid">
                </div>
                <div class="bottom">
                    <label for="upin">Pin : </label>
                    <input type="password" name="upin" style="margin-left: 17%">
                </div>
                <button type="submit" name="mlogin">Log In</button>
            </form>
        </div>
    </center>
</body>
</html>
<?php
    session_start();

    require __DIR__ . "/database/db_connection.php";
    require_once __DIR__ . "/operations/borrower_operations.php";
    require_once __DIR__ . "/operations/admin_operations.php";

    $borrower = new Borrower_operations($conn);
    $admin = new Admin_operations($conn);

    //button for unregistered users
    if(isset($_POST["notR"])){
        echo 
        "
            <div style='position: fixed; top: 15%; left: 50%; transform: translateX(-50%);background:#709775;height: fit-content;
            width: 30vw; border: 3px solid #111D13; border-radius: 20px;'>
                <center><h3>Please go to the Librarian to start the registration process.<br><br> Kindly bring your school ID.</h3></center>
            </div>
        ";
        header("refresh: 3;");
    }
    
    //button for id scanning
    if(isset($_POST["uscan"])){
            echo "
                <div style='position: fixed; top: 45%; left: 50%; transform: translateX(-50%); background: #709775; padding: 5px 10px; height: fit-content; width: 30vw; border: 3px solid #111D13; border-radius: 20px;'>
                    <center>
                        <h3>Scan your ID</h3>
                        <form method='post'>
                            <input type='text' name='scanInput'autofocus style='padding: 5px; margin-top: 10px; width: 80%; outline: none;
                            background:none; border:none; border-bottom: 1px solid black;'>
                            <br>
                            <button type='submit' name='scanSubmit' style='margin-top: 10px; padding: 5px 15px;'>Submit</button>
                        </form>
                    </center>
                </div>
            ";

    }
    //Handle scanned ID submission automatically
    if (isset($_POST["scanSubmit"])) {
        $scannedId = $_POST["scanInput"];
        if($borrower->userExist($scannedId)){
            header("location: borrowerInterface.php");
            exit;
        }
        else{
            echo "<div style='position: fixed; top: 15%; left: 50%; transform: translateX(-50%); height: 150px; width: 350px; padding: 0 40px; 
                border: 1px solid #A1CCA5; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                padding-bottom: 30px;'>
                <center><h3 style='color:maroon;'>warning</h3></center><br>
                <center><h2>ID card does not exist. Please try again</h2></center></div>";
            header("refresh:1;");
        }
    }

    //Handle manual login
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if (isset($_POST["mlogin"])){
            $userID = $_POST["uid"];
            $pin = $_POST["upin"];

            if ($borrower->userExist($userID) && $userID != "admin"){
                if($borrower->validatePin($userID, $pin)){
                    $userDetails = $borrower->getUserDetails($userID);

                    $_SESSION['uid'] = $userDetails['uid'];
                    $_SESSION['uname'] = $userDetails['uname'];
                    $_SESSION['udept_role'] = $userDetails['udept_role'];
                    $_SESSION['uconum'] = $userDetails['uconum'];
                    $_SESSION['uaddress'] = $userDetails['uaddress'];

                    header("location: borrowerInterface.php");
                    exit;
                }
                else{
                    echo "<div style='position: fixed; top: 15%; left: 50%; transform: translateX(-50%); height: 150px; width: 350px; padding: 0 40px; 
                        border: 1px solid #A1CCA5; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                        padding-bottom: 30px;'>
                        <center><h3 style='color:maroon;'>warning</h3></center><br>
                        <center><h2>You have entered a wrong pin. Please try again</h2></center></div>";
                    header("refresh:1;");
                }
            }
            elseif($userID == "admin"){
                if($admin->userExist($userID, $pin)){
                    header("location: adminInterface.php");
                    exit;
                }
                else{
                    echo "<div style='position: fixed; top: 15%; left: 50%; transform: translateX(-50%); height: 150px; width: 350px; padding: 0 40px; 
                        border: 1px solid #A1CCA5; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                        padding-bottom: 30px;'>
                        <center><h3 style='color:maroon;'>warning</h3></center><br>
                        <center><h2>You have entered a wrong pin. Please try again</h2></center></div>";
                    header("refresh:1;");
                }
            }
            else{
                echo "<div style='position: fixed; top: 15%; left: 50%; transform: translateX(-50%); height: 150px; width: 350px; padding: 0 40px; 
                    border: 1px solid #A1CCA5; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                    padding-bottom: 30px;'>
                    <center><h3 style='color:maroon;'>warning</h3></center><br>
                    <center><h2>ID card does not exist. Please try again</h2></center></div>";
                header("refresh:1;");
            }
        }
    }

?>
