# PHP VERSION (AT LEAST)
8.2.12

# INSTALL DEPENDENCIES
composer install

# LINK STORAGE
php artisan storage:link

# GENERATE
php artisan key:generate

# MIGRATE DATABASE
php artisan migrate

# RUN DEVELOPMENT MODE
php artisan serve

# ADD DEFAULT ADMIN
php db:seed

# DEFAULT CREDS
username: default@admin.test
password: password