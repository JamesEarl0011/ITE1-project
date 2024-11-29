<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="icon" href="imgs/favicon.png">
</head>
<style>
    *{
        margin: 0;
        padding: 0;
    }
    body{
        display: flex;
        flex-direction: column      ;
        justify-content: center;
        align-items: center; 
        height: 100vh; 
        background: #FCFFFC;
    }
    .welText{
        font-size: 70px;
        margin-bottom: 10vh;
        color: #040F0F;
    }
    #fm{
        border: 1px solid #040F0F;
        border-radius: 10px;
        height: 400px;
        width: 700px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #2D3A3A;
        color: #FCFFFC;
        box-shadow: 0 5px 30px 3px #040F0F;
    }
    h3{
        font-size: 35px;
        position: relative;
        top: -10%;
        text-decoration: underline 3px #2BA84A;
    }
    input{
        border: 0;
        outline: none;
        border-bottom: 1.5px solid #FCFFFC;
        background: none;
        font-weight: bold;
        color: #FCFFFC;
    }
    label{
        font-weight: bolder;
    }
    label,input{
        font-size: 25px;
    }
    .inp{
        margin-left: 30px;
    }
    .pin{
        margin-left: 143px;
        letter-spacing: 5px;
    }
    .subBtn{
        position: relative;
        top: 20%;
        padding: 20px 40px;
        font-size: 20px;
        font-weight: bolder;
        border: none;
        border-radius: 15px;
        background: #2BA84A;
        color: #040F0F;
        box-shadow: 0 5px 10px 1px #0A0908;
    }
    .subBtn:hover{
        background: #248232;
        color: #FCFFFC;
        box-shadow: none;
        box-shadow: 0 5px 10px 1px #0A0908;
    }
</style>
<body>
<!-- HTML CODE FOR THE LOGIN FORM -->
    <h1 class="welText">Welcome to University of Cebu Library</h1>
    <form id="fm" action="" method="post">
        <h3 class="fmHeader">Sign in using your UC Library Card</h3>
        
        <div>
            <label for="userID">Card Number : </label>
            <input class="inp id" type="text" name="userID">
        </div>
        <br>
        <div>
            <label for="userPin">Pin : </label>
            <input class="inp pin" type="password" name="userPin">
        </div>
        <button class="subBtn" type="submit">SIGN IN</button>
    </form>
</body>
</html>
<?php 
    session_start();

    include("database/dbCon.php");
    include("database/dbOperations.php");

    $user = new Operations($conn);

    global $uname;
    global $udept_role;
    global $uconum;
    global $uaddress;
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $userID = $_POST['userID'];
        $userPin = $_POST['userPin'];

        // Check if the user ID exists
        if ($user->userExists($userID)) {
            if ($user->validatePin($userID, $userPin)) {
                $userDetails = $user->getUserDetails($userID);
        
                $_SESSION['uid'] = $userDetails['uid'];
                $_SESSION['uname'] = $userDetails['uname'];
                $_SESSION['udept_role'] = $userDetails['udept_role'];
                $_SESSION['uconum'] = $userDetails['uconum'];
                $_SESSION['uaddress'] = $userDetails['uaddress'];

                header('Location: borrowerInterface.php');
                exit();
            }
            else {
                echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 100px; width: 350px; 
                    padding:60px; border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                    <center><h3 style='color:red;'>warning</h3></center><br>
                    <center><h2>You have entered a wrong pin. Please try again</h2></center></div>";
                header("refresh:1;");
            }
        }
        elseif($userID == "librarian" && $userPin == "librarian"){
            header('Location: adminInterface.php');
            exit();
        }
        else {
            echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 100px; width: 350px; 
                padding:60px; border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                <center><h3 style='color:red;'>warning</h3></center><br>
                <center><h2>ID card does not exist. Please try again</h2></center></div>";
            header("refresh:1;");
        }
    }
?>
