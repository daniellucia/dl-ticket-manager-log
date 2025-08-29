<?php

class TMLogCpt
{

    /**
     * Registramos el CPT para la gestión de logging de los tickets
     * @return void
     * @author Daniel Lucia
     */
    public function register(): void
    {
        register_post_type('dl-tickets-log', [
            'labels' => [
                'name' => __('Ticket Logs', 'dl-ticket-manager-log'),
                'singular_name' => __('Ticket Log', 'dl-ticket-manager-log'),
                'menu_name' => __('Ticket Logs', 'dl-ticket-manager-log'),
            ],
            'public'             => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'has_archive'        => false,
            'rewrite'            => false,
            'supports'           => ['title', 'editor', 'custom-fields'],
            'capabilities' => [
                'edit_post'          => 'do_not_allow',
                'delete_post'        => 'do_not_allow',
                'create_posts'       => 'do_not_allow',
            ],
            'show_in_rest'       => false
        ]);
    }
}
