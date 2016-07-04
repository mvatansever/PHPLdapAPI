# PHPLdapAPI
Open source PHP (with Slim micro framework) Ldap API.

#Description
This project based on Slim Framework 3 and use Adldap2 package for LDAP jobs. I need an API in PHP working with LDAP. But I can't found any open source project at Github or another sites.
For this reason I want to share this project and a small support to Open Source world. 

#How to Install It?
Clone this repository to your local:
**git clone https://github.com/mesutv/PHPLdapAPI.git**

Install dependencies with composer:
**composer install**

#Configurations
Should be copy config/*.php.dist files to config/*.php with you configurations.

- In **config/auth.php** must be set your Basic Authentication informations. 
- In **config/connection.php** must be set your Ldap server configs.
- In **config/settings.php** applications options or any other options what you want. In this file exist 1 option default. **basic_auth** default value is true. If don't want to use basic authentication this option must be set to false.

#Basic Authentication
You should use username and password for Basic Authentication on API request with these headers:
- **BASIC-AUTH-USERNAME**
- **BASIC-AUTH-PASSWORD**
