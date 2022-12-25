# Crypto Market website

![home.png](screenshots%2Fhome.png)

![1wallet.gif](screenshots%2F1wallet.gif)

![2currency.gif](screenshots%2F2currency.gif)

![3transactions.gif](screenshots%2F3transactions.gif)

### Technologies used:
- PHP 7.4
- mySQL 8.0.31
- Twig 3.4
- Tailwind CSS 3.2.4
- Flowbite 1.5.5

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
4. Enter your coinmarketcap.com API key in the `.env` file.
5. Create a mySQL database and run the `currencies_website_localhost.sql` file using the restore with 'mysql' command to create the tables.
6. Enter your database credentials in the `.env` file.
    - Fields `DATABASE_NAME`, `DATABASE_USER`, `DATABASE_PASSWORD` are required.
    - Field `DATABASE_HOST` is localhost by default and `DATABASE_DRIVER` is pdo_mysql by default.
7. Edit the `constants.php` file in the `app` directory.
8. Test the website by running it from the `public` directory using the command:
   ```
   php -S localhost:8000
   ```