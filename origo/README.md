Origo-base
==================

A boilerplate for smaller websites or webbapplications using PHP.

Built by Gunnar Eriksson

Usage
------------------

Origo is a boilerplate for smaller websites or webbapplications using PHP. The structure
of Origo is divided in three main parts.

The webroot folder contains side controllers, which are accessible using HTTP-requests.
The folder contains all files visible to users of the website.
The webroot folder is structured in to cache folder (used by the img.php-script), the
css folder for all css files, the img folder for all images for the webbsite and the
js folder for all javascript files.

The src folder containing all classes used in Origo. An autoloader loads all classes
in the src folder automatically to the system and the classes do not need to be
included in the side controllers.

The theme folder containing functions for the theme of the website or webbapplication
where a index template builds the template of the website or webbapplication.

Classes
------------------

Main classes included in Origo.

Database: contains functions to connect to a database via PDO. Connecting setup
is located in the config file.

FileUploader: Contains functions to upload image to a database.

Image: contains functions for image processing via the img.php script.

TextFilter: Applies a number of filters for a text string.

User: Provides functions to check if a user has logged in or not. The information
is saved in the session.


License
------------------

This software is free software and carries a MIT license.


------------------
 .
..:

Copyright (c) 2016 Gunnar Eriksson
