<?php
/**
 * Theme related functions.
 *
 */

/**
 * Get title for the webpage by concatenating page specific title with site-wide title.
 *
 * @param string $title for this page.
 * @return string/null wether the favicon is defined or not.
 */
function getTitle($title)
{
    global $origo;
    return $title . (isset($origo['title_append']) ? $origo['title_append'] : null);
}

/**
 * Create a navigation bar / menu, with submenu.
 *
 * @param string $menu for the navigation bar.
 * @return string as the html for the menu.
 */
function getNavbar($menu)
{
    // Keep default options in an array and merge with incoming options that can override the defaults.
    $default = array(
      'id'          => null,
      'class'       => null,
      'wrapper'     => 'nav',
      'create_url'  => function ($url) {
        return $url;
      },
    );
    $menu = array_replace_recursive($default, $menu);

    // Function to create urls
    $createUrl = $menu['create_url'];

    // Create the ul li menu from the array, use an anonomous recursive function that returns an array of values.
    $createMenu = function ($items, $callback) use (&$createMenu, $createUrl) {

        $html = null;
        $hasItemIsSelected = false;

        foreach ($items as $item) {

            // has submenu, call recursivly and keep track on if the submenu has a selected item in it.
            $submenu        = null;
            $selectedParent = null;

            if (isset($item['submenu'])) {
                list($submenu, $selectedParent) = $createMenu($item['submenu']['items'], $callback);
                $selectedParent = $selectedParent
                    ? "selected-parent "
                    : null;
            }

            // Check if the current menuitem is selected
            $selected = $callback($item['url'])
                ? "selected "
                : null;

            // Is there a class set for this item, then use it
            $class = isset($item['class']) && ! is_null($item['class'])
                ? $item['class']
                : null;

            // Prepare the class-attribute, if used
            $class = ($selected || $selectedParent || $class)
                ? " class='{$selected}{$selectedParent}{$class}' "
                : null;

            // Add the menu item
            $url = $createUrl($item['url']);

            if (!isset($_SESSION['user']) && shouldHideNotLoggedInNavBarItems($item['text'])) {
                // Do not print admin or account in navbar;
            } else if (shouldHideNonAdminNavBarItems($item['text']) || shouldHideNonUserNavBarItems($item['text'])) {

            } else {
                $html .= "\n<li><a {$class} href='{$url}' title='{$item['title']}'>{$item['text']}</a>{$submenu}</li>\n";
            }

            // To remember there is selected children when going up the menu hierarchy
            if ($selected) {
                $hasItemIsSelected = true;
            }
        }

        // Return the menu
        return array("\n<ul>$html</ul>\n", $hasItemIsSelected);
    };

    // Call the anonomous function to create the menu, and submenues if any.
    list($html, $ignore) = $createMenu($menu['items'], $menu['callback']);


    // Set the id & class element, only if it exists in the menu-array
    $id      = isset($menu['id'])    ? " id='{$menu['id']}'"       : null;
    $class   = isset($menu['class']) ? " class='{$menu['class']}'" : null;
    $wrapper = $menu['wrapper'];

    return "\n<{$wrapper}{$id}{$class}>{$html}</{$wrapper}>\n";
}

/**
 * Hides items in nav bar when no user has logged in.
 *
 * Hides the navbar items Admin and Konto when no user has logged in.
 *
 * @param  string   $item the navbar item to be printed in navbar
 *
 * @return boolean  true if the item should not be written, false otherwise.
 */
function shouldHideNotLoggedInNavBarItems($item)
{
    $shouldHide = false;

    if (strcmp ($item , 'Admin') === 0 || strcmp ($item , 'Konto') === 0) {
        $shouldHide = true;
    }

    return $shouldHide;
}

/**
 * Hides items in nav bar when an administrator has logged in.
 *
 * Hides the navbar items Logga in and Konto when an administrator has logged in.
 *
 * @param  string   $item the navbar item to be printed in navbar
 *
 * @return boolean  true if the item should not be written, false otherwise.
 */
function shouldHideNonAdminNavBarItems($item)
{
    $shouldHide = false;

    $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
    if (isset($acronym)) {
        if (strcmp ($acronym , 'admin') === 0) {
            if (strcmp($item , 'Logga in') === 0 || strcmp($item , 'Konto') === 0) {
                $shouldHide = true;
            }
        }
    }

    return $shouldHide;
}

/**
 * Hides items in nav bar when an user (non admin) has logged in.
 *
 * Hides the navbar items Logga in and Admin when a user has logged in.
 *
 * @param  string   $item the navbar item to be printed in navbar
 *
 * @return boolean  true if the item should not be written, false otherwise.
 */
function shouldHideNonUserNavBarItems($item)
{
    $shouldHide = false;

    $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
    if (isset($acronym)) {
        if (strcmp ($acronym , 'admin') !== 0) {
            if (strcmp($item , 'Logga in') === 0 || strcmp($item , 'Admin') === 0) {
                $shouldHide = true;
            }
        }
    }

    return $shouldHide;
}

function getSearchMovieTitleForm()
{
    $searchMovieTitleForm = <<<EOD
    <form class="search-movie-form" action="movie.php">
        <input type="hidden" name="substring" value="%"/>
        <input type="search" name="title" value="" placeholder="Ange filmtitel">
        <input type="submit" value="Sök">
    </form>
    <div class="clear-float"></div>
EOD;

    return $searchMovieTitleForm;
}
