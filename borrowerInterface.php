<?php
ob_start();
session_start();

if (!isset($_SESSION['uname'])) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION["uid"];
$uname = $_SESSION['uname'];
$udept_role = $_SESSION['udept_role'];
$uconum = $_SESSION['uconum'];
$uaddress = $_SESSION['uaddress'];

include("database/dbCon.php");
include("database/dbOperations.php");

$user = new Operations($conn);

// Fetch borrowed books
$borrowedBooks = $user->getBorrowedBooks($uid);

$returnFormVisible = false;

$book_number = '';
$book_title = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $book_number = $_POST['bNum'] ?? '';
        $book_title = $_POST['bTitle'] ?? '';

        if (!empty($book_number)) {
            // Search by book_number
            $book_title = $user->searchBookByNumber($book_number);
        } elseif (!empty($book_title)) {
            // Search by book_title
            $book_number = $user->searchBookByTitle($book_title);
        }

        if ($book_title === null || $book_number === null) {
            $searchError = "No book found. Please check the details and try again.";
        }
        // Keep return form visible after search
        $returnFormVisible = true; // Set to true to keep it visible
    } elseif (isset($_POST['button'])) {
        switch ($_POST['button']) {
            case 'borrow':
                if (count($borrowedBooks) < 5) {
                    header('Location: borrowBook.php');
                    exit();
                } else {
                    echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 150px; width: 450px;
                        border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                        <h3 style='color:red;'>Warning</h3>
                        <center><h2>You have reached the borrowing limit. Return some of the books first.</h2></center></div>";
                    header("refresh:3;");
                }
                break;
            case 'return':
                $returnFormVisible = true; // Set to true to display the return form
                break;
            case 'logout':
                session_unset(); 
                session_destroy();
                header("Location: index.php");
                exit();
        }
    }

    // Handle return book logic
    if (isset($_POST['return'])) {
        $book_number = $_POST['bNum'];
        $book_title = $_POST['bTitle'];

        $date = $user->fetchDateToReturn($uid, $book_number);

        if ($date && strtotime($date)) { 
            $pastDue = (new DateTime($date)) < (new DateTime());

            if ($pastDue && $user->hasPayment($uid, $book_number) === false) {
                $fine = $user->calculateFine($date);
                echo "
                <div class='confirmDialog'>
                    <h3 style='color:orange;'>Notice</h3>
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
            } elseif ($user->hasPayment($uid, $book_number)) {
                echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 120px; width: 450px;
                    border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                    <h2 style='color:red; margin-left: 30px;'>Payment Already Submitted</h2>
                    <center><h3>Please wait for the librarian's confirmation.</h3></center></div>";
                header("refresh:3;");
            } else {
                if ($user->returnBook($uid, $book_number, $book_title)) {
                    echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 120px; width: 450px;
                        border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                        <h2 style='color:green; margin-left: 30px;'>Success</h2>
                        <center><h3>Book return successful.</h3></center></div>";
                    header("refresh:2;");
                } else {
                    echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 120px; width: 550px;
                        border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                        <h2 style='color:red; margin-left: 30px;'>Warning</h2>
                        <center><h3>Book return failed. Please make sure you've entered the correct details.</h3></center></div>";
                    header("refresh:2;");
                }
            }
        } else {
            echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 120px; width: 550px;
                border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                <h2 style='color:red; margin-left: 30px;'>Error</h2>
                <center><h3>Invalid book details. Please check and try again.</h3></center></div>";
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
    <title>Library Interface</title>
    <link rel="icon" href="imgs/favicon.png">
</head>
<style>
    .libCard {
        background: #040F0F;
        margin: 10px 0 0 10px;
        border: 3px solid #2D3A3A;
        color: #FCFFFC;
        border-radius: 10px;
        height: 350px;
        width: 600px;
    }
    .libCard h2, h3 {
        margin: 20px 0 0 30px;
    }
    .btn {
        background: #2BA84A;
        padding: 15px 25px;
        font-size: 20px;
        font-weight: bolder;
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 13px black;
    }
    .btn:hover{
        background: #248232;
    }
    .btnCont {
        margin-top: 15px;
        margin-left: 180px;
        width: 250px;
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    .borBooks{
        background: #040F0F;
        position: absolute;
        top: 10px;
        left: 630px;
        border: 3px solid #2D3A3A;
        border-radius: 10px;
        height: 350px;
        width: 720px;
        color: #FCFFFC;
    }
    .borBooks table{
        width: 95%;
    }
    .borBooks h3{
        border-bottom: 1px solid black;
        width: 250px;
    }
    .borBooks table, th, tr, td{
        border: 1px solid #FCFFFC;
    }
    .returnBookForm{
        background: #040F0F;
        border: 3px solid #2D3A3A;
        color: #FCFFFC;
        position: absolute;
        top: 380px;
        right: 130px;
        height: 200px;
        width: 500px;
        border-radius: 20px;
        display: none;
        font-size: 18px;
    }
    .returnBookForm input{
        outline: none;
        border: none;
        border-bottom: 2px solid #FCFFFC;
        font-size: 18px;
        font-weight: bold;
        background: none;
        color: #FCFFFC;
    }
    .returnBookForm button{
        background: #2BA84A;
        padding: 10px 20px;
        font-weight: bolder;
        border: none;
        border-radius: 10px;
        font-size: 15px;
    }
    .returnBookForm button:hover{
        background: #248232;
    }
    .confirmDialog{
        position: fixed; 
        top: 20px; 
        left: 50%; 
        transform: translateX(-50%); 
        height: 250px; 
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
        background: #2BA84A;
        margin-right: 50px;
    }
    .pay:hover{
        background: #248232;
    }
    .later{
        background: #f44336;
    }
    .later:hover {
        background: #b52821;
    }
</style>
<body>
    <div class="libCard">
        <center><h1>LIBRARY CARD</h1></center>
        <h2 style="margin-top: 50px;">Card ID: <?php echo htmlspecialchars($uid); ?></h2>
        <h2 style="margin-right: 50px;">Name: <?php echo htmlspecialchars($uname); ?></h2>
        <h3>Department & Role: <?php echo htmlspecialchars($udept_role); ?></h3>
        <h3>Contact Number: <?php echo htmlspecialchars($uconum); ?></h3>
        <h3>Address: <?php echo htmlspecialchars($uaddress); ?></h3>
    </div>

    <form class="btnCont" method="post">
        <button class="btn brw" type="submit" name="button" value="borrow">Borrow Books</button>
        <button class="btn rtn" type="submit" name="button" value="return">Return Books</button>
        <button class="btn lgt" type="submit" name="button" value="logout">Log Out</button>
    </form>

    <div class="borBooks">
        <center><h3>BOOKS BORROWED</h3></center><br>
        <center>
            <table>
                <tr>
                    <th>Book Number</th>
                    <th>Book Title</th>
                    <th>Date Borrowed</th>
                    <th>Date To Return</th>
                </tr>
                <?php

                if (!empty($borrowedBooks)) {
                    foreach ($borrowedBooks as $book) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($book['book_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($book['book_title']) . "</td>";
                        echo "<td>" . htmlspecialchars($book['date_borrowed']) . "</td>";
                        echo "<td>" . htmlspecialchars($book['date_to_return']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No borrowed books yet.</td></tr>";
                }
                ?>
            </table>
        </center>
    </div>

    <?php if ($returnFormVisible): ?>
        <div class="returnBookForm" style="display: block;"> 
            <form action="" method="post">
                <center><h3 style="font-size: 25px;">RETURN BOOK</h3></center>
                <center>
                    <div style="margin-top:20px">
                        <label for="bNum">Book Number : </label>
                        <input style="margin-left: 30px;" type="text" name="bNum" value="<?php echo htmlspecialchars($book_number); ?>">
                    </div>
                </center>
                <center>
                    <div style="margin-top:10px">
                        <label for="bTitle">Book Title : </label>
                        <input style="margin-left: 56px;" type="text" name="bTitle" value="<?php echo htmlspecialchars($book_title); ?>">
                    </div>
                </center>
                <br>
                <center>
                    <button style="padding: 10px 30px;" type="submit" name="search">Search Book</button>
                    <button style="padding: 10px 30px;" type="submit" name="return">Return Book</button>
                </center>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>

<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['button'])) {
            switch ($_POST['button']) {
                case 'borrow':
                    if(count($borrowedBooks) < 5){
                        header('Location: borrowBook.php');
                        exit();
                    }else{
                        echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 150px; width: 450px;
                            border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                            <h3 style='color:red;'>warning</h3>
                            <center><h2>You have reached the borrowing limit. Return some of the books first.</h2></center></div>";
                        header("refresh:3;");
                    }
                    break;
                case 'return':
                    $returnFormVisible = true;
                    break;
                case 'logout':
                    session_unset(); 
                    session_destroy();
                    header("Location: index.php");
                    exit();
            }
        }
    }

    // Handle payment action
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_action'])) {
        switch ($_POST['payment_action']) {
            case 'pay_now':
                $book_title = $_POST['book_title'];
                $book_number = $_POST['book_number'];
                $fine_amount = $_POST['fine'];
                $user->addPayment($uid, $uname, $book_title, $book_number, $fine_amount);
                    
                echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 120px; width: 450px;
                    border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                    <h2 style='color:green; margin-left: 30px;'>Payment Successful</h2>
                    <center><h3>Please wait for the librarian's confirmation.</h3></center></div>";
                header("refresh:2;");
                break;
            case 'return_later':
                header('refresh:1;');
                break;
        } 
    }

    ob_end_flush();

?>
