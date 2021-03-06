<?php namespace Rareloop\Primer;

use Rareloop\Primer\Templating\ViewData;
use Rareloop\Primer\Events\Event;
use Rareloop\Primer\Primer;

/**
 * File system helper class
 */
class FileSystem
{

    /**
     * Retrieve data for a patter
     *
     * @param  String $id The id of the pattern
     * @param  Boolean $resolveAlias Whether or not to resolve data from aliased patterns (e.g. button~outline -> button)
     * @return ViewData     The decoded JSON data
     */
    public static function getDataForPattern($id, $resolveAlias = false)
    {
        $data = array();

        $id = Primer::cleanId($id);

        // Load the Patterns default data
        $defaultData = @file_get_contents(Primer::$PATTERN_PATH . '/' . $id . '/data.json');

        if ($defaultData) {
            $json = json_decode($defaultData);

            if ($json) {
                // Merge in the data
                $data += (array)$json;
            }
        }

        if ($resolveAlias) {
            // Parent data - e.g. elements/button is the parent of elements/button~primary
            $parentData = array();

            // Load parent data if this is inherit
            if (preg_match('/(.*?)~.*?/', $id, $matches)) {
                $parentData = FileSystem::getDataForPattern($matches[1]);
            }

            // Merge the parent and pattern data together, giving preference to the pattern data
            $data = array_replace_recursive((array)$parentData, (array)$data);
        }

        // Create the data structure
        $viewData = new ViewData($data);

        // Give the system a chance to mutate the data
        ViewData::fire($id, $viewData);

        // Return the data structure
        return $viewData;
    }
}
