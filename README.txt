EX_FORM_VALUES
===============

This example module saves temporary values from a form and process them later
in a controller. To do that we’ll use the Form API and the tempstore
or the temporary store storage system of Drupal 8.

The use case is the following: we need to build a simple RSS file reader
(a form) where the user could introduce the URL of an RSS file and the number
of items to retrieve from.  Next, in a new page (a controller),
the application should display the list of items from the RSS file with
a link to each syndicated page.


This module has two main parts:

1. A form (WithStoreForm.php) to collect the URL of the RSS file and the
number of items retrieve from. This form will redirect the user the
controller.

2. A controller to display a list of items from the RSS file introduced
in the previous form.

We'll also use custom service with several methods for getting items from
the RSS file and to prepare the render array.

Module tree
-----------

|-- ex_form_values.info.yml
|-- ex_form_values.routing.yml
|-- ex_form_values.services.yml
`-- src
    |-- Controller
    |   `-- SimpleController.php
    |-- Form
    |   `-- WithStoreForm.php
    `-- MyServices.php

Install
-------
- Download the module in your /modules/custom directory.
- Install it with Drupal Console: drupal moi ex_form_values

Use
---
To use the form, just go to /ex_form_values/form/myform
