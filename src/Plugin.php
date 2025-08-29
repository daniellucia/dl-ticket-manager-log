<?php

class TMLogManagementPlugin
{

    public function init(): void
    {
        $cpt = new TMLogCpt();
        add_action('init', [$cpt, 'register']);

        add_action('admin_menu', [$this, 'addSubmenu']);
        add_action('pre_get_posts', [$this, 'restrictCptToAdmins']);
        add_action('admin_head', [$this, 'hideAddNewButton']);

        add_action('admin_head', [$this, 'disableEditForLogs']);
        add_action('pre_get_posts', [$this, 'filterSearchQuery']);

        //Columnas
        $columns = new TMLogColumns();
        add_filter('manage_dl-tickets-log_posts_columns', [$columns, 'addCustomColumns']);
        add_action('manage_dl-tickets-log_posts_custom_column', [$columns, 'renderCustomColumns'], 10, 2);
        add_action('admin_head', [$columns, 'customColumnStyles']);

        //Columna en la lista de tickets
        add_filter('manage_dl-ticket_posts_columns', [$columns, 'addCustomColumnsTickets']);
        add_action('manage_dl-ticket_posts_custom_column', [$columns, 'renderCustomColumnsTickets'], 10, 2);

        // Acciones
        $hooks = new TMLogHooks();
        add_action('dl_ticket_manager_ticket_created', [$hooks, 'ticketCreated'], 10, 2);
        add_action('dl_ticket_manager_ticket_status_changed', [$hooks, 'ticketStatusChanged'], 10, 2);
        add_action('dl_validation_event', [$hooks, 'validationEvent'], 10, 2);
    }


    /**
     * Registramos el submenu para que cuelgue del menú de tickets
     * @return void
     * @author Daniel Lucia
     */
    public function addSubmenu(): void
    {
        add_submenu_page(
            'edit.php?post_type=dl-ticket',
            __('Ticket Logs', 'dl-ticket-manager-log'),
            __('Ticket Logs', 'dl-ticket-manager-log'),
            'manage_options',
            'edit.php?post_type=dl-tickets-log'
        );
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
     * Ocultamos el botón de añadir nuevo
     * @return void
     * @author Daniel Lucia
     */
    public function hideAddNewButton()
    {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'dl-tickets-log') {
            echo '<style>
                .page-title-action, #favorite-actions { display: none !important; }
                .tablenav.top, .subsubsub { display: none !important; }
            </style>';
        }
    }


    /**
     * Deshabilita la edición de los posts del CPT dl-tickets-log
     * @return void
     * @author Daniel Lucia
     */
    public function disableEditForLogs()
    {
        global $pagenow;
        $screen = get_current_screen();

        if ($screen && $screen->post_type === 'dl-tickets-log') {
            // Redirige si intenta acceder a la edición
            if ($pagenow === 'post.php' && isset($_GET['action']) && $_GET['action'] === 'edit') {
                wp_redirect(admin_url('edit.php?post_type=dl-tickets-log'));
                exit;
            }
            // Oculta el enlace de edición en el listado
            add_filter('post_row_actions', function ($actions, $post) {
                if ($post->post_type === 'dl-tickets-log') {
                    unset($actions['edit']);
                    unset($actions['inline hide-if-no-js']);
                }
                return $actions;
            }, 10, 2);
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
