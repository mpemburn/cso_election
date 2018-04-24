<?php

include 'admin_settings.php';

/*
 * @wordpress-plugin
 * Plugin Name: CSO Elections Plugin
 * Description: Facilitates club election process
 * Version: 1.0 Alpha
 * Author: Mark Pemburn
 * Author URI: http://www.pemburnia.com/
*/

class CsoElections
{
    protected $officeCount = 0;

    public static function register()
    {
        $instance = new self;
        if (is_admin()) {
            new \CsoElectionsSettings();
        } else {
            $instance->loadSettings();
        }
        $instance->enqueueAssets();
        add_action('init', array($instance, 'registerShortcodes'));

        // Set up AJAX handlers
        add_action('wp_ajax_cso_vote', [$instance, 'registerVote']);
        add_action('wp_ajax_nopriv_cso_vote', [$instance, 'registerVote']);
    }

    public function registerShortcodes()
    {
        add_shortcode('cso_elections', array($this, 'electionShortcodeHandler'));
    }

    public function electionShortcodeHandler($att, $content)
    {
        $html = '';

        if (isset($att['start'])) {
            $html = 'Start<br/>';
        }

        if (isset($att['date'])) {

        }

        if (isset($att['office'])) {
            $officer = $att['office'];
            $html = '<div class="cso-election" data-office="' . $att['offic'] . '">';
            $html .= '<h4>' . $att['officer'] . '</h4>';
            $html .= '~~~';
            $html .= '</div>';

            $this->officeCount++;
        }

        if (isset($att['candidates'])) {
            $candidates = explode(',', $att['candidates']);
            $choices = '';
            foreach ($candidates as $candidate) {
                $parts = explode(':', $candidate);
                $value = $parts[0];
                $name = $parts[1];
                $choice = '<label><input type="radio" name="' . strtolower($officer) . '" value="' . $value . '"/>' . $name . '</label>';
                $choices .= $choice;
            }

            $buttonDiv = '<div data-count="' . $this->officeCount . '">';
            $buttonDiv .= '</div>';
            $html .= $buttonDiv;

            $html = str_replace('~~~', $choices, $html);
        }

        if (isset($att['end'])) {
            $html = 'End';
        }

        return $html;
    }

    public function registerVote()
    {

    }

    protected function loadSettings()
    {

    }

    protected function enqueueAssets()
    {

    }


}

// Load as singleton to add actions and enqueue assets
CsoElections::register();
