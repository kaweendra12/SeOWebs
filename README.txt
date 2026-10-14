CITY LIBRARY MANAGEMENT SYSTEM
Student-friendly project explanation and presentation guide
=========================================================

1. WHAT THIS PROJECT IS
-----------------------
This is a web-based Library Management System. It helps a library keep its
books, members, users, and borrowing records in one place.

There are two main kinds of people who use the system:

1. Library staff (Admin or Librarian)
   They can add books and members, issue books, receive returned books, search
   records, and view reports. An Admin can also create and manage system users.

2. Readers / library members
   They can register, log in, view their account, and see their borrowing
   information.

The project uses PHP for the server-side work, MySQL for the database, HTML for
the page structure, CSS for the design, and a small amount of JavaScript for
browser-side behaviour.


2. SIMPLE EXPLANATION OF HOW A WEBSITE REQUEST WORKS
----------------------------------------------------
When a person opens a page, for example index.php, this happens:

1. The browser asks the web server for the page.
2. PHP runs on the server before the page is sent to the browser.
3. PHP reads or saves data in the MySQL database when needed.
4. PHP builds HTML using that data.
5. The browser receives the HTML and uses CSS to display it nicely.

In simple words: HTML shows the page, PHP does the thinking, MySQL remembers
the data, and CSS makes the page look good.


3. MAIN FEATURES OF THE SYSTEM
------------------------------
- Public home page showing library statistics and recently added books.
- Book catalogue page where visitors can view books.
- Reader registration and login.
- Reader "My Account" page for personal details and borrowing records.
- Staff dashboard with counts of books, members, issued books, overdue books,
  returned books, and users.
- Book management: add, edit, and manage book details.
- Member management: add, edit, search, and remove members when they have no
  unreturned books.
- Issue book process with due dates and availability checks.
- Return book process and borrowing history.
- Search and reporting pages for library records.
- User management page for administrators.


4. DATABASE EXPLANATION
-----------------------
The database name in this project is: lms

Important tables:

users
  Stores login accounts. Important fields are id, username (email), password,
  role, name, status, and member_id.

members
  Stores library-member details, such as member ID, name, address, telephone,
  email, and registration date.

books
  Stores book details, such as book ID, name, author, ISBN, category, total
  copies, and available copies.

borrowings
  Stores every borrowing activity. It connects one member to one book and
  saves issue date, due date, return date, and status.

Important relationship:

User (reader) -> Member -> Borrowing <- Book

Users stores login accounts. A reader user is linked one-to-one to a member by
users.member_id. A borrowing record links the member who receives the book to
the book itself. It also stores issued_by_user_id and returned_by_user_id so
the staff account that issued or received the book can be recorded.

For example, when member M001 borrows book B001, the system creates a new row
in the borrowings table. That row tells us who borrowed the book, which book it
was, when it should be returned, and whether it is still issued or returned.


5. FILE-BY-FILE EXPLANATION
---------------------------

config.php
  This is the shared database-connection file. Most PHP pages use
  require_once 'config.php' (or ../config.php from the admin folder) to connect
  to MySQL. It creates the $conn variable, which is used to run database
  queries. It also contains e(), a helper that safely displays text in HTML.

index.php
  This is the public home page. It reads totals from the database, such as
  total books and total members, then displays recently added books. It also
  changes the menu depending on whether somebody is logged in.

library.php
  This displays the library's book catalogue for visitors and members.

register.php
  This creates a reader account. It checks that required fields are filled,
  the email is valid, passwords match, and the password has at least six
  characters. It creates a member record and then creates the related user
  login account.

login.php
  This checks a user's email and password. If the password is correct, it saves
  small pieces of information in a session, such as user ID, role, name, and
  member ID. It then sends staff to the admin dashboard and readers to My
  Account.

logout.php
  This clears the session, which logs the user out.

my-account.php
  This is the page for readers. It shows their member details and their own
  borrowing information. It also lets them update their profile.

style.css
  This contains the design rules for public pages: colours, spacing, forms,
  navigation, cards, and responsive layout.

script.js
  This contains optional browser-side behaviour shared by pages.

admin/admin-style.css
  This contains the design rules for the staff/admin pages.

