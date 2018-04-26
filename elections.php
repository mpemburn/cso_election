<?php

include 'admin_settings.php';
include 'elections_post_type.php';

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
    protected $data = [];
    /** @var \ElectionsPosts $electionsPosts */
    protected $electionsPosts;

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
        // Register elections post type
        $instance->electionsPosts = new ElectionsPosts();

        // Set up AJAX handlers
        add_action('wp_ajax_cso_elections', [$instance, 'registerVote']);
        add_action('wp_ajax_nopriv_cso_elections', [$instance, 'registerVote']);
    }

    public function registerShortcodes()
    {
        add_shortcode('cso_elections', array($this, 'electionShortcodeHandler'));
    }

    public function electionShortcodeHandler($att, $content)
    {
        global $post;

        $html = '';

        if (isset($att['start'])) {
            $html = $this->buildFormHead($_GET, $post->ID);
        }

        if (isset($att['date'])) {
            $this->setElectionMeta($att['date'], $post->ID);
        }

        if (isset($att['office'])) {
            $office = $att['office'];
            $officeKey = strtolower(str_replace(' ', '_', $office));
            $html = $this->buildRace($office);

            $this->officeCount++;
        }

        if (isset($att['candidates'])) {
            $choices = $this->buildCandidates($att['candidates'], $officeKey, $post->ID);
            $html = str_replace('~~~', $choices, $html);
        }

        if (isset($att['end'])) {
            $html = $this->buildFormTail();
        }

        return $html;
    }

    protected function setElectionMeta($date, $postId)
    {

    }

    protected function buildRace($office)
    {
        $html = '<div class="cso-election" data-office="' . $office . '">';
        $html .= '<h4>' . $office . '</h4>';
        $html .= '~~~';
        $html .= '</div>';

        return $html;
    }

    protected function buildCandidates($candidatesAttribute, $officeKey, $postId)
    {
        $candidates = explode(',', $candidatesAttribute);
        $choices = '';
        foreach ($candidates as $candidate) {
            $parts = explode(':', $candidate);
            $value = trim($parts[0]);
            $name = trim($parts[1]);
            $choice = '<label>';
            $choice .= '<input type="radio" name="' . $officeKey . '" value="' . $value . '" class="required"/>' . $name;
            $choice .= '</label>';
            $choices .= $choice;
            $this->data[$officeKey][$value] = $name;
            // Add the data to the post meta
            update_post_meta($postId, 'elections', $this->data);
        }

        return $choices;
    }

    protected function buildFormHead($request, $postId)
    {
        $hash = (isset($request['x'])) ? $request['x'] : null;

        $html = '<div id="">';
        $html .= '<form id="cso_election">';
        $html .= '<input type="hidden" id="post_id" name="post_id" value="' . $postId . '">';
        $html .= '<input type="hidden" id="hash" name="hash" value="' . $hash . '">';

        return $html;
    }

    protected function buildFormTail()
    {
        $html = '    </form>';
        $html .= '    <div>';
        $html .= '        <button class="ui-button" id="vote_button" name="vote_button" disabled>Vote</button>';
        $html .= '        <div id="verify_message" style="display: none;">Please verify your choices before clicking "Vote".</div>';
        $html .= '    </div>';
        $html .= '</div>';

        return $html;
    }

    public function registerVote()
    {
        $success = true;
        $data = $_POST['data'];
        parse_str($data, $voteData);

        $postId = $voteData['post_id'];
        $hash = $voteData['hash'];
        $electionData = get_post_meta($postId, 'elections');
        unset($voteData['post_id']);
        unset($voteData['hash']);

        $this->electionsPosts->recordVote($voteData, array_pop($electionData), $hash);

        wp_send_json_success(['success' => $success, 'post' => $electionData]);
    }

    protected function recordVote($voteData, $electionData)
    {
        foreach ($voteData as $office => $vote) {
            $race = $electionData[$office];
            $candidate = $race[$vote];
            $post_id = wp_insert_post(array (
                'post_type' => 'elections',
                'post_title' => $office . ';' . time(),
                'post_content' => $candidate,
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ));
        }
    }

    protected function loadSettings()
    {

    }

    protected function enqueueAssets()
    {
        $version = '1.6';
        wp_enqueue_style( 'jquery-ui'. 'http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css' );
        wp_enqueue_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        wp_enqueue_style('cso_election', plugin_dir_url(__FILE__) . 'css/elections.css', '', $version);

        wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');
        wp_register_script('validate', plugin_dir_url(__FILE__) . 'js/validate.js', '', $version, true);
        wp_enqueue_script('validate');
        wp_register_script('cso_election', plugin_dir_url(__FILE__) . 'js/elections.js', '', $version, true);
        wp_enqueue_script('cso_election');

        wp_register_script('election-ajax-js', null);
        wp_localize_script('election-ajax-js', 'electionNamespace', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_script('election-ajax-js');
    }


}

// Load as singleton to add actions and enqueue assets
CsoElections::register();