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

        // On ajoute les textes pour les traductions.
        if (count(Text::script())) {
            $document->addRequireJSModule('text', 'etdsolutions/js/language/text')
                     ->requireJS('text', "text.load(" . json_encode(Text::script()) . ");");
        }

        // Generate script declarations
        if (count($document->js['foot']) || count($document->requireModules) || count($document->requireJS) || count($document->domReadyJs)) {

            $app = Web::getInstance();
            $buffer .= '<script>';

            // On prépare le buffer pour les scripts JS.
            $js = "\n";

            if (count($document->js['foot'])) {
                foreach ($document->js['foot'] as $content) {
                    $js .= $content . "\n";
                }
            }

            if (count($document->requireModules)) {

                // On crée la configuration de requireJS
                $js .= "requirejs.config({\n";
                $js .= "  baseUrl: '" . $app->get('uri.base.full') . "vendor/'";

                $shim  = array();
                $paths = array();
                foreach ($document->requireModules as $module) {
                    $paths[] = "    " . $module['module'] . ": '" . $module['path'] . "'";
                    if ($module['shim'] !== false) {
                        $shim[] = "    " . $module['module'] . ": " . json_encode($module['shim']);
                    }
                }

                if (count($shim)) {
                    $js .= ",\n  shim: {\n";
                    $js .= implode(",\n", $shim) . "\n";
                    $js .= "  }";
                }
                if (count($paths)) {
                    $js .= ",\n  paths: {\n";
                    $js .= implode(",\n", $paths) . "\n";
                    $js .= "  }";
                }

                // require-css
                $js .= ",\n  map: {\n";
                $js .= "    '*': {\n";
                $js .= "      'css': 'etdsolutions/requirecss/css.min'\n";
                $js .= "    }\n";
                $js .= "  }";

                $js .= "\n});\n";
            }

            if (count($document->requireJS)) {

                foreach ($document->requireJS as $id => $scripts) {

                    $content = "";
                    $modules = explode(",", $id);

                    foreach ($scripts as $script) {
                        if (!empty($script)) {
                            $content .= "  " . $script . "\n";
                        }
                    }

                    $js .= "require(" . json_encode($modules);

                    if (!empty($content)) {
                        $modules = array_filter($modules, function ($module) {

                            return (strpos($module, '!') === false);
                        });
                        $modules = array_map(function($module) {
                            if (strpos($module, '/') !== false) {
                                $module = substr($module, strrpos($module, '/') + 1);
                            }
                            return $module;
                        }, $modules);
                        $js .= ", function(" . implode(",", $modules) . ") {\n";
                        $js .= $content;
                        $js .= "}";
                    }

                    $js .= ");\n";

                }
            }

            /*if (count($document->domReadyJs)) {
                $js .= "jQuery(document).ready(function() {\n";
                foreach ($document->domReadyJs as $content) {
                    $js .= $content . "\n";
                }
                $js .= "});\n";
            }*/

            // On compresse le JavaScript avec JShrink si configuré.
            if ($app->get('minify_inline_js', false)) {
                $js = \JShrink\Minifier::minify($js);
            }

            $buffer .= $js . '</script>' . "\n";
        }

        return $buffer;
    }

}