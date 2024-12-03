<?php
    ob_start();
    session_start();

    require __DIR__ ."/database/db_connection.php";
    require_once __DIR__ ."/operations/borrower_operations.php";

    $borrower = new Borrower_operations($conn);
    
    $uid = $_SESSION["uid"];
    $uname = $_SESSION['uname'];
    $udept_role = $_SESSION['udept_role'];
    $uconum = $_SESSION['uconum'];
    $uaddress = $_SESSION['uaddress'];

    $borrowedBooks = $borrower->getBorrowedBooks($uid);


    $currentWindow = "profile";
    $scanActive = false;
    $book_number = "";
    $book_title = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
//WINDOW CHANGE AND OTHER MAIN BUTTONS
        if(isset($_POST["profile"])){
            $currentWindow = "profile";
        }
        elseif(isset($_POST["bbooks"])){
            $currentWindow = "bbooks";
        }
        elseif(isset($_POST["borrow"])){
            if (count($borrowedBooks) < 5) {
                header('Location: borrowBook.php');
                exit();
            } else {
        echo "<div style='position: fixed; top: 1%; left: 50%; transform: translateX(-50%); height: fit-content; width: 350px; padding: 0 40px; 
                    border: 1px solid black; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                    padding-bottom: 30px;'>
                    <center><h3 style='color:maroon;'>warning</h3></center><br>
                    <center><h2>You have reached the borrowing limit. Return some of the books first.</h2></center></div>";
                header("refresh:2;");
            }
        }
        elseif(isset($_POST["logout"])){
            session_destroy();
            header("location: index.php");
            exit;
        }

//BOOK RETURN BUTTONS
        if(isset($_POST["scan"])){
            $currentWindow = "bbooks";
            $scanActive = true;
        }
        if(isset($_POST["search"])){
            $currentWindow = "bbooks";
            $book_number = $_POST['bnum'] ?? '';
            $book_title = $_POST['btitle'] ?? '';

            if (!empty($book_number)) {
                // Search by book_number
                $book_title = $borrower->searchBookByNumber($book_number);
            } elseif (!empty($book_title)) {
                // Search by book_title
                $book_number = $borrower->searchBookByTitle($book_title);
            }
            if ($book_title == null || $book_number == null) {
                echo "<div style='position: fixed; top: 1%; left: 50%; transform: translateX(-50%); height: fit-content; width: 350px; padding: 0 40px; 
                    border: 1px solid black; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                    padding-bottom: 30px;'>
                    <center><h3 style='color:maroon;'>warning</h3></center><br>
                    <center><h2>Fields are empty.</h2></center></div>";
                header("refresh:2;");
            }
        }
        if(isset($_POST["return"])){
            $book_number = $_POST['bnum'];
            $book_title = $_POST['btitle'];

            $date = $borrower->fetchDateToReturn($uid, $book_number);

            if($date && strtotime($date)){
                $pastDue = (new DateTime($date) < (new DateTime()));

                if ($pastDue && $borrower->hasPayment($uid, $book_number) === false) {
                $fine = $borrower->calculateFine($date);
                echo "
                <div class='confirmDialog'>
                    <h2 style='color:orange; margin: 10px 0 20px 20px;'>Notice</h2>
                    <center>
                        <h3>The returned book \"{$book_title}\" has already passed the return date, <br/> a fine amount of {$fine} 
                        Php needs to be paid first. <br/> Please proceed to the front desk.</h3>
                    </center>
                    <form method='post'>
                        <input type='hidden' name='book_title' value='{$book_title}'>
                        <input type='hidden' name='book_number' value='{$book_number}'>
                        <input type='hidden' name='fine' value='{$fine}'>
                        <center>
                            <button type='submit' class='pay' name='payment_action' value='pay_now'>Pay Now</button>
                            <button type='submit' class='later' name='payment_action' value='return_later'>Return Later</button>
                        </center>
                    </form>
                </div>
                ";
                } elseif ($borrower->hasPayment($uid, $book_number)) {
                    echo "<div style='position: fixed; top: 1%; left: 50%; transform: translateX(-50%); height: fit-content; width: 450px;
                        border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                        padding-bottom: 30px;'>
                        <h2 style='color:red; margin-left: 30px;'>Payment Already Submitted</h2><br>
                        <center><h3>Please wait for the librarian's confirmation.</h3></center></div>";
                    header("refresh:3;");
                } else {
                    if ($borrower->returnBook($uid, $book_number, $book_title)) {
                        echo "<div style='position: fixed; top: 1%; left: 50%; transform: translateX(-50%); height: fit-content; width: 350px; padding: 0 40px; 
                            border: 1px solid black; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                            padding-bottom: 30px;'>
                            <center><h3 style='color:#e85d04;'>success</h3></center><br>
                            <center><h2>Book return successful</h2></center></div>";
                        header("refresh:2;");
                    } else {
                        echo "<div style='position: fixed; top: 1%; left: 50%; transform: translateX(-50%); height: fit-content; width: 350px; padding: 0 40px; 
                            border: 1px solid black; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                            padding-bottom: 30px;'>
                            <center><h3 style='color:maroon;'>warning</h3></center><br>
                            <center><h2>Book return failed. Please make sure you've entered the correct details.</h2></center></div>";
                        header("refresh:2;");
                    }
                }
            } else {
        echo "<div style='position: fixed; top: 1%; left: 50%; transform: translateX(-50%); height: fit-content; width: 350px; padding: 0 40px; 
                    border: 1px solid black; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                    padding-bottom: 30px;'>
                    <center><h3 style='color:maroon;'>warning</h3></center><br>
                    <center><h2>Invalid book details. Please check and try again.</h2></center></div>";
                header("refresh:2;");
            }
            }
        }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Interface</title>
    <link rel="icon" href="imgs/favicon.png">
