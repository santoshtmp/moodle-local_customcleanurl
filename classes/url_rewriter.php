<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * 
 * @package    local_customcleanurl
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

namespace local_customcleanurl;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

class url_rewriter implements \core\output\url_rewriter
{
    /**
     * Rewrite moodle_urls into another form. 
     * By using customcleanurl if not possible.
     *
     * @param \moodle_url $url a url to potentially rewrite
     * @return \moodle_url Returns a new, or the original, moodle_url;
     */
    public static function url_rewrite(moodle_url $url)
    {
        global $CFG;
        if (empty($CFG->upgraderunning)) {
            return \local_customcleanurl\local\helper::get_clean_url($url);
        }
        return $url;
    }



    /**
     * Gives a url rewriting plugin a chance to rewrite the current page url
     * avoiding redirects and improving performance.
     *
     * @return void
     */
    public static function html_head_setup()
    {
        global $CFG, $PAGE;
        $clean_url = $PAGE->url->out(false);

        $orig = $PAGE->url->raw_out(false);
        $output = '';
        // $cat_url = new moodle_url('/course/index.php', ['categoryid' => 1]);
        // $output .= " <br><br><br><br> ";
        // $output .=  $clean_url . ' <br> url_rewriter html_head_setup <br> ' . $orig;
        // $output .= " <br><br><br><br> ";
        // $output .= $cat_url->raw_out(false);
        // $output .= " <br><br> ";
        // $output .= $cat_url->out(false);


        if (isset($CFG->uncleanedurl)) {
            // This page came through router uncleaning.
            $output .= self::get_base_href($CFG->uncleanedurl);
            $output .= self::get_anchor_fix_javascript($clean_url);
        } else {
            // This page came through its canonical/legacy address (not clean version).
            // $orig = $PAGE->url->raw_out(false);
            // if ($orig != $clean_url) {
            //     // This page URL could have been cleaned up, so do it!
            //     $output .= self::get_base_href($orig);
            //     $output .= self::get_replacestate_script($clean_url);
            //     $output .= self::get_anchor_fix_javascript($clean_url);
            //     $output .= self::get_link_canonical();
            //     self::mark_apache_note($clean_url);
            // }
        }

        return $output;
    }

    private static function get_base_href($uncleanedurl)
    {
        return "<base href=\"{$uncleanedurl}\">\n";
    }

    /**
     * Rewire #anchor links dynamically
     *
     * This fixes an edge case bug where in the page there are simple links
     * to internal #hash anchors. But because we add a base href tag these
     * links now appear to link to another page and not this one and cause
     * a reload. So on the fly we detect this and insert the clean url base.
     *
     * @param $clean string
     * @return string
     */
    private static function get_anchor_fix_javascript($clean)
    {
        return <<<HTML
<script>
        // testttt

document.addEventListener('click', function (event) {
    var element = event.target;
    while (element.tagName != 'A') {
        if (!element.parentElement) {
            return;
        }
        element = element.parentElement;
    }
    if (element.getAttribute('href').charAt(0) == '#') {
        element.href = '{$clean}' + element.getAttribute('href');
    }
}, true);
</script>
HTML;
    }
}
