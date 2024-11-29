<?php
session_start();

include("database/dbCon.php");
include("database/dbOperations.php");

$user = new Operations($conn);

$currentTable = 'books';
$message = "";

// Handle button clicks
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit();
    } elseif (isset($_POST['table'])) {
        $currentTable = $_POST['table'];
    } elseif (isset($_POST['delete_user'])) {
        $uidToDelete = $_POST['uid'];
        if ($user->deleteUser($uidToDelete)) {
            $message = "User deleted successfully.";
            header('refresh:2;');
        } else {
            $message = "Error deleting user.";
            header('refresh:2;');
        }
    } elseif (isset($_POST['add_user'])) {
        header('Location: addUser.php');
        exit();
    } elseif (isset($_POST['tr_hist'])) {
        header('Location: transactionHistory.php');
        exit();
    }

}

// Fetch data from the database based on the current table
$users = $user->getAllUsers();
$books = $user->getAllBooks();
$borrowedBooks = $user->getAllBorrowedBooks();
$booksDue = $user->getBooksDue();
$payments = $user->getAllPayments();

if (isset($_POST['confirm'])) {
    $uid = $_POST['uid'];
    $name = $_POST['uname'];
    $fine = $_POST['fine'];

    $borrowedBooks = $user->getBorrowedBooksFromPayments($uid);

    foreach ($borrowedBooks as $book) {
        $book_number = $book['book_number'];
        $book_title = $book['book_title'];

        $user->returnBook($uid, $book_number, $book_title);

        $user->deletePaymentByBookNumber($uid, $book_number);
    }

    echo "<div style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); height: 120px; width: 450px;
            border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
            <h2 style='color:green; margin-left: 30px;'>Success</h2>
            <center><h3>All borrowed books returned successfully.</h3></center></div>";
    $user->addTransactionRecord($uid, $name, $fine);
    header("refresh:3;");
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Interface</title>
    <link rel="icon" href="favicon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        nav {
            background-color: #4CAF50;
            padding: 15px;
            color: white;
        }
        nav button {
            color: white;
            background: none;
            border: none;
            text-decoration: none;
            margin-right: 20px;
            cursor: pointer;
            font-weight: bolder;
            font-size: 18px;
            border-bottom: 3px solid white;
        }
        nav form {
            display: inline;
        }
        .logout {
            position: fixed;
            right: 3%;
            background: #f44336;
            border-radius: 10px;
            height: 25px;
            width: 100px;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bolder;
            font-size: 18px;
        }
        table {
            margin: 20px;
            width: 95%;
            border-collapse: collapse;
            font-size: 1em;
        }
        th, td {
            padding: 10px;
            text-align: left; 
            border: 1px solid #ccc; 
        }
        th {
            background-color: #4CAF50; 
            color: white; 
        }

        @media (max-width: 600px) {
            nav button {
                font-size: 16px; 
                margin-right: 10px;
            }
            nav {
                text-align: center;
            }
            .logout {
                position: static;
                width: 100%;
                margin-top: 10px;
            }
            table {
                font-size: 0.9em;
                width: 100%;
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <form action="" method="post" style="display: inline;">
            <button class="btn" type="submit" name="table" value="books">Books</button>
            <button class="btn" type="submit" name="table" value="borrowed">Borrowed Books</button>
            <button class="btn" type="submit" name="table" value="due">Books Due</button>
            <button class="btn" type="submit" name="table" value="users">Users</button>
            <button class="btn" type="submit" name="table" value="payment">Payments</button>
            <button style="color: #007B9E;border-bottom: 3px solid #007B9E;" class="btn" type="submit" name="tr_hist">Transaction History</button>
            <button style="color: #007B9E;border-bottom: 3px solid #007B9E;" class="btn" type="submit" name="add_user">Add User</button>
            <button class="btn logout" type="submit" name="logout">Logout</button>
        </form>
    </nav>

    <div class="content">
        <!-- Display message -->
        <?php if ($message): ?>
            <p style="color: green; text-align: center;"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Users Table -->
        <?php if ($currentTable === 'users'): ?>
            <center><h2>Users</h2></center>
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
                    if (!empty($users)) {
                        foreach ($users as $user) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($user['uid']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['uname']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['udept_role']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['uconum']) . "</td>";
                            echo "<td  style='height:50px; width: 150px;'>";
                            echo "<form action='' method='post'  style='height:50px; width: 150px;'>"; // Form for deletion
                            echo "<input type='hidden' name='uid' value='" . htmlspecialchars($user['uid']) . "' />"; // Hidden input to store uid
                            echo "<button style='height:100%;width: 100%;border:none;border-radius: 10px; background: #f44336; font-size: 15px; font-weight: bolder;' 
                                    class='btn' type='submit' name='delete_user'>Delete</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No users registered.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Books Table -->
        <?php if ($currentTable === 'books'): ?>
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
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No books available.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Borrowed Books Table -->
        <?php if ($currentTable === 'borrowed'): ?>
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
                if (!empty($borrowedBooks)) {
                    $today = date('Y-m-d');
                    foreach ($borrowedBooks as $borrowedBook) {
                        $isOverdue = $borrowedBook['date_to_return'] < $today;
                        $style = $isOverdue ? ' style="color:red;"' : '';
                        echo "<tr$style>";
                        echo "<td>" . htmlspecialchars($borrowedBook['uid']) . "</td>";
                        echo "<td>" . htmlspecialchars($borrowedBook['uname']) . "</td>";
                        echo "<td>" . htmlspecialchars($borrowedBook['book_title']) . "</td>";
                        echo "<td>" . htmlspecialchars($borrowedBook['book_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($borrowedBook['date_borrowed']) . "</td>";
                        echo "<td>" . htmlspecialchars($borrowedBook['date_to_return']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No borrowed books.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>

        <!-- Books due table -->
        <?php if($currentTable === 'due'): ?>
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
                    if (!empty($booksDue)) {
                        foreach ($booksDue as $book) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($book['uid']) . "</td>";
                            echo "<td>" . htmlspecialchars($book['book_title']) . "</td>";
                            echo "<td>" . htmlspecialchars($book['book_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($book['date_borrowed']) . "</td>";
                            echo "<td>" . htmlspecialchars($book['date_to_return']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No books due.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Payment table -->
        <?php if($currentTable === 'payment'):?>
        <form method="POST" action="">
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
                        echo "<td style='height:50px; width: 150px;'>
                            <form method='POST' action=''>
                                <input type='hidden' name='uid' value='{$payment['uid']}'>
                                <input type='hidden' name='uname' value='{$payment['uname']}'>
                                <input type='hidden' name='fine' value='{$payment['total_fine']}'>
                                <button type='submit' name='confirm' 
                                style='background-color:#4CAF50; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:5px;'>
                                    Confirm Payment
                                </button>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No pending payments.</td></tr>";
                }
                ?>
            </table>
        </form>
    <?php endif; ?>
    </div>
</body>
</html>