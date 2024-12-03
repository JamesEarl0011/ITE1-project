<?php
    require __DIR__ . '/../database/db_connection.php';
    require_once __DIR__ . "/abstract_operations.php";

    class Borrower_operations extends Operations {
        private $conn;
        public function __construct($conn) {
            $this->conn = $conn;
        }

//LOG IN PROCESS
        //Method to check whether user exist in the database
        public function userExist($userID, $upin = "") {
            $userID = $this->conn->real_escape_string($userID);

            $sql = "SELECT * FROM users WHERE uid = '$userID'";
            $result = $this->conn->query($sql);

            return $result->num_rows > 0;
        }

        //Method to check whether the pin inputted matches with the userID
        public function validatePin($userID, $userPin) {
            $userID = $this->conn->real_escape_string($userID);
            $userPin = $this->conn->real_escape_string($userPin);
    
            $sql = "SELECT * FROM users WHERE uid = '$userID' AND upin = '$userPin'";
            $result = $this->conn->query($sql);
    
            return $result->num_rows > 0;
        }

//MENU PROCESS
        //Method to fetch user details by userID
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

//BORROW BOOK PROCESS
        //Method to borrow a book
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

        //Method to filter out the books using sidebar menu
        public function getAvailableBooksByGenre($genre) {
            $sql = "SELECT * FROM books WHERE genre = '$genre' AND book_available > 0";
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        //Method to check whether borrower has already borrowed the book
        public function hasBorrowedBook($uid, $book_number) {
            $query = "SELECT * FROM borrowed_books WHERE uid = ? AND book_number = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $uid, $book_number);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
        }

        //Method to update book availability book_ after user borrow
        public function updateBookAvailability($book_number) {
            $stmt = $this->conn->prepare("UPDATE books SET book_available = book_available - 1 WHERE book_number = ?");
            $stmt->bind_param("s", $book_number);
            return $stmt->execute();
        }
        //Method for the search function
        public function searchAvailableBooks($searchQuery) {
            $sql = "SELECT * FROM books WHERE book_available > 0 AND (book_title LIKE ? OR author LIKE ?) LIMIT 12";
            $stmt = $this->conn->prepare($sql);
            $searchTerm = "%" . $searchQuery . "%";
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        //Method to fetch borrowed books for the logged-in user
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

        //Method to get all available books
        public function getAvailableBooks($limit = null) {
            $query = "SELECT * FROM books WHERE book_available > 0";
            if ($limit) {
                $query .= " LIMIT " . intval($limit);
            }
            $result = $this->conn->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        //Method to search for a book by its number
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

        //Method to search for a book by its title
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

//RETURN BOOK PROCESS
        //Method for the return of books
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

        //Method to check whether the returned book is past due
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

        //Method to get return date
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
        
        //Method to calculate fine amount
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
        //Method to check if payment already exists for a user and book title
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

        //Payment method for the fine
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

    }
