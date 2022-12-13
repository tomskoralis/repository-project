# Crypto Market

### Instructions to run the website:

1. Clone this repository using the command:
   ```
   git clone https://github.com/tomskoralis/repository-project
   ```
2. Install the required packages using the command:
   ```
   composer install
   ```
3. Make a copy of the `.env.example` and rename it to `.env`.
4. Enter your https://coinmarketcap.com/ API key in the `.env` file.
5. Create a mySQL 8.0 database with 2 tables and 1 view with these SQL queries:
   ```
   create table users
   (
      id       int auto_increment
      primary key,
      name     varchar(255)   not null,
      email    varchar(255)   not null,
      password varchar(255)   null,
      wallet   decimal(22, 2) not null,
      constraint unique_email
      unique (email)
   );
   ```
   ```
   create table transactions
   (
       id      int auto_increment
           primary key,
       user_id int            not null,
       symbol  varchar(10)    not null,
       price   decimal(16, 8) not null,
       amount  decimal(16, 8) not null,
       time    datetime       null
   );
   ```
   ```
   create view balances as
   select transactions.user_id                             as id,
          transactions.symbol                              as symbol,
          cast(sum(transactions.amount) as decimal(16, 8)) as balance
   from users
            join transactions on users.id = transactions.user_id
   group by transactions.user_id, transactions.symbol
   having cast(sum(transactions.amount) as decimal(16, 8)) > 0;
   ```
6. Enter your database credentials in the `.env` file.
    - Fields `DATABASE_NAME`, `DATABASE_USER`, `DATABASE_PASSWORD` are required.
    - Field `DATABASE_HOST` is localhost by default and `DATABASE_DRIVER` is pdo_mysql by default.
7. Test the website by running it from the `public` directory using the command:
   ```
   php -S localhost:8000
   ```