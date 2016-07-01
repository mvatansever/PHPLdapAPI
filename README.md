# PHPLdapAPI
Open source PHP (with Slim micro framework) Ldap API.

#Description
This project based on Slim Framework 3 and use Adldap2 package for LDAP jobs. I need an API in PHP working with LDAP. But I can't found any open source project at Github or another sites.
For this reason I want to share this project and a small support to Open Source world. 

#How to Work It?
Once you must be install dependencies with composer:

**composer install**

After install the dependencies should be configure the auth and Ldap server configurations:

- In **config/auth.php** must be set your Basic Authentication informations. You must be delete at public/index.php:24 line number If you don't want to basic auth middleware.
- In **config/connection.php** must be set your Ldap server configs.

#Basic Authentication
You should use username and password for Basic Authentication on API request with these headers:
- **BASIC-AUTH-USERNAME**
- **BASIC-AUTH-PASSWORD**