admin/dashboard.php
  This is the staff home page. It checks that the logged-in user is an Admin or
  Librarian. It counts important database records and shows recent borrowing
  activity.

admin/books.php
  This is used by staff to manage books. It stores book information in the
  books table and displays the list of books.

admin/members.php
  This lets staff add, edit, search, and delete library members. It generates
  readable member IDs such as M001, M002, and M003. It prevents deletion of a
  member who still has an issued book.

admin/issue_book.php
  This creates a borrowing record when a staff member lends a book. The code
  checks the selected member and book, makes sure the book is available, and
  sets a due date.

admin/return_book.php
  This records a returned book and updates its status. The available-copy count
  is updated so the book can be issued again.

admin/history.php
  This shows past borrowing records.

admin/search.php
  This helps staff find books, members, or borrowing data quickly.

admin/reports.php
  This creates useful reports, such as member, issued-book, returned-book, and
  late-book reports.

admin/users.php
  This is the administrator-only user-account page. It creates accounts for
  Admins, Librarians, or Readers and lists existing system users. Passwords are
  hashed before saving. The page checks whether the email already exists before
  inserting a new user.


6. IMPORTANT CODE CONCEPTS IN SIMPLE ENGLISH
---------------------------------------------

PHP tags: <?php ... ?>
  Code inside these tags runs on the web server. The visitor does not see the
  PHP source code; they only receive the final web page.

require_once
  This includes another PHP file once. For example, config.php is included so a
  page can use the database connection.

$ symbol
  In PHP, a variable starts with $. For example, $username stores an email and
  $conn stores the database connection.

$_POST
  This holds data sent from a form after a user presses a submit button. For
  example, $_POST['username'] contains the username entered into a form.

$_GET
  This holds data from the address bar. For example, users.php?delete=5 sends
  the value 5 as a request to delete user number 5.

$_SESSION
  A session remembers that a person is logged in while they move between pages.
  The system uses it to remember user ID, role, name, and member ID.

if statement
  This makes a decision. Example: if a person is not logged in, the system
  sends them to login.php.

while loop
  This repeats code. It is used when displaying multiple books, users, or
  borrowing records from the database.

SQL query
  SQL is the language used to speak to the database.
  SELECT reads data.
  INSERT adds a new record.
  UPDATE changes an existing record.
  DELETE removes a record.

Prepared statement
  A prepared statement sends the SQL command separately from user-entered data.
  In admin/users.php, it is used when checking and adding users. This is safer
  because entered text cannot change the meaning of the SQL command.

password_hash()
  This changes a password into a protected hash before it is saved. The real
  password should never be stored directly in the database.

password_verify()
  This checks the password typed at login against the protected hash saved in
  the database.

htmlspecialchars() and e()
  These safely display text in the web page. They help prevent a visitor's text
  from being treated as page code.


7. IMPORTANT SYSTEM FLOWS TO EXPLAIN
------------------------------------

A. Registration flow
  1. A new reader fills in the registration form.
  2. register.php validates the fields, email, and password.
  3. The system checks that the email is not already used.
  4. It creates a member record with an ID such as M001.
  5. It hashes the password and creates a user record with the reader role.
  6. The reader is redirected to the login page.

B. Login flow
  1. The user enters email and password.
  2. login.php finds the account by email.
  3. password_verify() checks the entered password safely.
  4. The system saves login details in the session.
  5. Admin/Librarian users go to the dashboard; readers go to My Account.

C. Issue book flow
  1. Staff selects a member and an available book.
  2. The system checks that the book is available.
  3. A borrowing record is added with issue date and due date.
  4. The number of available book copies is reduced.
  5. The dashboard and history can now show the new activity.

D. Return book flow
  1. Staff selects an issued borrowing record.
  2. The system records the return date.
  3. It changes the borrowing status to Returned or Late when appropriate.
  4. The number of available copies is increased.

E. Admin user creation flow
  1. The Admin opens User Management.
  2. The Admin enters full name, email, password, and role.
  3. users.php validates the data and checks for a duplicate email.
  4. The password is converted to a secure hash.
  5. A prepared INSERT statement saves the new user.
  6. The user list is read again and shows the new account.


8. COMMON EXAMINER QUESTIONS AND SIMPLE ANSWERS
------------------------------------------------

