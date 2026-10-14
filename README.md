# City Library Management System

A PHP and MySQL library management app for books, members, accounts, and borrowing records.

## Run locally with WampServer

1. Start Apache and MySQL in WampServer.
2. Create a MySQL database named `lms` and import the project's database schema/data if provided by the project owner.
3. Set the database connection values in PHP environment variables when needed: `DB_HOST`, `DB_USER`, `DB_PASSWORD`, and `DB_NAME`. Defaults are `localhost`, `root`, an empty password, and `lms` for a standard local WampServer setup.
4. Copy the project into the web root and open it through the local Apache URL.

## Apply the database relationship fix

The updated PHP code records the reader-to-member link and the staff user who issues or receives a book. To add the matching constraints and audit columns to an existing client database:

1. Export a backup of the `lms` database in phpMyAdmin.
2. Check and repair any reader accounts without a matching member as described in [the client database guide](CLIENT_DATABASE_RELATIONSHIP_GUIDE.txt).
3. Run [`database_relationships.sql`](database_relationships.sql) once against that database.
4. Deploy the updated PHP files and follow the verification checklist in the guide.

The SQL migration is a one-time change. Back up first, and inspect the database before retrying if MySQL reports that an index, column, or constraint already exists.

## Deploy for the client

GitHub stores and shares the source code; GitHub Pages cannot run PHP or connect to MySQL. To give the client a working site, deploy this project to a PHP-capable web host with MySQL, configure the four database environment variables there, import or migrate the client's database, and share the host's URL. Do not commit client database exports, passwords, or other private data.
