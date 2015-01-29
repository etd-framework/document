<?php
/**
 * Part of the ETD Framework Document Package
 *
 * @copyright   Copyright (C) 2015 ETD Solutions, SARL Etudoo. Tous droits réservés.
 * @license     Apache License 2.0; see LICENSE
 * @author      ETD Solutions http://etd-solutions.com
 */

namespace EtdSolutions\Document\Renderer;

use EtdSolutions\Application\Web;
use Joomla\Language\Text;

defined('_JEXEC') or die;

class MessageRenderer extends DocumentRenderer {

    public function render() {

        // Initialise variables.
        $buffer = '';
        $lists  = array();
        $app    = Web::getInstance();
        $text   = $app->getText();

        // Get the message queue
        $messages = $app->getMessageQueue();

        // Build the sorted message list
        if (is_array($messages) && !empty($messages)) {
            foreach ($messages as $msg) {
                if (isset($msg['type']) && isset($msg['message'])) {
                    $lists[$msg['type']][] = $msg;
                }
            }
        }

        $buffer = '<div id="message-container">';

        if (!empty($lists)) {
            $buffer .= '<ul class="alerts-list">';
            foreach ($lists as $type => $messages) {
                $buffer .= '<li>';
                $buffer .= '<div class="alert alert-' . $type . ' alert-dismissable" role="alert">';
                $buffer .= '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">' . $text->translate('APP_GLOBAL_CLOSE') . '</span></button>';
                foreach ($messages as $i => $message) {
                    if ($i > 0) {
                        $buffer .= "<br>";
                    }
                    $buffer .= $message['message'];
                }
                $buffer .= '</div>';
                $buffer .= '</li>';
            }
            $buffer .= '</ul>';
        }

        $buffer .= '</div>';

        return $buffer;
    }

}