Question: What is the purpose of this project?
Answer: It is a web-based Library Management System. It helps a library manage
books, members, user accounts, and book borrowing records efficiently.

Question: Which technologies did you use?
Answer: I used HTML for page structure, CSS for design, PHP for server-side
logic, MySQL for the database, and JavaScript for small client-side behaviour.

Question: Why did you use MySQL?
Answer: MySQL stores data permanently in organised tables. It is suitable for
related data such as books, members, users, and borrowing records.

Question: What is PHP doing in your system?
Answer: PHP receives form data, validates it, communicates with MySQL, decides
which page a user can access, and creates the HTML shown in the browser.

Question: How do you know who is logged in?
Answer: After a successful login, PHP stores the user's ID and role in a
session. Protected pages check the session before showing their content.

Question: How do you control access for different users?
Answer: Each account has a role: admin, librarian, or reader. The code checks
the role in the session. For example, only an admin can open User Management.

Question: How are passwords protected?
Answer: Passwords are not saved as plain text. The system uses password_hash()
to save a hash and password_verify() when a user logs in.

Question: What happens when a book is issued?
Answer: The system saves a borrowing record containing the member, book, issue
date, due date, and status. It also reduces the available-copy count.

Question: What prevents a member with an unreturned book from being deleted?
Answer: Before deleting a member, the system checks the borrowings table for
records with the Issued status. If one exists, deletion is stopped.

Question: What is a prepared statement and why is it useful?
Answer: It is a safer way to run database queries. The SQL command and the
user's input are handled separately, which helps protect the database from
malicious input.

Question: What was the user-creation bug and how was it fixed?
Answer: The page attempted to save name, status, and member ID fields that did
not exist in the users table. The database was updated with the required
columns, and users.php was changed to use a checked prepared statement. Now it
reports success only after the database insert succeeds.


9. SHORT PRESENTATION SCRIPT
----------------------------
"My project is a City Library Management System developed using PHP and MySQL.
The public side allows visitors to view books and readers to register and log
in. The staff side allows librarians and administrators to manage books,
members, issuing, returns, history, searches, and reports. The database keeps
the information in separate tables for users, members, books, and borrowings.
PHP connects the pages to the database and controls the system logic. Sessions
are used to keep users logged in and roles are used to control access. Passwords
are hashed instead of stored directly. When a book is issued, a borrowing
record is created and the available-copy count is reduced. When it is returned,
the record is updated and the copy becomes available again."


10. HOW TO RUN THE PROJECT LOCALLY
----------------------------------
1. Start Apache and MySQL in WAMP.
2. Ensure the project folder is inside C:\wamp64\www\Libra1.
3. Create/import the lms database with its required tables and data.
4. Check config.php. It currently uses localhost, user root, an empty password,
   and database lms. Change these values only if your MySQL setup differs.
5. Open http://localhost/Libra1/ in a browser.


11. FINAL POINTS TO REMEMBER FOR THE EXAM
------------------------------------------
- Do not try to memorise every line. Explain the purpose and flow of each page.
- Start with the problem: a library needs organised records.
- Explain the solution: this system keeps books, members, and borrowings in a
  database and gives staff an easy web interface.
- Mention security: login sessions, roles, password hashing, validation, safe
  output, and prepared statements.
- Give one real example: issuing book B001 to member M001 creates a borrowing
  record and reduces available copies.
- If asked about a line you do not remember, explain what the surrounding
  section is doing rather than guessing.


12. DETAILED PHP CODE GUIDE: EVERY PAGE
---------------------------------------
Use this section when an examiner points to a file or a PHP line and asks,
"What does this code do?" The wording is intentionally simple.

IMPORTANT: THE COMMON START OF MOST PAGES
-----------------------------------------
Most PHP pages begin with code similar to this:

    require_once 'config.php';
    session_start();

require_once
  Loads config.php one time. That file creates the MySQL connection called
  $conn. Without it, the page cannot read or save database data.

session_start()
  Starts or continues the user's session. This is how PHP remembers who logged
  in from one page to the next.

Admin pages normally also use:

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }

isset() checks whether a value exists. Here, it checks whether the person has
logged in. If not, header() redirects the browser to Login, and exit() stops
the current page immediately. This protects staff pages from visitors.

