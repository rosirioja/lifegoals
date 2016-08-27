<?php

if (! function_exists('json_to_string')) {
    /**
    * Convert json to string
    *
    * @access public
    * @param json
    * @return string
    */
    function json_to_string($json = [])
    {
        if (empty($json)) return '';

        $string = '';
        foreach ($json as $i => $row) {
            foreach ($row as $key => $value) {
                $string .= $value.'<br/>';
            }
        }

        return $string;
    }
}
