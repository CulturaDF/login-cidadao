Login Cidadão
=============

This is the source code for the 'Login Cidadão' (Citizen's Login) project.

This project's main objective is to provide a way for citizens to authenticate against official online services, eliminating the need to create and maintain several credentials on several services.

It also allows government agencies to better understand its citizen's needs and learn how to interact more effectively with them.

*Note*: Since this project is just on it's initial stages, it's not recommended to fork it just yet.

Dependencies
============

 * PHP >=5.3.3
 * composer
 * node.js

Setup - Development
===================

Setting up on Linux
-------------------

### Requirements
 * Sudoer user
 * PHP CLI
 * ACL-enabled filesystem
 * Composer

### Before you start
It's highly recommended to create your `app/config/parameters.yml` before installing to avoid database connection problems.

You can start by using `app/config/parameters.yml.dist` as a template by simply copying it to the same folder but naming it as `parameters.yml`, then edit the default values.

### Running the script
Just execute the `install.sh` script and follow instructions in case of errors or warnings.
 

Setting up on Windows
---------------------
Currently we do not have a setup script for Windows, but it should be pretty straightforward to convert the install.sh to be Windows compatible.

General Steps for Installation
------------------------------

1. Make sure the following directories are writeable by your http/PHP user via ACL permissions ([you can read more here](http://symfony.com/doc/current/book/installation.html)):
  * app/cache
  * app/logs
  * web/uploads
2. Check if your environment meets Symfony's prerequesites:

    `$ php app/check.php`

3. Run `$ composer install`
4. Create the database if you didn't do it yet:

    `$ php app/console doctrine:database:create`

5. Create the schema:

    `$ php app/console doctrine:schema:create`

6. Point your server's Document Root to the /web folder and make sure app.php is your index. Symfony already comes with .htaccess to do it for you on Apache.