The HTML below the PHP is the visible page. PHP inside it is mainly used to
print values, repeat table rows, or show/hide a small part of the page.


A. config.php - DATABASE CONNECTION AND SAFE OUTPUT
----------------------------------------------------
Main code idea:

    $conn = new mysqli($host, $user, $pass, $database);

new mysqli(...)
  Opens the connection between PHP and MySQL. $conn is the connection object
  used by every database query in the project.

    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

connect_error contains an error message if MySQL cannot be reached. die() stops
the page so the program does not continue with a broken connection.

    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

This defines a small reusable function. e($value) makes database text safe
before showing it in HTML. ?? means "use an empty value if this value is
missing or null." ENT_QUOTES protects both single and double quotation marks.


B. index.php - HOME PAGE
------------------------
The home page reads summary values:

    $totalBooks = $conn->query("SELECT COUNT(*) AS total FROM books")
                       ->fetch_assoc()['total'];

query() sends SQL to MySQL. SELECT COUNT(*) counts all rows in the books table.
AS total gives the result a friendly name. fetch_assoc() gets one database row
as an associative array, and ['total'] obtains the count from that row.

The page uses similar queries to count members, available copies, and
categories. It then reads the newest six books:

    SELECT * FROM books ORDER BY id DESC LIMIT 6

ORDER BY id DESC means newest first. LIMIT 6 means show only six records.

    while ($book = $featuredBooks->fetch_assoc())

while repeats once for every book returned by the query. In each repetition,
the PHP prints a book card in the HTML.


C. library.php - PUBLIC BOOK CATALOGUE
--------------------------------------
This page reads books from the books table and displays them to visitors. Its
PHP code gets book information, and its HTML loop creates one visible row/card
for each book. If it contains a search value, the search value is used to limit
the records shown. This page does not need an admin login because visitors are
allowed to browse books.


D. register.php - READER REGISTRATION
-------------------------------------
This page receives form fields with $_POST:

    $password = $_POST['password'];
    $name = trim($_POST['name']);

$_POST['name'] means the value entered in the form field called name. trim()
removes unnecessary spaces at the beginning and end.

The validation checks:

    if (empty(...))                 // required information was not entered
    elseif (strlen($password) < 6)  // password is too short
    elseif ($password !== $confirm) // two passwords are not identical
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) // invalid email

elseif is checked only if the condition before it was false. !== means "not
exactly equal to." filter_var(..., FILTER_VALIDATE_EMAIL) checks email format.

Before inserting records, the page checks if the email already exists in users
or members. It creates the next member ID from the last ID:

    $member_id = 'M' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

. joins text together. str_pad() changes 1 into 001, so the final ID becomes
M001. The page saves the member first, then saves the login account.

    $hashedPass = password_hash($password, PASSWORD_DEFAULT);

password_hash() is used before INSERT. The original password is never stored
in the database.

    header("Location: login.php?registered=1");
    exit();

After successful registration, the user is sent to Login. ?registered=1 is a
small message flag in the URL so Login can show a success message.


E. login.php - LOGIN AND ROLE REDIRECTION
-----------------------------------------
The page finds an account by its username/email:

    SELECT * FROM users WHERE username = '$username'

It then checks the password:

    password_verify($password, $row['password'])

This compares the typed password with the saved password hash. It does not
need to expose or reverse the original password.

On success, the page saves important values:

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['role'] = $row['role'];

The role decides where to send the person:

    if ($row['role'] === 'admin' || $row['role'] === 'librarian')

=== means "same value and same type." Admins and librarians go to
admin/dashboard.php. Readers go to my-account.php.


F. logout.php - ENDING A LOGIN
------------------------------
This page clears/destroys the session and redirects the user back to Login.
In simple terms, it removes the system's memory that the user was logged in.


G. my-account.php - READER ACCOUNT PAGE
----------------------------------------
This page uses the member ID saved during login:

    $member_id = $_SESSION['member_id'];

It reads the matching member record and that member's borrowing records. When
the reader updates personal information, PHP validates the form and sends
UPDATE queries to both members and users so the member profile and login email
remain consistent.


H. admin/dashboard.php - STAFF SUMMARY PAGE
--------------------------------------------
The first checks allow only staff:

    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian')

