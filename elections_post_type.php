<?php

class ElectionsPosts
{
    public function __construct()
    {
        add_action('init', array($this, 'registerPostType'));
    }

    public function recordVote($voteData, $electionData, $electionDate, $hash)
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
            update_post_meta($post_id, 'election_date', $electionDate);
            update_post_meta($post_id, 'hash', $hash);
        }
    }

    public function registerPostType()
    {
        $labels = [
            'name' => _x('Election', 'Post Type General Name', 'twentyseventeen'),
            'singular' => _x('Election', 'Post Type Singular Name', 'twentyseventeen'),
            'plural' => __('Elections', 'twentyseventeen')
        ];
        $postTypeArgs = [
            'label' => __('elections', 'twentyseventeen'),
            'description' => __('Election', 'twentyseventeen'),
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => false,
            'rewrite' => array('slug' => 'election'),
            'capability_type' => 'post',
        ];

        register_post_type('elections', $postTypeArgs);
    }

    public function listVotes()
    {

    }

    public function testIfAlreadyVoted($electionDate, $hash)
    {
        $vote = new WP_Query(array(
            'post_type' => 'elections',
            'meta_query'    => array(
                array(
                    'key' => 'election_date',
                    'value' => $electionDate,
                    'compare' => '='
                ),
                array(
                    'key' => 'hash',
                    'value' => $hash,
                    'compare' => '='
                ),
            )
        ));

        $hasVoted = $vote->have_posts();

        return $hasVoted;
    }

}