</head>
<style>
    *{
        padding: 0;
        margin: 0;
        font-family: 'Rockwell';
    }
    body{
        background: #A1CCA5;
    }
    nav{
        position: fixed;
        top: 2%;
        left: 1%;
    }
    nav h1{
        font-size: 3em;
    }
    .container{
        position: fixed;
        top: 15%;
        left: 50%;
        transform: translateX(-50%);
        width: 90vw;
        height: 70vh;
        display: flex;
        flex-direction: row;
        gap: 3%;
    }
    .btnContainer{
        margin-top: 3%;
        display: flex;
        flex-direction: column;
        width: fit-content;
        height: fit-content;
        gap: 10px;
    }
    .btns{
        padding: 15px 30px;
        font-size: 1em;
        font-weight: bolder;
        border-radius: 10px;
        background: #709775;
        color: black;
    }
    .logout{
        padding: 15px 30px;
        font-size: 1em;
        border-radius: 10px;
        background: #1f3322;
        color: #fff;
    }
    .content{
        border: 3px solid black;
        border-radius: 30px;
        width: 80%;
        height: 100%;
        background-color: #709775;
    }
    .profileW h2{
        font-size: 2rem;
    }
    .profileW h3{
        font-size: 1.5rem;
    }
    .borrowed{
        display: flex;
        flex-direction: row;
    }
    .borrowed .left{
        height: 100%;
        width: 60%;
    }
    .left table{
        margin-left: .5%;
        width: fit-content;
        border-collapse: collapse;
        font-size: 1.1rem;
    }
    .left th, td{
        text-align: center; 
        border: 2px solid black;
        padding: 0 10px;
    }
    .left th{
        text-align: center;
    }
    .borrowed .right{
        height: 100%;
        width: 40%;
    }
    .right label,input{
        font-size: 1.2rem;
        font-weight: bold;
    }
    .right input{
        outline: none;
        border: none;
        background: none;
        border-bottom: 2px solid black;
    }
    .right button{
        padding: 5px 10px;
        font-size: 1rem;
        font-weight: bold;
        border-radius: 10px;
    }
    .confirmDialog{
        position: fixed; 
        top: 20px; 
        left: 50%; 
        transform: translateX(-50%); 
        height: fit-content; 
        padding-bottom: 20px;
        width: 600px;
        border: 1px solid #ccc; 
        border-radius: 5px; 
        background-color: #fff; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); 
        z-index: 1000;
    }
    .confirmDialog button{
        margin-top: 30px;
        border: none;
        border-radius: 10px;
        padding: 15px 25px;
        font-size: 15px;
        font-weight: bold;
        box-shadow: 0 3px 5px black;
    }
    .pay{
        background: #709775;
        margin-right: 50px;
    }
    .pay:hover{
        background: #248232;
    }
    .later{
        background: #1f3322;
        color: #fff;
    }
    .later:hover {
        background: maroon;
    }
