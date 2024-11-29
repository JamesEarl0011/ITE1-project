<?php
session_start();

include("database/dbCon.php");
include("database/dbOperations.php");
require_once('TCPDF-main/tcpdf.php');

$user = new Operations($conn);

$records = $user->getAllTransactionRecords();

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
        $records = $user->getTransactionRecordsByDate($startDate, $endDate);
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
        $records = $user->getTransactionRecordsByDate($startDate, $endDate);

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
            $pdf->SetFont('helvetica', '', 10);
            $pdf->AddPage();
            
            $sDate = new DateTime($startDate); 
            $sDateFormatted = htmlspecialchars($sDate->format('Y/m/d/')); 

            $eDate = new DateTime($endDate); 
            $eDateFormatted = htmlspecialchars($eDate->format('Y/m/d')); 

            $html = "<h2>Transaction Records [$sDateFormatted] to [$eDateFormatted] </h2>
                      <table border=\"1\" cellpadding=\"5\">
                          <thead>
                              <tr>
                                  <th>Transaction ID</th>
                                  <th>Borrower ID Number</th>
                                  <th>Borrower Name</th>
                                  <th>Amount Paid</th>
                                  <th>Date</th>
                              </tr>
                          </thead>
                          <tbody>";

            foreach ($records as $record) {
                $html .= '<tr>
                              <td>' . htmlspecialchars($record['transaction_id']) . '</td>
                              <td>' . htmlspecialchars($record['uid']) . '</td>
                              <td>' . htmlspecialchars($record['uname']) . '</td>
                              <td>' . htmlspecialchars($record['amount_paid']) . '.00 </td>
                              <td>' . htmlspecialchars($record['date']) . '</td>
                          </tr>';
            }

            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('transaction_records.pdf', 'I');
            exit();
        } else {
            echo "No records found for the specified dates.";                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
        }
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
            position: absolute;
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
        .filter-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            background: white;
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
            display: block; /* Show when active */
        }
        .filter-form input {
            margin: 5px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 60%;
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
            background-color: #4CAF50;
            color: white;
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
            <button class="btn" type="submit" name="filter">Filter Records</button>
            <button class="btn" type="submit" name="printR">Print Records</button>
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
                    <button type="submit" name="close_filter" style="background-color: #f44336; padding: 10px 20px">Close</button>
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
                    <button type="submit" name="print" target="_blank" style="background-color: goldenrod; padding: 10px 20px">Print</button>
                </center>
            </form>
            <form action="" method="post">
                    <center>
                        <button type="submit" name="close_print" style="background-color: #f44336; padding: 10px 20px">Close</button>
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
