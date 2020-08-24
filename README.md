This project is a skeleton for building clean Symfony web APIs. It ships with most of the packages and configurations that you will need.
and helps you reducing the boring scaffolding time of a Symfony project.

## Table of Contents

- [What is installed?](https://github.com/romainjudic/symfony-api-skeleton/tree/master/skel/doc/what_is_installed.md)
- [Requirements](#requirements)
- [Installation](#installation)
    - [Create your project](#create-your-project)
    - [Create your local envfile](#create-your-local-envfile)
    - [Configure JWT encryption](#configure-jwt-encryption)
    - [Clear the cache](#clear-the-cache)
- [Usage](#usage)
    - [Development server](#development-server)
    - [Authentication](#authentication)
    - [Documentation](#documentation)

## [What is installed?](https://github.com/romainjudic/symfony-api-skeleton/tree/master/skel/doc/what_is_installed.md)

## Requirements

Make sure you have the following installed:
- A version of PHP 7.4+ (instructions may change according to your OS)
- Composer (https://getcomposer.org/download/)
- The Symfony CLI (https://symfony.com/download)
- a database with user/pwd that will be dedicated to your new app.

Then run the following command to see if your system is ready to run a Symfony app:
```bash
symfony check:requirements
```

If all is clear you can go on. Otherwise, fix what needs to be fixed before you proceed.


## Installation

### Create your project

This project is here to help you start your own Symfony application.

You can create your application using Composer's `create-project` command: it will create a new project using what is already done in this one.

```bash
composer create-project romainjudic/symfony-api-skeleton your-app-name -sdev
```

Notice: as you can see we use the "-sdev" flag because the project does not have an official stable release. This just tells Composer that it is safe to use the latest version of this repository. The project has no proper version yet, but this is coming soon.


### Create your local envfile

Project "external" configuration is achieved using "enviles".

The ".env" file is the main configuration file and does not need to be modified for now.

The ".env.local" file is a second envfile that will contain configuration data that depend on your installation.
You will need to create and fill this file.

To create it, copy the dist file ".env.local.dist" and fill the empty values:
- "APP_SECRET" is a secret string used to encode semi-sensitive data. Give it a random value.
- "DATABASE_URL" is the connection string to your database.
- **leave the "JWT_PASSPHRASE" empty for now, we will discuss it in the next part.**


### Configure JWT encryption

You can follow the instructions of [LexikJwtAuthenticationBundle's website](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md) to create your keys and passphrase for JWT encryption.


**Warning: make sure to write the passphrase that you used to generate the keys.**

**Warning: the public/private will not be stored in your Git repository (if your create one). you will need to provide/create them for each install.**

Keys and passphrase are no longer located in the config folder. They are set in your envfiles.
You may change the keys location in the ".env" file with the "JWT_SECRET_KEY" and "JWT_PUBLIC_KEY".<br/>
You **must** set your passphrase value in the "JWT_PASSPHRASE" variable of the ".env.local" file.


### Finish Symfony installation

Now that everything is configured, you can finish the Smyfony installation (clears the cache and installs assets):
```bash
composer run auto-scripts
```


## Usage

### Development server

For ease of use, you can develop your app using Symfony's development server. (Just be aware that this is not suited for production!)
You may also put the app in a classic webserver and make it target the "public/index.php" file.

Start the development server to run the app.
```bash
symfony server:start
```

Now your app should be running on "http://localhost:8000".
If everything is OK, you should see you Profiler at "http://localhost:8000/_profiler/".


### Authentication

In order to access protected API routes, you must get a JWT (JSON Web Token).

The login route is at "/auth/login".

But you still need a user to try it!
Insert one user manually in the database:
- encode a password with this command: `php bin/console security:encode-password`
- insert the user and their password in the database.

Now you can call the login route:
```
POST http://localhost:8000/auth/login

{
    "email": "your.email@test.com",
    "password": "yourpassword"
}
```

The app should respond with a token that you can now use as a Bearer token: for every API call that requires authentication, add a "Authorization: Bearer YOUR_TOKEN" header.

To see if it works, there is a default route in the app skeleton that requires authentication: "/default".


### Documentation

You can access the web GUI of the Nelmio/OpenAPI documentation of your API's routes here: "http://localhost:8000/doc/".

There is also a JSON version available at: "http://localhost:8000/doc.json".

See [NelmioApiDocBundle's documentation](https://symfony.com/doc/current/bundles/NelmioApiDocBundle/index.html) for more information baout how to write and customize the documentation.
