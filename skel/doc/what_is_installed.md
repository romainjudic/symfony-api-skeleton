This skeleton ships with the following packages:
- symfony/skeleton (skeleton project)
- symfony/apache-pack
- annotations
- symfony/orm-pack
- doctrine/doctrine-fixtures-bundle
- fzaninotto/faker
- symfony/serializer-pack
- symfony/security-bundle
- lexik/jwt-authentication-bundle
- cors
- symfony/form
- symfony/validator
- nelmio/api-doc-bundle
- symfony/maker-bundle (dev)
- debug-pack (dev)
- overtrue/phplint (dev)
- squizlabs/php_codesniffer (dev)
- squizlabs/php_codesniffer (dev)

_Notice:_ all the packs listed here are automatically unpacked when installed.

In the rest of the document we will go through the installation steps that led to these packages.

## System requirements

As described in the README, there are a few things that need to be present before we can start a new Symfony app:
- a PHP 7.4+ installation
- the Composer executable
- the Symfony CLI.

Also, the command `symfony check:requirements` command must be successful.


## Create the Symfony app itself

We use the official Symfony skeleton to create our base structure:
```bash
composer create-project symfony/skeleton symfony-api-skeleton # This is my project's name, feel free to use yours instead
```

## Minimal packages for a web API

There are several packages that are vital for a web API, such as authentication, database management, serialization...

- Let's start with a recipe that will let us use our app under an Apache environment:
    ```bash
    composer require symfony/apache-pach
    ```
    _Notice:_ this pack does not actually install anything. It only triggers a "recipe" that creates a ".htacess" file inside our "public" directory.


- Now let's install the annotation system to be able to declare route/validation/schemas directly using annotations. It will install DOctrine annotations and the FrameworkExtraBundle, that brings a lot of usefull tools:
    ```bash
    composer require annotations
    ```

- As a web API, our app will need a way to handle CORS requests gracefully:
    ```bash
    composer require cors
    ```
    Then configure the ".env" file to accept requests from any URL: `CORS_ALLOW_ORIGIN=^https?://.\*?\$` (you can make more restrictions if you need to).
    We also create an event subscriber ("AdditionalHeadersSubscriber") that will add a "Vary=Origin" header to make development easier.

- Next, we need the serializer to handle the request/response formats:
    ```bash
    composer require symfony/serializer-pack
    ```
    We configure default serializers in the "config/packages/serializer.yaml" file **which extends the service configuration file** in order to:
    - use object getter/setters by default
    - serialize DateTime objects using a specific format ("Y-m-d H:i:s" by default).

- In order to process request bodies, we need the Form and Validator components:
    ```bash
    composer require symfony/form symfony/validator
    ```


## Development tools

We will go faster with those ones because ther's not much to say:
```bash
composer require --dev symfony/maker-bundle debug-pack overtrue/phplint squizlabs/php_codesniffer
```


## ORM

In order to persist data to the database, we install Doctrine's ORM:
```bash
composer require symfony/orm-pack
```
_Notice:_ The Doctrine ORM requires a PDO driver for the DBMS you are using. For example, if you plan to use a MySQL database, you must install pdo_mysql.

Once installed, we have to configure the DATABASE_URL in our envfiles (".env.local" because this changes for every installation). Please make sure to specify the "server_version" in the DATABASE_URL or in the "config/packages/doctrine.yaml" configuration file.


## Security

We will use a JWT-based authentication for our API.

- To do so, we first need to install the main Security component:
    ```bash
    composer require symfony/security-bundle
    ```

- The authentication system depends on a user interface, so let's create it:
    ```bash
    php bin/console make:user User
    ```

- We slightly modify the generated User entity so that it uses a table called "app_user" instead of simply "user" (this is to avoid naming conflicts).
    To reflect the changes on the database, create and execute a migration:
    ```bash
    php bin/console make:migration
    php bin/console d:m:m
    ```

- Now that the database contains our "app_user" table, let's insert a user manually:
    - generate a password in the terminal using the `php bin/console security:encode-password` command
    - insert the user in the table.


- The LexikJWTAuthenticationBundle will handle the JWTstuff for us. Follow the instructions on [their GitHub page](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md) to install it.

    **Warning: do not forget to write down the passphrase in the "JWT_PASSPHRASE" variable of your ".env.local" envfile.**

    **Warning: the keys are not stored in the Git repo. The will have to be generated/provided on every install.**

    Those keys location can be customized using the "JWT_SECRET_KEY" and "JWT_PUBLIC_KEY" variables in the envfile.


- Next we create a route that will support the login action, directly in the "config/routes.yaml" file. This route has no controller, it only has to exists.

- In "security.yaml", we configure one firewall dedicated to authentication that will use LexikJwtAuthenticationBundle and the route we just created.

- We also add a "main" firewall that will protect all the "classic" routes with the JWT authenticator.

- Finally, we create an event subscriber (JwtAuthenticationSubscriber) to customize the authentication process: throw the right exceptions when it fails, add custom data to JWTs, etc...


## Base controller

The BaseController is a base class that all other controllers must inherit. It contains a bunch of usefull methods to format responses, process forms, etc...

## Exception system

Exceptions in web APIs must be well formatted to be correctly understood by client apps.
We choose to use a JSON:API(-like) format to represent exceptions.

Every exception that goes out of the app is represented by a JSON object:
```json
{
    "status": 400,
    "type": "invalid_request_format",
    "title": "Request format is invalid",
    "detail": "Missing parameter XX in the request"
}
```
One can also add extra properties to this object when needed, such as an "error" property that would list validation errors after a failed POST request.

A special exception class is used for that purpose: ApiException. It is a parent class that contains the necessary logic to be serialized correctly.

In this class, we define exception "type"s: categories for errors.
To each "type" corresponds a unique "title".

Every exception going out of the API will have to inherit this class and specify a few arguments such as the HTTP code, the type and a details message.

In order to make sure every exception will end up inheriting this class, we add an event subscriber: ApiExceptionSubscriber.


## Fixtures system

Fixtures allow us to generate a large amount of random data to fill our test/dev databases automatically.

**Warning: fixtures are not available in the production environment so you must not rely on them to insert strategic data such as references. Instead you should use the migrations system.**

```bash
composer require --dev doctrine/doctrine-fixtures-bundle
composer require --dev fzaninotto/faker
```

We create a base fixtures class that contains many tools to create many occurences of the same class seamlessly.


## API documentation

The NelmioApiDocBundle is a great tool to write OpenAPI doc within code using annotations.

```bash
composer require twig assets nelmio/api-doc-bundle
```
**Warning: you have to accept when Composer asks you if you want to execute the recipe.**

We want our doc to be available at "/doc" and "/doc.json" so we modify the routes in the "config/routes/nelmio_api_doc.json" file.

Finally, create a firewall named "doc" to disable "security" for docuemntation routes, because our doc is public.

To go further and understand how Swagger annotations work, please follow [this link](https://symfony.com/doc/current/bundles/NelmioApiDocBundle/index.html).