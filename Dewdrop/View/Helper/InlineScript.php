<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * Display an inline script snippet following the printing of all
 * enqueued script tags.  This can be necessary when the JavaScript
 * queued up by a view helper includes many options that are specific
 * to a single instance of that helper.
 *
 * For example, if you had two different color pickers on a page and
 * each color picker had a different default color and pre-set
 * palette options, you'd need to use inline script snippets to
 * pass those options to the specific fields that required them.
 */
class InlineScript extends AbstractHelper
{
    /**
     * Queue up the rendering of the supplied template.  It will be
     * renderered as a partial with the supplied options array passed
     * into the partial.
     *
     * @param string $templateName
     * @param array $options
     * @return void
     */
    public function direct($templateName, array $options = array())
    {
        $helper = $this;

        add_action(
            (is_admin() ? 'admin_print_footer_scripts' : 'print_footer_scripts'),
            function () use ($helper, $templateName, $options) {
                echo $helper->open();
                echo $helper->partial($templateName, $options);
                echo $helper->close();
            }
        );
    }

    /**
     * Open the script tag.  This isn't really intended to be called from
     * outside this helper, but it has to be public because it is used in
     * the anonymous function created during the direct() method.
     *
     * @return string
     */
    public function open()
    {
        return '<script type="text/javascript">//<![CDATA[' . PHP_EOL;
    }

    /**
     * Close the script tag.  This isn't really intended to be called from
     * outside this helper, but it has to be public because it is used in
     * the anonymous function created during the direct() method.
     *
     * @return string
     */
    public function close()
    {
        return '//]]></script>';
    }
}
