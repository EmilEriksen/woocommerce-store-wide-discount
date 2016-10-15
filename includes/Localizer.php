<?php
namespace WCSWD;

/**
 * The class responsible to localizing the plugin.
 */
class Localizer {
    /**
     * Plugin textdomain.
     *
     * @var String
     */
    protected $textdomain;

    /**
     * File path to languages dir.
     *
     * @var String
     */
    protected $languages_path;

    /**
     * Constructor.
     *
     * @param String $textdomain     Textdomain.
     * @param String $languages_path File path to languages.
     */
    public function __construct( String $textdomain, String $languages_path ) {
        $this->textdomain = $textdomain;
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            $this->textdomain,
            false,
            $this->languages_path
        );
    }

}
