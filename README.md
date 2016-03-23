# JEasyUIBackendBundle

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require officeutils/JEasyUIBackendBundle "~1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new OfficeUtils\JEasyUIBackendBundle\OfficeUtilsJEasyUIBackendBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Add and configure PHPActiveRecordBundle to work with your database. 
-------------------------
Prepare your database models according to PHPActiveRecord requirements.

Step 4: Enable JEasyUI bundle routing at routing.yml
-------------------------
```yml
office_utils_j_easy_ui_backend:
    resource: "@OfficeUtilsJEasyUIBackendBundle/Controller/"
    type:     annotation
```

Step 5: Use routes to work with your model. 
-------------------------
For example:
```text
To CREATE User - POST http://localhost/User/add?username=user1
To READ User as JSON - GET http://localhost/User/get?user_id=1
To EDIT User - POST http://localhost/User/update?user_id=1&username=user2
To DELETE User - POST http://localhost/User/delete?user_id=1

To get DATAGRID data as JSON - GET http://localhost/User/datagrid
Ti get COMBOBOX data as JSON - GET http://localhost/User/combobox
```

Step 5: Extend JEasyUIController. 
-------------------------

To customize your model behavior (sorting, ordering, grouping and etc.) extend JEasyUIController and override appropriate methods. 

```php
...
use OfficeUtils\JEasyUIBackendBundle\Controller\JEasyUIBackendController as Controller;
...
class UserController extends Controller
{
    ...
    protected function getSelect() {};
    protected function getJoins() {};
    protected function getGroups() {};
    protected function getConditions() {};
    protected function getOrder() {};
    ...
}
```

Set up your controller using at routing.yml

```yml
user_datagrid:
    path:   /User/datagrid
    defaults: { _controller: ACME\UserBundle\Controller\UserController::apiDatagrid, object:User }
```
