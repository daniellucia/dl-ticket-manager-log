<?php

namespace Dl\TicketsLog;

defined('ABSPATH') || exit;

class Cpt
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

    /**
     * Restricción del CPT a los administradores
     * @param mixed $query
     * @return void
     * @author Daniel Lucia
     */
    public function restrictCptToAdmins($query)
    {
        if (
            is_admin() &&
            $query->get('post_type') === 'dl-tickets-log' &&
            !current_user_can('manage_options')
        ) {
            $query->set('post_type', 'none');
        }
    }


    /**
     * Permite buscar por ticket_code, ticket_name o ticket_event en el listado
     * @param mixed $query
     * @return void
     * @author Daniel Lucia
     */
    public function filterSearchQuery($query)
    {
        if (
            is_admin() &&
            $query->is_main_query() &&
            $query->get('post_type') === 'dl-tickets-log' &&
            !empty($query->get('s'))
        ) {
            $search = $query->get('s');
            $meta_query = [
                'relation' => 'OR',
                [
                    'key'     => 'ticket_code',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'ticket_name',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'ticket_event',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ],
            ];
            $query->set('meta_query', $meta_query);
            $query->set('s', ''); // Evita que WP busque por título
        }
    }
}
