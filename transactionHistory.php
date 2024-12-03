<?php
session_start();

require __DIR__ . "/database/db_connection.php";
require_once __DIR__ ."/operations/admin_operations.php";

$admin = new Admin_operations($conn);

$records = $admin->getAllTransactionRecords();

$showFilter = false;
$printDialog = false;
$startDate = '';
$endDate = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['back'])) {
        header("Location: adminInterface.php");
        exit();
    }

    if (isset($_POST['filter'])) {
        $showFilter = true;
    }elseif(isset($_POST['printR'])){
        $printDialog = true;
    }

    // Handle filter submission
    if (isset($_POST['apply_filter'])) {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $records = $admin->getTransactionRecordsByDate($startDate, $endDate);
    }

    // Handle filter closing
    if (isset($_POST['close_filter'])) {
        $showFilter = false;
    }

    if (isset($_POST['$close_print'])){
        header('refresh:1;');
    }

    // Handle PDF generation
    if (isset($_POST['print'])) {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        
        $admin->printTrasactionHistory($startDate, $endDate);
    }

    if (isset($_POST['close_filter'])) {
        $showFilter = false;
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Interface</title>
    <link rel="icon" href="imgs/favicon.png">
    <style>
        *{
            font-family: "Rockwell";
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #709775;
        }
        nav {
            background-color: #A1CCA5;
            padding: 15px;
            color: black;
        }
        nav .btns {
            color: black;
            background: none;
            border: none;
            text-decoration: none;
            margin-right: 20px;
            cursor: pointer;
            font-weight: bolder;
            font-size: 18px;
            border-bottom: 3px solid black;
        }
        nav form {
            display: inline;
        }
        .logout {
            position: absolute;
            right: 3%;
            background: #1f3322;
            border-radius: 10px;
            height: 30px;
            width: 100px;
            color: white;
            cursor: pointer;
            font-weight: bolder;
            font-size: 18px;
        }
        table {
            margin: 20px;
            width: 95%;
            border-collapse: collapse;
            font-size: 1em;
            background-color: #A1CCA5;
        }
        th, td {
            padding: 10px;
            text-align: left; 
            border: 1px solid black; 
        }
        th {
            text-align: center;
            background-color: #2e8533; 
            color: black; 
        }
        .filter-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            background: #A1CCA5;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .filter-form input {
            margin: 5px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 60%;
        }
        .filter-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }
        .filter-active {
            display: block;
        }
        .filter-form input {
            margin: 5px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 60%;
            font-weight: bolder;
            font-size: 1rem;
            background-color: #f9f9f9;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .filter-form input:focus {
            border-color: #4CAF50;
            background-color: #fff;
        }

        .filter-form button {
            margin-top: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #2e8533;
            color: black;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .filter-form button:hover {
            background-color: #45a049;
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
            <button class="btns" type="submit" name="filter">Filter Records</button>
            <button class="btns" type="submit" name="printR">Print Records</button>
            <button class="btn logout" type="submit" name="back">Back</button>
        </form>
    </nav>
    <center><h2>Transaction Records</h2></center>

    <div class="filter-overlay <?php echo $showFilter ? 'filter-active' : ''; ?>"></div>
    
    <?php if ($showFilter): ?>
        <div class="filter-form">
            <form action="" method="post">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" required style="margin-left: 10px" value="<?php echo date('Y-m-d'); ?>">
                <br><br>
                <center>
                    <button type="submit" name="apply_filter">Filter</button>
                </center>
                <center>
                    <button type="submit" name="close_filter" style="background-color: #6a040f; padding: 10px 20px; color: white;">Close</button>
                </center>
            </form>
        </div>
    <?php endif; ?>
    
    <div class="filter-overlay <?php echo $printDialog ? 'filter-active' : ''; ?>"></div>

    <?php if ($printDialog): ?>
        <div class="filter-form">
            <form action="" method="post" target="_blank">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" required style="margin-left: 10px" value="<?php echo date('Y-m-d'); ?>">
                <br><br>
                <center>
                    <button type="submit" name="print" target="_blank">Print</button>
                </center>
            </form>
            <form action="" method="post">
                    <center>
                        <button type="submit" name="close_print" style="background-color: #6a040f; padding: 10px 20px; color: white;">Close</button>
                    </center>
            </form>
        </div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Borrower ID Number</th>
                <th>Borrower Name</th>
                <th>Amount Paid</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($records)) {
                foreach ($records as $record) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($record['transaction_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['uid']) . "</td>";
                    echo "<td>" . htmlspecialchars($record['uname']) . "</td>";
                    echo "<td> ₱ " . number_format(htmlspecialchars($record['amount_paid']), 2) . "</td>";
                    echo "<td>" . htmlspecialchars($record['date']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No transaction recorded yet.</td></tr>";
            }
            
            ?>
        </tbody>
    </table>
</body>
</html>