&& means "and." The condition says: if the person is neither Admin nor
Librarian, send them away from the admin area.

The dashboard runs COUNT queries for total books, members, issued books,
overdue books, returned books, and users. It also uses this kind of query:

    JOIN books b ON br.book_id = b.book_id
    JOIN members m ON br.member_id = m.member_id

JOIN combines related tables. br is a short name for borrowings, b is books,
and m is members. This lets one table row display the book name and member name
instead of only their IDs.

The PHP chooses a badge colour based on the borrowing status and due date.
This is only display logic; it helps staff see warnings quickly.


I. admin/books.php - ADD, EDIT, DELETE, AND LIST BOOKS
-------------------------------------------------------
This page uses one form for both adding and editing.

    if (isset($_POST['save']))

This runs when the Save button is pressed. The values are cleaned with:

    $conn->real_escape_string(...)

This escapes special characters before the value is inserted into an SQL
string. Quantity is converted to a number with (int).

The code decides between edit and add:

    elseif (isset($_POST['book_id']) && $_POST['book_id'] != '')

If there is an existing book ID, the page runs UPDATE. Otherwise it creates the
next ID, such as B001, and runs INSERT.

    UPDATE books SET ... WHERE book_id='$id'

UPDATE changes the existing book. WHERE is important because it identifies the
one record to change.

    DELETE FROM books WHERE book_id='$id'

This runs only when a delete value is sent in the URL. The list query at the
end reads all books and the while loop prints them in the table.

Note for explanation: when a book is edited, this page sets available_copies
equal to quantity. That is how the current version keeps those fields aligned.


J. admin/members.php - MEMBER MANAGEMENT
-----------------------------------------
This page has four logical jobs:

1. Add a member
   It reads the submitted form, validates the fields, finds the last member ID,
   creates the next ID, and uses INSERT to save the member.

2. Edit a member
   The edit link sends ?edit=MEMBER_ID. PHP reads that one record to fill the
   form. When Update is submitted, the page runs UPDATE.

3. Delete a member safely
   Before DELETE, PHP checks:

       SELECT COUNT(*) FROM borrowings
       WHERE member_id='$mid' AND status='Issued'

   If the count is greater than zero, the member still has a book out and
   cannot be deleted. Otherwise, it deletes the related user record first and
   then the member record.

4. Search members
   The page starts with "WHERE 1=1". This always evaluates as true and makes
   it easy to append an optional AND search condition. LIKE '%text%' means
   "contains this text anywhere."


K. admin/issue_book.php - ISSUING A BOOK
-----------------------------------------
At the beginning, PHP reads only books where:

    available_copies > 0

This prevents staff from selecting a book that has no copies available.

When the form is submitted, the code validates book, member, issue date, and
due date. It also checks:

    if ($due_date <= $issue_date)

<= means less than or equal to. A due date must be later than the issue date.

Before issuing, it checks for overdue books belonging to the selected member:

    SELECT COUNT(*) ... WHERE status='Issued' AND due_date < CURDATE()

CURDATE() is today's date from MySQL. If the count is above zero, the page
does not issue another book to that member.

If all checks pass, two database actions happen:

1. INSERT adds a row to borrowings with the status Issued.
2. UPDATE reduces books.available_copies by 1.

The redirect after success stops accidental duplicate form submission caused
by refreshing the page.


L. admin/return_book.php - RETURNING A BOOK
--------------------------------------------
The URL sends a borrowing ID:

    return_book.php?return_id=12

(int) converts that ID to a number. PHP gets the issued borrowing record,
compares today's date with the due date, and chooses a status:

    $status = $returnDate > $dueDate ? 'Late' : 'Returned';

This is called a ternary operator. It is a short if/else:
"if the return date is late, use Late; otherwise use Returned."

Then the page:

1. Updates the borrowing row with return date and status.
2. Increases available_copies by 1 for the returned book.
3. Redirects back to the page with a success message.

The table below lists only records where status is Issued. A due date before
today receives an Overdue badge.


M. admin/history.php - BORROWING HISTORY
-----------------------------------------
The history page supports optional filtering:

    $filter = $_GET['filter'] ?? '';
    $allowedFilters = ['Issued', 'Returned', 'Late'];

