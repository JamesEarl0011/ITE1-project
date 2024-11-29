<?php
class Operations {
    private $conn;

    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to check if the user exists by userID (uid)
    public function userExists($userID) {
        $userID = $this->conn->real_escape_string($userID);

        $sql = "SELECT * FROM users WHERE uid = '$userID'";
        $result = $this->conn->query($sql);

        return $result->num_rows > 0;
    }

    // Method to check if the provided pin matches the user ID
    public function validatePin($userID, $userPin) {
        $userID = $this->conn->real_escape_string($userID);
        $userPin = $this->conn->real_escape_string($userPin);

        $sql = "SELECT * FROM users WHERE uid = '$userID' AND upin = '$userPin'";
        $result = $this->conn->query($sql);

        return $result->num_rows > 0;
    }

    // Method to fetch user details by userID
    public function getUserDetails($userID) {
        $userID = $this->conn->real_escape_string($userID);

        $sql = "SELECT * FROM users WHERE uid = '$userID'";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); 
        } else {
            return null; 
        }
    }

    // Method to fetch borrowed books for the logged-in user
    public function getBorrowedBooks($uid) {
        $uid = $this->conn->real_escape_string($uid);

        $sql = "SELECT book_title, book_number, date_borrowed, date_to_return 
                FROM borrowed_books 
                WHERE uid = ?";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $uid); 
        $stmt->execute();
        $result = $stmt->get_result();

        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        $stmt->close();
        return $books;
    }
    
    // Method to search for a book by its number
    public function searchBookByNumber($book_number) {
        $stmt = $this->conn->prepare("SELECT book_title FROM books WHERE book_number = ?");
        $stmt->bind_param("s", $book_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['book_title'];
        }
        return null;
    }

    // Method to search for a book by its title
    public function searchBookByTitle($book_title) {
        $stmt = $this->conn->prepare("SELECT book_number FROM books WHERE book_title = ?");
        $stmt->bind_param("s", $book_title);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['book_number'];
        }
        return null;
    }


    //method for the return of books
    public function returnBook($uid, $book_number, $book_title) {
        $query = "SELECT * FROM borrowed_books WHERE uid = ? AND book_number = ? AND book_title = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $uid, $book_number, $book_title);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $deleteQuery = "DELETE FROM borrowed_books WHERE uid = ? AND book_number = ? AND book_title = ?";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bind_param("sss", $uid, $book_number, $book_title);
            $deleteStmt->execute();
    
            $updateQuery = "UPDATE books SET book_available = book_available + 1 WHERE book_number = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $book_number);
            $updateStmt->execute();
    
            return true;
        }
    
        return false;
    }

    //method to check whether the returned book is past due
    public function isPastDue($uid, $bookNumber) {
        $currentDate = date('Y-m-d');
        $date_to_return = date('Y-m-d');

        $sql = "SELECT date_to_return FROM borrowed_books WHERE uid = ? AND book_number = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $uid, $bookNumber);
        $stmt->execute();
        $stmt->bind_result($date_to_return);
        $stmt->fetch();
        $stmt->close();
    
        if ($date_to_return) {
            return $currentDate > $date_to_return;
        }
        return false;
    }

    //method to get return date
    public function fetchDateToReturn($uid, $book_number) {
        // SQL query to fetch the date_to_return for the given uid and book_number
        $query = "SELECT date_to_return FROM borrowed_books WHERE uid = ? AND book_number = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $uid, $book_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['date_to_return'];
        }
        return null;
    }
    
    //method to calculate fine amount
    public function calculateFine($dateToReturn) {
        $returnDate = new DateTime($dateToReturn);
        $currentDate = new DateTime();

        $interval = $currentDate->diff($returnDate);

        if ($returnDate < $currentDate) {
            $daysPastDue = $interval->days;
            $fineAmount = $daysPastDue * 200;
            return $fineAmount;
        }

        return 0;
    }
    // Method to check if payment already exists for a user and book title
    public function hasPayment($uid, $book_number) {
        $sql = "SELECT * FROM payment WHERE uid = ? AND book_number = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $uid, $book_number);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return true;
        }
        
        return false;
    }

    //payment method for the fine
    public function addPayment($uid, $uname, $book_title, $book_number, $fine_amount) {
        // SQL query to insert data into the payment table
        $sql = "INSERT INTO payment (uid, uname, book_title, book_number, fine_amount)
                VALUES (?, ?, ?, ?, ?)";
    
        // Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssd", $uid, $uname, $book_title, $book_number, $fine_amount);
    
        // Check if the query was successful
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }    

    // Method to get all available books
    public function getAvailableBooks($limit = null) {
        $query = "SELECT * FROM books WHERE book_available > 0";
        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Method to borrow a book
    public function borrowBook($uid, $uname, $book_number, $book_title, $date_to_return) {
        $uid = $this->conn->real_escape_string($uid);
        $uname = $this->conn->real_escape_string($uname);
        $book_number = $this->conn->real_escape_string($book_number);
        $book_title = $this->conn->real_escape_string($book_title);
        $date_to_return = $this->conn->real_escape_string($date_to_return);

        $sql = "INSERT INTO borrowed_books (uid, uname, book_number, book_title, date_borrowed, date_to_return) 
                VALUES (?, ?, ?, ?, NOW(), ?)";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $uid, $uname, $book_number, $book_title, $date_to_return);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    // Filter out the books using sidebar menu
    public function getAvailableBooksByGenre($genre) {
        $sql = "SELECT * FROM books WHERE genre = '$genre' AND book_available > 0";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //method to check whether borrower has already borrowed the book
    public function hasBorrowedBook($uid, $book_number) {
        $query = "SELECT * FROM borrowed_books WHERE uid = ? AND book_number = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $uid, $book_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    //method for the search function
    public function searchAvailableBooks($searchQuery) {
        $sql = "SELECT * FROM books WHERE book_available > 0 AND (book_title LIKE ? OR author LIKE ?) LIMIT 12";
        $stmt = $this->conn->prepare($sql);
        $searchTerm = "%" . $searchQuery . "%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update book availability book_ after user borrow
    public function updateBookAvailability($book_number) {
        $stmt = $this->conn->prepare("UPDATE books SET book_available = book_available - 1 WHERE book_number = ?");
        $stmt->bind_param("s", $book_number);
        return $stmt->execute();
    }
    
    // Method to fetch all registered users' details (uid, uname, udept_role, uconum)
    public function getAllUsers() {
        $sql = "SELECT uid, uname, udept_role, uconum FROM users";
    
        $result = $this->conn->query($sql);

        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }


    //get books values for inventory
    public function getAllBooks() {
        $sql = "SELECT * FROM books";
        $result = $this->conn->query($sql);

        $books = [];

        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        return $books;
    }

    // Method to get all borrowed books for inventory
    public function getAllBorrowedBooks() {
        $sql = "SELECT * FROM borrowed_books"; 
        $result = $this->conn->query($sql); 

        $borrowedBooks = [];

        while ($row = $result->fetch_assoc()) {
            $borrowedBooks[] = $row; 
        }

        return $borrowedBooks;
    }

    // Method to get books due today
    public function getBooksDue() {
        $curDate = date('Y-m-d');

        $sql = "SELECT * FROM borrowed_books WHERE date_to_return = ?";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $curDate); 
        $stmt->execute();
        $result = $stmt->get_result();

        $dueBooks = [];

        while ($row = $result->fetch_assoc()) {
        $dueBooks[] = $row;
        }

        $stmt->close();

        return $dueBooks;
    }

    // Method to get all payments, grouped by uid with total fine amount
    public function getAllPayments() {
        $sql = "SELECT uid, uname, SUM(fine_amount) AS total_fine
                FROM payment
                GROUP BY uid, uname";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        return $payments;
    }
        
    // Method to delete a user by uid
    public function deleteUser($uid) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE uid = ?");
        $stmt->bind_param("s", $uid);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    //method to add new user
    public function addUser($uid, $upin, $uname, $udept_role, $uconum, $address) {
        $checkUidQuery = "SELECT * FROM users WHERE uid = ?";
        $stmt = $this->conn->prepare($checkUidQuery);
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            return false;
        }
    
        $query = "INSERT INTO users (uid, upin, uname, udept_role, uconum, uaddress) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssss", $uid, $upin, $uname, $udept_role, $uconum, $address);
    
        return $stmt->execute();
    }

    //method to get the books with the same uid for iteration
    public function getBorrowedBooksFromPayments($uid) {
        $stmt = $this->conn->prepare("SELECT book_number, book_title FROM payment WHERE uid = ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();

        $result = $stmt->get_result();

        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        return $books;
    }

    //method for deleting the confirmed payment in the payment table
    public function deletePaymentByBookNumber($uid, $book_number) {
        $stmt = $this->conn->prepare("DELETE FROM payment WHERE uid = ? AND book_number = ?");
        
        if (!$stmt) {
            die("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
        }

        $stmt->bind_param("ss", $uid, $book_number);
        
        return $stmt->execute();
    }

    // Insert a new transaction record into the transaction_records table
    public function addTransactionRecord($uid, $uname, $amount_paid) {
        $sql = "INSERT INTO transaction_records (uid, uname, amount_paid, date) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssd", $uid, $uname, $amount_paid);
        
        return $stmt->execute();
    }

    // Fetch all records from the transaction_records table, sorted by date
    public function getAllTransactionRecords() {
        $sql = "SELECT * FROM transaction_records ORDER BY date DESC";
        $result = $this->conn->query($sql);

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }

        return $transactions;
    }

    //table filtering for the transaction records
    public function getTransactionRecordsByDate($startDate, $endDate) {
        $sql = "SELECT * FROM transaction_records WHERE date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    

}