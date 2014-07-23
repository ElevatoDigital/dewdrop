<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Mail\View;

use Dewdrop\View\View as BaseView;

/**
 * This class helps you render a nice HTML template for an email using this
 * template from Github:
 *
 * https://github.com/leemunroe/html-email-template
 *
 * The template has already been run through Premailer (http://premailer.dialect.ca/)
 * so all of its styles are inlined and it is tested in a variety of mail clients.
 * This view sub-class gives you a number of helpers that make it easier to add
 * content to this template without having to ready the terrible markup and styles.
 *
 * A typical view script would look like this:
 *
 * <pre>
 * echo $this->document()->open('My message title');
 * echo $this->content()->open();
 *
 * echo $this->p('Hello, world!');
 * echo $this->h1('This is a header');
 * echo $this->h2('This is a slightly smaller header');
 * echo $this->button('CALL TO ACTION!', 'http://example.org/');
 *
 * echo $this->content()->close();
 *
 * // Unsubscribe or other footer content here
 *
 * echo $this->document()->close();
 * </pre>
 */
class View extends BaseView
{
    /**
     * Register all the custom helpers needed to render the HTML email template.
     */
    public function init()
    {
        $this
            ->registerHelper('document', '\Dewdrop\Mail\View\Helper\Document')
            ->registerHelper('content', '\Dewdrop\Mail\View\Helper\Content')
            ->registerHelper('footer', '\Dewdrop\Mail\View\Helper\Footer')
            ->registerHelper('p', '\Dewdrop\Mail\View\Helper\Paragraph')
            ->registerHelper('a', '\Dewdrop\Mail\View\Helper\Link')
            ->registerHelper('h1', '\Dewdrop\Mail\View\Helper\HeaderOne')
            ->registerHelper('h2', '\Dewdrop\Mail\View\Helper\HeaderTwo')
            ->registerHelper('button', '\Dewdrop\Mail\View\Helper\Button');
    }
}
