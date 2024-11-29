<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: index.php"); 
    exit();
}

include("database/dbCon.php");
include("database/dbOperations.php");

$user = new Operations($conn);

$confirmationVisible = false; 
$book_number = '';
$book_title = '';
$date_to_return = '';
$searchQuery = '';

// Initialize messages
$errorMessage = '';
$successMessage = '';

// Check if the borrow request has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_borrow'])) {
    $book_number = $_POST['book_number'];
    $book_title = $_POST['book_title'];

    // Get the user ID from the session
    $uid = $_SESSION['uid'];

    // Check the number of borrowed books
    $borrowedBooks = $user->getBorrowedBooks($uid);
    $borrowedCount = count($borrowedBooks);
    
    // Check whether the user exceeds the limit of 5 borrowed books
    if ($borrowedCount < 5) {
        $confirmationVisible = true;
    } else {
        $_SESSION['warning_message'] = "You have reached the borrowing limit of 5 books. <br>
                                        Please return a book before borrowing more.";
    }
}

// Handle final borrowing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['final_borrow'])) {
    $uid = $_SESSION["uid"]; 
    $uname = $_SESSION["uname"];
    $book_number = $_POST['confirm_book_number'];
    $book_title = $_POST['confirm_book_title'];
    $date_to_return = $_POST['date_to_return']; 

    // Check if user has already borrowed this book
    if ($user->hasBorrowedBook($uid, $book_number)) {
        $errorMessage = "You have already borrowed this book.";
    } else {
        // Attempt to borrow the book
        if ($user->updateBookAvailability($book_number) && 
            $user->borrowBook($uid, $uname, $book_number, $book_title, $date_to_return)) {
            $successMessage = "You have successfully borrowed the book.";
        } else {
            $errorMessage = "Failed to borrow the book. Please try again.";
        }
    }
    $confirmationVisible = false; 
}

$selectedGenre = isset($_POST['genre']) ? $conn->real_escape_string($_POST['genre']) : '';
$searchQuery = isset($_POST['search_query']) ? $conn->real_escape_string($_POST['search_query']) : '';

if (empty($selectedGenre) && empty($searchQuery)) {
    $availableBooks = $user->getAvailableBooks(11);
} elseif (!empty($selectedGenre)) {
    $availableBooks = $user->getAvailableBooksByGenre($selectedGenre);
} else { 
    $availableBooks = $user->searchAvailableBooks($searchQuery);
}


// Check for warning message
$warningMessage = isset($_SESSION['warning_message']) ? $_SESSION['warning_message'] : '';
unset($_SESSION['warning_message']);

// Display messages
if ($errorMessage) {
    echo "<div style='position: absolute; top: 20px; left: 50%; transform: translateX(-50%); height: 170px; width: 350px;
        border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
        <center><h3 style='color:red;'>Warning</h3></center><br>
        <center><h4>$errorMessage</h4></center></div>";
        header("refresh:3;");
}

if ($successMessage) {
    echo "<div style='position: absolute; top: 20px; left: 50%; transform: translateX(-50%); height: 170px; width: 350px;
        border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
        <center><h3 style='color:green;'>Success</h3></center><br>
        <center><h4>$successMessage</h4></center></div>";
        header("refresh:1.5;");
}

