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
use EtdSolutions\Document\Document;
use Joomla\Language\Text;

defined('_JEXEC') or die;

class FootRenderer extends DocumentRenderer {

    public function render() {

        return $this->fetchFoot($this->_doc);

    }

    /**
     * Generates the head HTML and return the results as a string
     *
     * @param   Document $document The document for which the head will be created
     *
     * @return  string  The head hTML
     */
    public function fetchFoot($document) {

        $buffer = '';

        // Generate stylesheet links
        if (count($document->stylesheets['foot'])) {
            foreach ($document->stylesheets['foot'] as $src) {
                $buffer .= '<link rel="stylesheet" href="' . $src . '">' . "\n";
            }
        }

        // Generate stylesheet declarations
        if (count($document->styles['foot'])) {
            $buffer .= '<style>' . "\n";
            foreach ($document->styles['foot'] as $content) {
                $buffer .= $content . "\n";
            }
            $buffer .= '</style>' . "\n";
        }

        // Generate scripts
        if (count($document->scripts['foot'])) {
            foreach ($document->scripts['foot'] as $src) {
                $buffer .= '<script src="' . $src . '"></script>' . "\n";
            }
        }

        // Generate script declarations
        if (count($document->js['foot']) || count($document->requireJS) || count($document->domReadyJs) || count(Text::script())) {

            $app = Web::getInstance();
            $buffer .= '<script>';

            // On prépare le buffer pour les scripts JS.
            $js = "\n";

            if (count(Text::script())) {
                $js .= "if (typeof EtdSolutions !== undefined) {\n";
                $js .= "  var Text = EtdSolutions.Framework.Language.Text;";
                $js .= "  Text.load(" . json_encode(Text::script()) . ");\n";
                $js .= "}\n";
            }

            if (count($document->js['foot'])) {
                foreach ($document->js['foot'] as $content) {
                    $js .= $content . "\n";
                }
            }

            if (count($document->requireJS)) {
                
                $js .= "requirejs.config({\n";
                $js .= "  baseUrl: '" . $app->get('uri.base.full') . "vendor/',\n";
                $js .= "  paths: {\n";

                $modules = array();
                foreach ($document->requireModules as $id => $path) {
                    $modules[] = "    " . $id . ": '". $path ."'";
                }
                $js .= implode(",\n", $modules) . "\n";

                $js .= "  }\n";
                $js .= "});\n";
                
                foreach ($document->requireJS as $id => $scripts) {
                    $modules = explode(",", $id);
                    $js .= "require(" . json_encode($modules) . ", function(" . implode(",", $modules) . ") {\n";
                    foreach($scripts as $content) {
                        $js .= "  " . $content . "\n";
                    }
                    $js .= "});\n";
                }
            }

            if (count($document->domReadyJs)) {
                $js .= "jQuery(document).ready(function() {\n";
                foreach ($document->domReadyJs as $content) {
                    $js .= $content . "\n";
                }
                $js .= "});\n";
            }

            // On compresse le JavaScript avec JShrink si configuré.
            if ($app->get('minify_inline_js', false)) {
                $js = \JShrink\Minifier::minify($js);
            }

            $buffer .= $js . '</script>' . "\n";
        }

        return $buffer;
    }

}