The allowed list makes sure the filter is one of the expected status values.
Only then does PHP add a WHERE condition to the SQL.

The query joins borrowings, books, and members so the table can show useful
names rather than only IDs. In the HTML loop, PHP selects a badge colour:

Late     = red/danger
Returned = green/success
Issued   = yellow/warning
Overdue issued record = red/danger


N. admin/search.php - SEARCHING RECORDS
----------------------------------------
The page reads two values: search_type and keyword. A label array maps machine
values to friendly words:

    'book' => 'Book Name'

The switch/query list chooses the correct SQL search depending on what the
staff selected: book name, ISBN, author, book ID, member name, or member ID.

After the query, num_rows gives the number of matching records. PHP checks
whether the search is a book search or member search, then prints the correct
table headings and columns.


O. admin/reports.php - REPORTS
------------------------------
The type in the URL chooses the report:

    reports.php?type=all_books

The page uses if / elseif branches. Only the branch matching $type runs. For
example:

all_books
  Reads all book rows and totals of quantity and available copies.

all_members
  Reads all member rows.

currently_issued
  Joins borrowing, book, and member data where status is Issued.

currently_received
  Reads books returned on time (Returned).

late_received
  Reads late returns and uses DATEDIFF(return_date, due_date) to calculate how
  many days late each book was.

final_report
  Runs several COUNT and SUM queries for one overall library summary, groups
  books by category, and lists all transactions.

SUM() adds values, COUNT() counts rows, GROUP BY category makes one summary row
for each category, and DATEDIFF() calculates the gap between two dates.


P. admin/users.php - ADMIN USER MANAGEMENT
-------------------------------------------
This is Admin-only:

    if ($_SESSION['role'] !== 'admin')

Librarians and readers cannot use this page.

The create form validates required fields, valid email, password length, and
the role list. It then uses prepared statements:

    $userCheck = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $userCheck->bind_param('s', $username);

The ? is a placeholder. bind_param('s', ...) inserts a string safely in that
placeholder. This is safer than joining the email directly into SQL.

The page uses password_hash() and INSERT to create an account. It only displays
"User created successfully" when execute() confirms the insert worked.

For deletion, the code compares the selected user ID with the session user ID.
This stops an administrator from accidentally deleting their own account.


13. MEANING OF PHP AND SQL SYMBOLS OFTEN ASKED IN VIVA
------------------------------------------------------
$variable
  A PHP variable that stores a value, for example $name or $books.

-> 
  Calls a method or accesses information from an object. For example,
  $conn->query() calls the query method on the database connection.

[]
  Accesses an array value. $row['name'] means the name column in the row.

??
  Uses a default value when the value on the left does not exist or is null.

.
  Joins strings together in PHP. Example: 'M' . '001' becomes M001.

==
  Checks whether two values are equal.

===
  Checks whether two values and their types are equal. It is stricter.

!
  Means "not." !empty($value) means the value is not empty.

&&
  Means "and." Both conditions must be true.

||
  Means "or." At least one condition must be true.

?
  In a URL, begins query information such as ?edit=B001.
  In a prepared statement, it is a safe placeholder for a value.

:
  In the ternary operator, separates the "otherwise" answer.

AS
  Gives a calculated SQL value a temporary name. COUNT(*) AS total creates a
  result called total.

WHERE
  Chooses which rows an SQL command should use.

ORDER BY
  Sorts SQL results.

DESC / ASC
  Descending (largest/newest first) / ascending (smallest/oldest first).

LIMIT
  Restricts the number of rows returned.

JOIN ... ON
  Combines matching rows from related tables.


14. BEST WAY TO EXPLAIN A CODE BLOCK IN THE PRESENTATION
--------------------------------------------------------
Use this simple four-part answer:

1. Input: "This code receives a value from a form, URL, session, or database."
2. Check: "It validates the value or checks a condition."
3. Action: "It reads, inserts, updates, or deletes data in MySQL."
4. Output: "It shows a message, table, or redirects the user."

Example for issuing a book:
"The form sends the selected book and member to PHP. PHP checks that the dates
are valid and the member has no overdue books. Then it creates a borrowing
record and reduces the available book copies. Finally it redirects back with a
success message."