if ($warningMessage) {
    echo "<div style='position: absolute; top: 20px; left: 50%; transform: translateX(-50%); height: 170px; width: 500px;
        border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8); z-index: 1000;'>
        <center><h3 style='color:red;'>Warning</h3></center><br>
        <center><h4>$warningMessage</h4></center></div>";
        header("refresh:3;");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Books</title>
    <link rel="icon" href="imgs/favicon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2; 
            display: flex;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 200px;
            height: 100%;
            background-color: #4CAF50;
            padding: 20px;
            color: white;
            overflow-y: auto;
        }
        .sidebar button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px;
            margin: 0 0 5px 0;
            cursor: pointer;
            font-size: 12px;
            border-bottom: 1px black solid;
        }
        .sidebar button:hover {
            background-color: #45a049;
        }
        .content {
            margin-left: 220px; 
            width: calc(100% - 220px);
        }
        .search{
            position: absolute;
            top: 1.3vh;
            right: 5vw;
            width: 20vw;
            display: flex;
            flex-direction: row;
            gap: 5%;
        }
        .search input{
            width: 75%;
        }
        .search button{
            background: #008CBA;
            width: 25%;
            border: none;
            border-radius: 10px;
            padding: 0;
        }
        .search button:hover{
            background-color: #007B9E; 
        }
        table {
            margin: 20px auto;
            width: 90%;
            font-size: 1.2em;
        }
        th, td {
            padding: 15px;
            text-align: left; 
            border: 1px solid #ccc; 
        }
        th {
            background-color: #4CAF50; 
            color: white; 
        }
        .acol{
            padding: 0;
        }
        input[type="date"], input[type="text"], input[type="submit"] {
            width: 100%; 
            padding: 10px;
            margin: 5px 0; 
            border: 1px solid #ccc;
            border-radius: 5px; 
        }
        input[type="submit"], button {
            background-color: #4CAF50; 
            color: white; 
            cursor: pointer; 
            width: 100%;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #45a049; 
        }
        .action-btn {
            background-color: #008CBA;
            color: white; 
            border: none; 
            padding: 15px; 
            cursor: pointer; 
            border-radius: 5px; 
            width: 100%;
        }
        .action-btn:hover {
            background-color: #007B9E;
        }
        #backbtn {
            background: #f44336;
            border-radius: 10px;
            padding: 15px;
            width: 100%;
        }
        #backbtn:hover {
            background: #f0382e;
        }
        .confirmation-dialog {
            position: fixed; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
            width: 400px; 
            padding: 20px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            background-color: #fff; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); 
            z-index: 1000; 
        }
        .confirmation-dialog h2 {
            margin: 0 0 20px; 
            text-align: center; 
        }
        .confirmation-dialog button {
            padding: 15px;
            margin-top: 20px; 
            border-radius: 5px; 
            width: 100%;
        }
        .confirmation-dialog input {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>GENRES</h3>
        <form action="" method="post">
            <button type="submit" name="genre" value="adventure">ADVENTURE</button>
            <button type="submit" name="genre" value="comedy">COMEDY</button>
            <button type="submit" name="genre" value="dystopian">DYSTOPIAN</button>
            <button type="submit" name="genre" value="fiction">FICTION</button>
            <button type="submit" name="genre" value="historical fiction">HISTORICAL FICTION</button>
            <button type="submit" name="genre" value="literary fiction">LITERARY FICTION</button>
            <button type="submit" name="genre" value="political satire">POLITICAL SATIRE</button>
            <button type="submit" name="genre" value="romance">ROMANCE</button>
            <button type="submit" name="genre" value="southern gothic">SOUTHERN GOTHIC</button>
            <button type="submit" name="genre" value="thriller">THRILLER</button>
            <button type="submit" name="genre" value="war fiction">WAR FICTION</button>
        </form>
        <br><br><br>
        <form action="borrowerInterface.php" method="post">
            <button id="backbtn" type="submit">BACK</button>
        </form>
    </div>
    
    <div class="content">
        <center><h1>Available Books</h1></center>

        <!-- Search Form -->
        <form class="search" action="" method="post">
            <input type="text" name="search_query" placeholder="Search by title or author" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit" value='search'>Search</button>
        </form>

        <?php if ($confirmationVisible): ?>
            <div class="confirmation-dialog">
                <h2>Confirm Borrowing</h2>
                <p>Book Number: <?php echo htmlspecialchars($book_number); ?></p>
                <p>Book Title: <?php echo htmlspecialchars($book_title); ?></p>
                <form action='' method='post'>
                    <input type='hidden' name='confirm_book_number' value='<?php echo htmlspecialchars($book_number); ?>'>
                    <input type='hidden' name='confirm_book_title' value='<?php echo htmlspecialchars($book_title); ?>'>
                    <div>
                        <label for='date_to_return'>Date to Return:</label>
                        <input style="width: 35%;" type='date' name='date_to_return' disabled value="<?php echo date('Y-m-d', timestamp: strtotime('+1 week')); ?>">
                        <input type="date" hidden name='date_to_return' value="<?php echo date('Y-m-d', timestamp: strtotime('+1 week')); ?>">
                    </div>
                    <button type='submit' name='final_borrow' class='action-btn'>Confirm Borrow</button>
                </form>
                <form action='borrowBook.php' method='post'>
                    <button type='submit' class='action-btn' style='background-color: #f44336;'>Cancel</button>
                </form>
            </div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Book Number</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Year Published</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($availableBooks as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['book_number']); ?></td>
                        <td><?php echo htmlspecialchars($book['book_title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['year_published']); ?></td>
                        <td class="acol">
                            <form action="" method="post">
                                <input type="hidden" name="book_number" value="<?php echo htmlspecialchars($book['book_number']); ?>">
                                <input type="hidden" name="book_title" value="<?php echo htmlspecialchars($book['book_title']); ?>">
                                <button type="submit" name="confirm_borrow" class="action-btn">Borrow</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
