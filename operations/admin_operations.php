<?php
    require __DIR__ . '/../database/db_connection.php';
    require_once __DIR__ . "/abstract_operations.php";
    require_once __DIR__ . "/../TCPDF-main/tcpdf.php";

    class Admin_operations extends Operations {
        private $conn;
        public function __construct($conn) {
            $this->conn = $conn;
        }
//LOGIN PROCESS
        //Method to check whether user exists in the database
        public function userExist($userID, $upin = "") {
            if($userID == "admin" && $upin == "admin123") {
                return true;
            }
            return false;
        }

//MENU PROCESS
        //Method to fetch all registered users' details (uid, uname, udept_role, uconum)
        public function getAllUsers() {
            $stmt = $this->conn->prepare("SELECT uid, uname, udept_role, uconum FROM users");
            $stmt->execute();
            $result = $stmt->get_result();
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $stmt->close();
            return $users;
        }        

        //Method to get books values for inventory
        public function getAllBooks() {
            $stmt = $this->conn->prepare("SELECT * FROM books");
            $stmt->execute();
            $result = $stmt->get_result();
            $books = [];
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            $stmt->close();
            return $books;
        }
        

        //Method to get all borrowed books for inventory
        public function getAllBorrowedBooks() {
            $stmt = $this->conn->prepare("SELECT * FROM borrowed_books");
            $stmt->execute();
            $result = $stmt->get_result();
            $borrowedBooks = [];
            while ($row = $result->fetch_assoc()) {
                $borrowedBooks[] = $row;
            }
            $stmt->close();
            return $borrowedBooks;
        }        

        //Method to get books due today
        public function getBooksDue() {
            $curDate = date('Y-m-d');
            $stmt = $this->conn->prepare("SELECT * FROM borrowed_books WHERE date_to_return = ?");
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
        

        //Method to get all payments, grouped by uid with total fine amount
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

//USER REGISTRATION AND DELETION PROCESS
        //Method to delete a user by uid
        public function deleteUser($uid) {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE uid = ?");
            $stmt->bind_param("s", $uid);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
        
        //Method to add new user
        public function addUser($uid, $upin, $uname, $udept_role, $uconum, $address) {
            // Check if the UID already exists
            $checkUidQuery = "SELECT * FROM users WHERE uid = ?";
            $stmt = $this->conn->prepare($checkUidQuery);
            $stmt->bind_param("s", $uid);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows > 0) {
                return false;
            }
            else{
                $query = "INSERT INTO users (uid, upin, uname, udept_role, uconum, uaddress) 
                      VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ssssss", $uid, $upin, $uname, $udept_role, $uconum, $address);
            
                return $stmt->execute();
            }
        }
        
//PAYMENT PROCESS
        //Method to get the books with the same uid for iteration
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

        //Method for deleting the confirmed payment in the payment table
        public function deletePaymentByBookNumber($uid, $book_number) {
            $stmt = $this->conn->prepare("DELETE FROM payment WHERE uid = ? AND book_number = ?");
            
            if (!$stmt) {
                die("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            }

            $stmt->bind_param("ss", $uid, $book_number);
            
            return $stmt->execute();
        }

        //Method to insert a new transaction record into the transaction_records table
        public function addTransactionRecord($uid, $uname, $amount_paid) {
            $sql = "INSERT INTO transaction_records (uid, uname, amount_paid, date) VALUES (?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssd", $uid, $uname, $amount_paid);
            
            return $stmt->execute();
        }

//TRANSACTION PROCESS
        //Method to fetch all records from the transaction_records table, sorted by date
        public function getAllTransactionRecords() {
            $stmt = $this->conn->prepare("SELECT * FROM transaction_records ORDER BY date DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
            $stmt->close();
            return $transactions;
        }        

        //Method for table filtering for the transaction records
        public function getTransactionRecordsByDate($startDate, $endDate) {
            $sql = "SELECT * FROM transaction_records WHERE date BETWEEN ? AND ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        //Method for transaction history printing
        public function printTrasactionHistory($startDate, $endDate){
            $records = $this->getTransactionRecordsByDate($startDate, $endDate);

            if (!empty($records)) {
                // Proceed to generate the PDF with the records
                $pdf = new TCPDF();
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('Your Name');
                $pdf->SetTitle('Transaction Records');
                $pdf->SetSubject('Transaction Records');
                $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Library Management System', 'Transaction Records');
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->AddPage();
                
                $sDate = new DateTime($startDate); 
                $sDateFormatted = htmlspecialchars($sDate->format('Y/m/d/')); 

                $eDate = new DateTime($endDate); 
                $eDateFormatted = htmlspecialchars($eDate->format('Y/m/d')); 

                $html = "<h2>Transaction Records [$sDateFormatted] to [$eDateFormatted] </h2>
                        <table border=\"1\" cellpadding=\"5\">
                            <thead>
                                <tr>
                                    <th style=\"text-align: center;\">Transaction ID</th>
                                    <th style=\"text-align: center;\">Borrower ID Number</th>
                                    <th style=\"text-align: center;\">Borrower Name</th>
                                    <th style=\"text-align: center;\">Amount Paid</th>
                                    <th style=\"text-align: center;\">Date</th>
                                </tr>
                            </thead>
                            <tbody>";

                foreach ($records as $record) {
                    $html .= '<tr>
                                <td style="text-align: center;">' . htmlspecialchars($record['transaction_id']) . '</td>
                                <td style="text-align: center;">' . htmlspecialchars($record['uid']) . '</td>
                                <td style="text-align: center;">' . htmlspecialchars($record['uname']) . '</td>
                                <td style="text-align: center;">' . htmlspecialchars($record['amount_paid']) . '.00 </td>
                                <td style="text-align: center;">' . htmlspecialchars($record['date']) . '</td>
                            </tr>';
                }

                $html .= '</tbody></table>';
                $pdf->writeHTML($html, true, false, true, false, 'center');
                $pdf->Output('transaction_records.pdf', 'I');
                exit();
            } else {
                echo "No records found for the specified dates.";                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
            }
        }

    }