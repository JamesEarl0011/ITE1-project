-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2024 at 01:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_number` varchar(30) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `year_published` varchar(4) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `book_available` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_number`, `book_title`, `author`, `year_published`, `genre`, `book_available`) VALUES
('10089608', 'I, Claudius', 'Robert Graves', '1962', 'Historical Fiction', 10),
('10099102', 'All the King\'s Men', 'Robert Penn Warren', '1971', 'Fiction', 10),
('10276352', 'Death Comes for the Archbishop', 'Willa Cather', '1927', 'Fiction', 10),
('10281075', 'Native Son', 'Richard Wright', '1940', 'Fiction', 10),
('10468612', 'Lord Jim', 'Joseph Conrad', '1900', 'Adventure', 10),
('11292183', 'The Call of the Wild', 'Jack London', '1903', 'Adventure', 10),
('11948038', 'The Way of All Flesh', 'Samuel Butler', '1903', 'Fiction', 10),
('120396121', 'A Dance to the Music of Time (series)', 'John Fowles', '1951', 'Fiction', 10),
('12356770', 'The Good Soldier', 'Ford Madox Ford', '1915', 'Fiction', 10),
('12542306', 'Heart of Darkness', 'Joseph Conrad', '1899', 'Adventure', 10),
('12564166', 'Nostromo', 'Joseph Conrad', '1904', 'Fiction', 10),
('12793233', 'Lolita', 'Vladimir Nabokov', '1955', 'Romance', 10),
('13263175', 'From Here to Eternity', 'James Jones', '1934', 'Fiction', 10),
('13279255', 'Sophie\'s Choice', 'William Styron', '1939', 'Fiction', 10),
('13728233', 'The Magnificent Ambersons', 'Booth Tarkington', '1918', 'Fiction', 10),
('13734916', 'The Naked and the Dead', 'Norman Mailer', '1948', 'Fiction', 10),
('13834368', 'Point Counter Point', 'Aldous Huxley', '1928', 'Fiction', 10),
('14340073', 'The Adventures of Augie March', 'Saul Bellow', '1953', 'Fiction', 10),
('14398516', 'The Alexandria Quartet (series)', 'Lawrence Durell', '1951', 'Fiction', 10),
('14792978', 'The Age of Innocence', 'Edith Wharton', '1920', 'Fiction', 10),
('15155272', 'The Golden Bowl', 'Henry James', '1904', 'Fiction', 10),
('15667299', 'Kim', 'Rudyard Kipling', '1901', 'Fiction', 10),
('15953154', 'The Wapshot Chronicle', 'John Cheever', '1957', 'Fiction', 10),
('16239786', 'The Rainbow', 'D.H. Lawrence', '1915', 'Fiction', 10),
('17153807', 'The House of Mirth', 'Edith Wharton', '1905', 'Fiction', 10),
('17190358', 'The Secret Agent', 'Joseph Conrad', '1907', 'Thriller', 10),
('17894615', 'The Ambassadors', 'Henry James', '1903', 'Fiction', 10),
('1820510', 'The Catcher in the Rye', 'J.D. Salinger', '1951', 'Fiction', 10),
('19411993', 'A House for Mr. Biswas', 'V.S. Naipaul', '1961', 'Fiction', 10),
('19539077', 'Under the Volcano', 'Malcolm Lowry', '1947', 'Fiction', 10),
('20029050', 'U.S.A. (trilogy)', 'John Dos Passos', '1960', 'Fiction', 10),
('20460387', 'Women In Love', 'D.H. Lawrence', '1920', 'Fiction', 10),
('20677658', 'The Death of the Heart', 'Elizabeth Bowen', '1938', 'Literary Fiction', 10),
('20720626', 'Catch-22', 'Joseph Heller', '1939', 'Fiction', 10),
('2306773', 'Wide Sargasso Sea', 'Jean Rhys', '1966', 'Fiction', 10),
('2326969', 'As I Lay Dying', 'William Faulkner', '1930', 'Fiction', 10),
('23533354', 'Midnight\'s Children', 'Salman Rushdie', '1949', 'Fiction', 10),
('24368748', 'The Studs Lonigan Trilogy (series)', 'James T. Farrell', '1935', 'Fiction', 10),
('2445778', 'Appointment in Samarra', 'John O’Hara', '1934', 'Fiction', 10),
('25017445', 'Sister Carrie', 'Theodore Dreiser', '1900', 'Fiction', 10),
('2654695', 'The Bridge of San Luis Rey', 'Thornton Wilder', '1927', 'Fiction', 10),
('26588929', 'The Old Wives\' Tale', 'Arnold Bennett', '1908', 'Fiction', 10),
('2672049', 'A Clockwork Orange', 'Anthony Burgess', '1965', 'Dystopian', 10),
('27450507', 'Ulysses', 'James Joyce', '1922', 'Fiction', 10),
('27986975', 'An American Tragedy', 'Theodore Dreiser', '1925', 'Fiction', 10),
('28025189', 'Finnegans Wake', 'James Joyce', '1939', 'Fiction', 10),
('28889297', 'The Wings of the Dove', 'Henry James', '1902', 'Fiction', 10),
('2934248', 'Loving', 'Henry Green', '1945', 'Romance', 10),
('29834871', 'Sons and Lovers', 'D.H. Lawrence', '1913', 'Fiction', 10),
('3028252', 'Animal Farm', 'George Orwell', '1945', 'Political Satire', 10),
('3128474', 'Tobacco Road', 'Erskine Caldwell', '1932', 'Fiction', 10),
('31448917', 'Of Human Bondage', 'W. Somerset Maugham', '1940', 'Fiction', 10),
('31791386', 'Main Street', 'Sinclair Lewis', '1920', 'Fiction', 10),
('3274264', 'Deliverance', 'James Dickey', '1970', 'Fiction', 10),
('35672167', 'Parade\'s End (series)', 'Ford Madox Ford', '1928', 'Fiction', 10),
('3632809', 'The Prime of Miss Jean Brodie', 'Muriel Spark', '1961', 'Fiction', 10),
('3645830', 'The Heart of the Matter', 'Graham Greene', '1936', 'Fiction', 10),
('3748990', 'Slaughterhouse-Five', 'Kurt Vonnegut', '1960', 'Fiction', 10),
('3877951', 'Ironweed', 'William Kennedy', '1983', 'Fiction', 10),
('3907927', 'Under the Net', 'Iris Murdoch', '1954', 'Fiction', 10),
('3932463', 'The Ginger Man', 'J.P. Donleavy', '1955', 'Fiction', 10),
('4176862', 'The Sun Also Rises', 'Ernest Hemingway', '1926', 'Fiction', 10),
('4224334', 'The Great Gatsby', 'F. Scott Fitzgerald', '1925', 'Fiction', 10),
('4327537', 'The Maltese Falcon', 'Dashiell Hammett', '1929', 'Fiction', 10),
('4588577', 'A Handful of Dust', 'Evelyn Waugh', '1934', 'Fiction', 10),
('4889841', 'The Day of the Locust', 'Nathanael West', '1939', 'Fiction', 10),
('4889852', 'Scoop', 'Evelyn Waugh', '1938', 'Fiction', 10),
('5052942', 'Darkness at Noon', 'Arthur Koestler', '1979', 'Fiction', 10),
('5091345', 'Go Tell It on the Mountain', 'James Baldwin', '1946', 'Fiction', 10),
('5237831', 'The Moviegoer', 'Walker Percy', '1961', 'Fiction', 10),
('5356841', 'The Heart is a Lonely Hunter', 'Carson McCullers', '1940', 'Fiction', 10),
('5470812', 'A Farewell to Arms', 'Ernest Hemingway', '1929', 'War Fiction', 10),
('5783324', 'Tropic of Cancer', 'Henry Miller', '1934', 'Fiction', 10),
('5891123', 'Henderson the Rain King', 'Saul Bellow', '1959', 'Fiction', 10),
('5956652', 'To the Lighthouse', 'Virginia Woolf', '1927', 'Fiction', 10),
('604874', 'The Postman Always Rings Twice', 'William Faulkner', '1934', 'Fiction', 10),
('6185075', 'Lord of the Flies', 'William Golding', '1954', 'Fiction', 10),
('6286674', 'Brideshead Revisited', 'Evelyn Waugh', '1915', 'Fiction', 10),
('6331670', 'On the Road', 'Jack Kerouac', '1957', 'Fiction', 10),
('6379210', 'Portnoy\'s Complaint', 'Philip Roth', '1969', 'Comedy', 10),
('6421682', '1984', 'George Orwell', '1946', 'Dystopian', 10),
('6785397', 'The Sheltering Sky', 'Paul Bowles', '1949', 'Fiction', 10),
('6918560', 'Brave New World', 'Aldous Huxley', '1932', 'Fiction', 10),
('7425727', 'The Grapes of Wrath', 'John Steinbeck', '1939', 'Fiction', 10),
('7462114', 'A High Wind in Jamaica', 'Richard Hughes', '1929', 'Fiction', 10),
('7673453', 'Light in August', 'William Faulkner', '1932', 'Fiction', 10),
('7967244', 'Tender is the Night', 'F. Scott Fitzgerald', '1934', 'Fiction', 10),
('8095511', 'Howards End', 'E.M. Forster', '1910', 'Fiction', 10),
('8222956', 'Invisible Man', 'Ralph Ellison', '1952', 'Fiction', 10),
('8254946', 'The Magus', 'Vladimir Nabokov', '1962', 'Fiction', 10),
('8272944', 'Pale Fire', 'Vladimir Nabokov', '1960', 'Fiction', 10),
('8389343', 'A Bend in the River', 'V.S. Naipaul', '1979', 'Fiction', 10),
('8444685', 'A Passage to India', 'E.M. Forster', '1924', 'Fiction', 10),
('8466658', 'Ragtime', 'E.L. Doctorow', '1975', 'Fiction', 10),
('9195067', 'Zuleika Dobson', 'Max Beerbohm', '1911', 'Comedy', 10),
('9239680', 'Winesburg, Ohio', 'Sherwood Anderson', '1919', 'Fiction', 10),
('9419977', 'A Room With a View', 'E.M. Forster', '1908', 'Fiction', 10),
('9892422', 'The Sound and the Fury', 'William Faulkner', '1929', 'Southern Gothic', 10),
('9918875', 'Angle of Repose', 'Wallace Stegner', '1979', 'Fiction', 10),
('9932318', 'A Portait of the Artist as a Young Man', 'James Joyce', '1916', 'Literary Fiction', 10);

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_books`
--

CREATE TABLE `borrowed_books` (
  `uid` varchar(50) NOT NULL,
  `uname` varchar(255) DEFAULT NULL,
  `book_title` text NOT NULL,
  `book_number` varchar(100) NOT NULL,
  `date_borrowed` date NOT NULL,
  `date_to_return` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `uid` varchar(30) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `book_number` varchar(50) NOT NULL,
  `fine_amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_records`
--

CREATE TABLE `transaction_records` (
  `transaction_id` int(11) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `uname` varchar(255) DEFAULT NULL,
  `amount_paid` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_records`
--

INSERT INTO `transaction_records` (`transaction_id`, `uid`, `uname`, `amount_paid`, `date`) VALUES
(1, 'ST23079569', 'James Earl T. Dologa-og', 400, '2024-10-16'),
(2, 'ST23079569', 'James Earl T. Dologa-og', 1200, '2024-10-16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` varchar(30) NOT NULL,
  `upin` varchar(50) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `udept_role` varchar(255) NOT NULL,
  `uconum` varchar(11) NOT NULL,
  `uaddress` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `upin`, `uname`, `udept_role`, `uconum`, `uaddress`) VALUES
('ST23079569', '123123', 'James Earl T. Dologa-og', 'BSIT [Student]', '09305940633', 'Biasong, Talisay City, Cebu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_number`);

--
-- Indexes for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD KEY `book_number` (`book_number`);

--
-- Indexes for table `transaction_records`
--
ALTER TABLE `transaction_records`
  ADD PRIMARY KEY (`transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `transaction_records`
--
ALTER TABLE `transaction_records`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD CONSTRAINT `book_number` FOREIGN KEY (`book_number`) REFERENCES `books` (`book_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
