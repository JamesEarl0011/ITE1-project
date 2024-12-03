<?php
    session_start();

    require __DIR__ . "/database/db_connection.php";
    require_once __DIR__ . "/operations/admin_operations.php";
    require_once __DIR__ . "/operations/borrower_operations.php";

    $admin = new Admin_operations($conn);
    $borrower = new Borrower_operations($conn);

    $currentTable = "books";

    if(isset($_POST["logout"])){
        session_destroy();
        header("location: index.php");
        exit;
    }
    if(isset($_POST["transactions"])){
        header("location: transactionHistory.php");
        exit;
    }
    if(isset($_POST["adduser"])){
        header("location: addBorrower.php");
        exit;
    }
    if(isset($_POST["table"])){
        $currentTable = $_POST["table"];
    }

//FETCH ALL TABLE CONTENTS
    $books = $admin->getAllBooks();
    $bbooks = $admin->getAllBorrowedBooks();
    $dbooks = $admin->getBooksDue();
    $borrowers = $admin->getAllUsers();
    $payments = $admin-> getAllPayments();

//OTHER PROCESSES
    //user deletion
    if(isset($_POST["delete_user"])){
        $uidToDelete = $_POST["uid"];
        if($admin->deleteUser($uidToDelete)){
            echo 
            "
                <div style='position: fixed; top: 15%; left: 50%; transform: translateX(-50%);background:#A1CCA5;height: 7vh;
                width: 30vw; border: 3px solid #111D13; border-radius: 20px;color: #111D13;padding-bottom: 30px;'>
                    <center><h2 style='color: #e85d04'>SUCCESSFUL</h2><br><h3>Account deleted successfully.</h3></center>
                </div>
            ";
            header("refresh: 2;");
        }else{
            echo 
            "
                <div style='position: fixed; top: 15%; left: 50%; transform: translateX(-50%);background:#A1CCA5;height: 7vh;
                width: 30vw; border: 3px solid #111D13; border-radius: 20px;color: #111D13;padding-bottom: 30px;'>
                    <center><h3 style='color: #e63946'>FAILURE</h3><br><h4>Account deletion failed.</h4></center>
                </div>
            ";
            header("refresh: 2;");            
        }
    }

    //payment confirmation
    if(isset($_POST["confirm"])){
        $uid = $_POST["uid"];
        $name = $_POST["uname"];
        $fine = $_POST["fine"];

        $borrowedBooks = $admin->getBorrowedBooksFromPayments($uid);

        foreach($borrowedBooks as $book){
            $book_number = $book["book_number"];
            $book_title = $book["book_title"];

            $borrower->returnBook($uid,$book_number, $book_title);

            $admin->deletePaymentByBookNumber($uid,$book_number);

            echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 120px; width: 450px;
                    border: 1px solid #ccc; border-radius: 5px; background-color: #A1CCA5; color: #111D13; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
                    <h2 style='color:#e85d04; margin-left: 30px;'>Success</h2>
                    <center><h3>All borrowed books returned successfully.</h3></center></div>";
            $admin->addTransactionRecord($uid, $name, $fine);
            header("refresh:3;");
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface</title>
    <link rel="icon" href="imgs/favicon.png">
</head>
<style>
    *{
        margin: 0;
        padding: 0;
        font-family: 'Rockwell';
    }
    body{
        background: #A1CCA5;
    }
    nav{
        display: flex;
        flex-direction: row;
    }
    nav h2{
        margin: 10px 0 0 10px;
    }
    .lbtn{
        position: absolute;
        top: 2.5px;
        right: 20px;
        padding: 5px 10px;
        font-size: .8em;
        border-radius: 10px;
        background: #1f3322;
        color: #fff;
    }
    .container{
        height: fit-content;
        width: 100%;
        background: #709775;
    }
    .btns{
        width: 100%;
    }
    .btn{
        margin-top: 10px;
        height: fit-content;
        width: 14%;
        font-size: 1em;
        padding: 5px 10px;
        border-radius: 10px;
        background: #111D13;
        color: #A1CCA5;
    }
    .content{
        margin-top: 20px;
    }
    table {
            margin: 20px;
            width: 95%;
            border-collapse: collapse;
            font-size: 1em;
        }
    th, td {
            padding: 10px;
            text-align: center; 
            border: 1px solid #ccc; 
            font-weight: bold;
        }
    th {
            text-align: center;
            background-color: #2e8533; 
            color: white; 
        }
</style>
<body>
    <nav>
        <h2>Library Admin</h2>
        <form method="post"><button class="lbtn" type="submit" name="logout">Log out</button></form>
    </nav>
    <center>
        <div class="container">
            <form class="btns" method="post">
                <button class="btn" type="submit" name="table" value="books">Books</button>
                <button class="btn" type="submit" name="table" value="borrowedbooks">Borrowed Books</button>
                <button class="btn" type="submit" name="table" value="booksdue">Books Due</button>
                <button class="btn" type="submit" name="table" value="users">Borrowers</button>
                <button class="btn" type="submit" name="table" value="payments">Payments</button>
                <button class="btn" type="submit" name="transactions" style="color: #8ecae6;">Transaction History</button>
                <button class="btn" type="submit" name="adduser" style="color: #8ecae6;">Add Borrower</button>
            </form>
            <div class="content">
<!-- CONTENT for books table -->
                <?php if($currentTable == "books") : ?>
                    <center><h2>Books</h2></center>
                    <table>
                        <thead>
                            <tr>
                                <th>Book Number</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Year Published</th>
                                <th>Genre</th>
                                <th>Available Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($books)) {
                                foreach ($books as $book) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($book['book_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['book_title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['author']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['year_published']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['genre']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['book_available']) . "</td>";
                                    echo "</tr> <style> .content{padding-bottom: 1vh;} </style>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No books available.</td></tr> <style> .content{padding-bottom: 5vh;} </style>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>

<!-- CONTENT for borrowed books table             -->
                <?php if($currentTable == "borrowedbooks") : ?>
                    <center><h2>Borrowed Books</h2></center>
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Borrower Name</th>
                                <th>Book Title</th>
                                <th>Book Number</th>
                                <th>Date Borrowed</th>
                                <th>Date to Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($bbooks)) {
                                $today = date('Y-m-d');
                                foreach ($bbooks as $bbook) {
                                    $isOverdue = $bbook['date_to_return'] < $today;
                                    $style = $isOverdue ? ' style="color:red;"' : '';
                                    echo "<tr$style>";
                                    echo "<td>" . htmlspecialchars($bbook['uid']) . "</td>";
                                    echo "<td>" . htmlspecialchars($bbook['uname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($bbook['book_title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($bbook['book_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($bbook['date_borrowed']) . "</td>";
                                    echo "<td>" . htmlspecialchars($bbook['date_to_return']) . "</td>";
                                    echo "</tr> <style> .content{padding-bottom: 1vh;} </style>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No borrowed books.</td></tr> <style> .content{padding-bottom: 5vh;} </style>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>

<!-- CONTENT for books due table -->
                <?php if($currentTable == "booksdue") : ?>
                    <center><h2>Books due Today (<?php echo date('Y-m-d');?>)</h2></center>
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Book Title</th>
                                <th>Book Number</th>
                                <th>Date Borrowed</th>
                                <th>Date to Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($dbooks)) {
                                foreach ($dbooks as $book) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($book['uid']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['book_title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['book_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['date_borrowed']) . "</td>";
                                    echo "<td>" . htmlspecialchars($book['date_to_return']) . "</td>";
                                    echo "</tr> <style> .content{padding-bottom: 1vh;} </style>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No books due.</td></tr> <style> .content{padding-bottom: 5vh;} </style>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>

<!-- CONTENT for borrowers table -->
                <?php if($currentTable == "users") : ?>
                    <center><h2>Borrowers</h2></center>
                    <table>
                        <thead>
                            <tr>
                                <th>Card Id Number</th>
                                <th>Name</th>
                                <th>Department & Role</th>
                                <th>Contact Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($borrowers)) {
                                foreach ($borrowers as $borrower) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($borrower['uid']) . "</td>";
                                    echo "<td>" . htmlspecialchars($borrower['uname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($borrower['udept_role']) . "</td>";
                                    echo "<td>" . htmlspecialchars($borrower['uconum']) . "</td>";
                                    echo "<td>";
                                    echo "<form action='' method='post'>";
                                    echo "<input type='hidden' name='uid' value='" . htmlspecialchars($borrower['uid']) . "' />";
                                    echo "<button style='margin-top: -1px;height:100%;width: 100%;border:none;border-radius: 10px; background: #6a040f; font-size: 15px; font-weight: bolder;' 
                                            class='btn' type='submit' name='delete_user'>Delete</button>";
                                    echo "</form>";
                                    echo "</td>";
                                    echo "</tr> <style> .content{padding-bottom: 1vh;} </style>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No users registered.</td></tr> <style> .content{padding-bottom: 5vh;} </style>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>

<!-- CONTENT for payments table -->
                <?php if($currentTable == "payments") : ?>
                    <form method="POST">
                    <center><h2>Payments</h2></center>
                        <table class='ptb'>
                            <tr>
                                <th>UID</th>
                                <th>Borrower Name</th>
                                <th>Total Fine</th>
                                <th>Action</th>
                            </tr>
                            <?php
                            if (!empty($payments)) {
                                foreach ($payments as $payment) {
                                    echo "<tr>";
                                    echo "<td>{$payment['uid']}</td>";
                                    echo "<td>{$payment['uname']}</td>";
                                    echo "<td>{$payment['total_fine']} Php</td>";
                                    echo "<td>
                                        <form method='POST' action=''>
                                            <input type='hidden' name='uid' value='{$payment['uid']}'>
                                            <input type='hidden' name='uname' value='{$payment['uname']}'>
                                            <input type='hidden' name='fine' value='{$payment['total_fine']}'>
                                            <button type='submit' name='confirm' style='background: #6a040f; color:white; border:none; padding:10px 20px; 
                                            cursor:pointer; border-radius:5px;'>
                                                Confirm Payment
                                            </button>
                                        </form>
                                        </td>";
                                    echo "</tr> <style> .content{padding-bottom: 1vh;} </style>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No pending payments.</td></tr> <style> .content{padding-bottom: 5vh;} </style>";
                            }
                            ?>
                        </table>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </center>
</body>
</html>