</style>
<body>
    <nav>
        <h1>Borrower Interface</h1>
    </nav>
    <div class="container">
        <form class="btnContainer" method="post">
            <button type="submit" class="btns" name="profile">Borrower's Profile</button>
            <button type="submit" class="btns" name="bbooks">Books Borrowed</button>
            <button type="submit" class="btns" name="borrow">Borrow Books</button>
            <button type="submit" class="logout" name="logout" style="margin-top: 100%;">Log Out</button>
        </form>
        <div class="content">
            <?php if($currentWindow == "profile") : ?>
                <div class="profileW" style="margin-left: 5%;">
                    <br><br><br>
                    <center><h1 style="width:fit-content; padding: 3px 20px; border-bottom: 3px solid black;">Borrower Profile</h1></center>
                    <h2 style='margin: 50px 0 30px 0;'><span style="margin-right: 182px; color: #032940; font-weight: bolder;">Card ID: </span><?php echo htmlspecialchars($uid); ?></h2>
                    <h2 style='margin-bottom: 30px;'><span style="margin-right: 222px; color: #032940; font-weight: bolder;">Name:</span><?php echo htmlspecialchars($uname); ?></h2>
                    <h3 style='margin-bottom: 30px;'><span style="margin-right: 60px; color: #032940; font-weight: bolder;">Department & Role: </span><?php echo htmlspecialchars($udept_role); ?></h3>
                    <h3 style='margin-bottom: 30px;'><span style="margin-right: 97px; color: #032940; font-weight: bolder;">Contact Number: </span><?php echo htmlspecialchars($uconum); ?></h3>
                    <h3 style='margin-bottom: 30px;'><span style="margin-right: 217px; color: #032940; font-weight: bolder;">Address: </span><?php echo htmlspecialchars($uaddress); ?></h3>
                </div>
            <?php endif;?>
            <?php if($currentWindow == "bbooks") : ?>
                <div class="borrowed">
                    <div class="left">
                        <center><h2 style='margin: 10px 0 20px 0;'>Borrowed Books</h2></center>
                        <table>
                            <thead>
                                <tr>
                                    <th>Book Number</th>
                                    <th>Book Title</th>
                                    <th>Date Borrowed</th>
                                    <th>Date to Return</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($borrowedBooks)) {
                                    foreach ($borrowedBooks as $book) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($book['book_number']) . "</td>";
                                        echo "<td style='text-align: left;'>" . htmlspecialchars($book['book_title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($book['date_borrowed']) . "</td>";
                                        echo "<td>" . htmlspecialchars($book['date_to_return']) . "</td>";
                                        echo "</tr> <style> .content{padding-bottom: 1vh;} </style>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No borrowed books yet.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="right">
                        <center>
                            <h2 style='margin: 10px 0 50px 0;'>Return Books</h2>
                            <form method="post">
                                <div>
                                    <label for="bnum">Book Number : </label>
                                    <input type="text" name="bnum" style='margin-left: 5px;' value="<?php echo htmlspecialchars($book_number); ?>">
                                </div>
                                <div style="margin: 10px 0 50px 0;">
                                    <label for="btitle">Book Title : </label>
                                    <input type="text" name="btitle" style='margin-left: 40px;' value="<?php echo htmlspecialchars($book_title); ?>">
                                </div>
                                <button type="submit" name="scan" style="background-color: #186e46; color: white;">Scan Book</button>
                                <button type="submit" name="search" style="background-color: #186e46; color: white;">Search Book</button>
                                <button type="submit" name="return" style="background-color: #032940; color: white;">Return Book</button>
                            </form>
                        </center>
                        <?php if($scanActive) : ?>
                            <div style='margin:50px 0 0 10%; background: #709775; padding: 5px 10px; 
                            height: fit-content; width: 80%; border: 3px solid #111D13; border-radius: 20px; z-index: 100;'>
                                <center>
                                    <h3>Scan book number</h3>
                                    <form method='post'>
                                        <input type='text' name='bnum'autofocus style='padding: 5px; margin-top: 10px; width: 50%; outline: none;
                                        background:none; border:none; border-bottom: 1px solid black;'>
                                        <br>
                                        <button type='submit' name='search' style='margin-top: 10px; padding: 5px 15px;'>Submit</button>
                                    </form>
                                </center>
                            </div>
                        <?php endif;?>
                    </div>
                </div>
            <?php endif;?>
        </div>
        
    </div>
</body>
</html>
<?php 
// Handle payment action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_action'])) {
    switch ($_POST['payment_action']) {
        case 'pay_now':
            $book_title = $_POST['book_title'];
            $book_number = $_POST['book_number'];
            $fine_amount = $_POST['fine'];
            $borrower->addPayment($uid, $uname, $book_title, $book_number, $fine_amount);
                
    echo "<div style='position: fixed; top: 1%; left: 50%; transform: translateX(-50%); height: fit-content; width: 350px; padding: 0 40px; 
                border: 1px solid black; border-radius: 5px; background-color: #709775; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;
                padding-bottom: 30px;'>
                <center><h3 style='color:#e85d04;'>Payment successful</h3></center><br>
                <center><h2>Please wait for the librarian's confirmation.</h2></center></div>";
            header("refresh:2;");
            break;
        case 'return_later':
            header('refresh:1;');
            break;
    } 
}

ob_end_flush();
?>