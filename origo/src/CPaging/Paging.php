<?php
/**
 * Provides HTML tables and functions for returning number of hits, arrows to
 * be able to sort columns in ascending or descending order and support for
 * paging.
 */
class Paging
{
    /**
     * Create links for hits per page.
     *
     * @param array $hits       a list of hits-options to display.
     * @param array $current    current value, default null
     * .
     * @return string as a link to this page.
     */
    public function getHitsPerPage($hits, $current=null)
    {
        $nav = "Träffar per sida: ";
        foreach($hits AS $val) {
            if($current == $val) {
                $nav .= "<span class='selected'>$val </span>";
            }
            else {
                $nav .= "<a href='" . $this->getQueryString(array('hits' => $val)) . "'>$val</a> ";
            }
        }

        return $nav;
    }

    /**
     * Use the current querystring as base, modify it according to $options and return the modified query string.
     *
     * @param array $options    to set/change.
     * @param string $prepend   this to the resulting query string
     *
     * @return string with an updated query string.
     */
    private function getQueryString($options=array(), $prepend='?')
    {
        // parse query string into array
        $query = array();
        parse_str($_SERVER['QUERY_STRING'], $query);

        // Modify the existing query string with new options
        $query = array_merge($query, $options);

        // Return the modified querystring
        return $prepend . htmlentities(http_build_query($query));
    }

    /**
     * Create navigation bar among pages.
     *
     * @param integer $hits per page.
     * @param integer $page current page.
     * @param integer $max number of pages.
     * @param integer $min is the first page number, usually 0 or 1.
     * @return string as a link to this page.
     */
    public function getPageNavigationBar($hits, $page, $max, $min=1)
    {
        $nav = "<div class='navigationBar'>";
        $nav .= "<ul class='backButtons'>";
        $nav .= "<li>";
        $nav .= ($page != $min) ? "<a href='" . $this->getQueryString(array('page' => $min)) . "'><span class='button'>&lt;&lt;</span></a> " : "<span class='button'>&lt;&lt;</span>";
        $nav .= "</li>";
        $nav .= "<li>";
        $nav .= ($page > $min) ? "<a href='" . $this->getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'><span class='button'>&lt;</span></a> " : "<span class='button'>&lt;</span>";
        $nav .= "</li>";
        $nav .= "</ul>";

        $nav .= "<ul class='forwardButtons'>";
        $nav .= "<li>";
        $nav .= ($page < $max) ? "<a href='" . $this->getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'><span class='right-button'>&gt;</span></a> " : "<span class='right-button'>&gt;</span>";
        $nav .= "</li>";
        $nav .= "<li>";
        $nav .= ($page != $max) ? "<a href='" . $this->getQueryString(array('page' => $max)) . "'><span class='right-button'>&gt;&gt;</span></a> " : "<span class='right-button'>&gt;&gt;</span>";
        $nav .= "</li>";
        $nav .= "</ul>";

        $nav .= "<ul class='pageNumbers'>";
        for($i=$min; $i<=$max; $i++) {
            $nav .= "<li>";
            if($page == $i) {
                $nav .= "<span class='button selected'>$i</span>";
            }
            else {
              $nav .= "<a href='" . $this->getQueryString(array('page' => $i)) . "'><span class='button'>$i</span></a> ";
          }
          $nav .= "</li>";
        }
        $nav .= "</ul>";

        $nav .= "</div>";

        return $nav;
    }
}
