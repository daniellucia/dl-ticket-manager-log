<?php

use DL\TicketManager\Order\Ticket;

class TMLogManagementPlugin
{

    public function init(): void
    {
        add_action('init', [$this, 'registerCpt']);
        add_action('admin_menu', [$this, 'addSubmenu']);
        add_action('pre_get_posts', [$this, 'restrictCptToAdmins']);
        add_action('admin_head', [$this, 'hideAddNewButton']);
        add_filter('manage_dl-tickets-log_posts_columns', [$this, 'addCustomColumns']);
        add_action('manage_dl-tickets-log_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
        add_action('admin_head', [$this, 'disableEditForLogs']);
        add_action('pre_get_posts', [$this, 'filterSearchQuery']); // <-- Añade este hook

        // Acciones
        add_action('dl_ticket_manager_ticket_created', [$this, 'logTicketCreated'], 10, 2);
        add_action('dl_ticket_manager_ticket_status_changed', [$this, 'logTicketStatusChanged'], 10, 2);
        add_action('dl_validation_event', [$this, 'validationEvent'], 10, 2);
    }

    /**
     * Registramos el CPT para la gestión de logging de los tickets
     * @return void
     * @author Daniel Lucia
     */
    public function registerCpt(): void
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
            </style>';
        }
    }

    /**
     * Añade columnas personalizadas al listado del CPT
     * @param mixed $columns
     * @author Daniel Lucia
     */
    public function addCustomColumns($columns)
    {
        // Guardamos las columnas personalizadas
        $custom = [
            'ticket_id'   => __('Ticket ID', 'dl-ticket-manager-log'),
            'ticket_code' => __('Ticket Code', 'dl-ticket-manager-log'),
            'ticket_name' => __('Customer name', 'dl-ticket-manager-log'),
            'ticket_event' => __('Event', 'dl-ticket-manager-log'),
            'log_type'    => __('Log Type', 'dl-ticket-manager-log'),
        ];

        // Guarda y elimina la columna 'date' si existe
        if (isset($columns['date'])) {
            $date = $columns['date'];
            unset($columns['date']);
        }

        // Elimina las columnas personalizadas si ya existen
        foreach (array_keys($custom) as $key) {
            unset($columns[$key]);
        }

        // Añade las columnas personalizadas
        $columns = array_merge($columns, $custom);

        // Añade la columna 'date' al final
        if (isset($date)) {
            $columns['date'] = $date;
        }

        return $columns;
    }

    /**
     * Renderiza el contenido de las columnas personalizadas
     * @param mixed $column
     * @param mixed $post_id
     * @return void
     * @author Daniel Lucia
     */
    public function renderCustomColumns($column, $post_id)
    {
        switch ($column) {
            case 'ticket_id':
                echo esc_html(get_post_meta($post_id, 'ticket_id', true));
                break;
            case 'ticket_code':
                echo esc_html(get_post_meta($post_id, 'ticket_code', true));
                break;
            case 'ticket_name':
                echo esc_html(get_post_meta($post_id, 'ticket_name', true));
                break;
            case 'ticket_event':
                echo esc_html(get_post_meta($post_id, 'ticket_event', true));
                break;
            case 'log_type':

                $type = esc_html(get_post_meta($post_id, 'log_type', true));
                switch ($type) {
                    case 'info':
                        $color = '#2c76e6ff';
                        $bg    = 'rgba(26, 28, 153, 0.15)';
                        break;
                    case 'success':
                        $color = '#27ae60';
                        $bg    = 'rgba(39,174,96,0.15)';
                        break;
                    case 'warning':
                        $color = '#f1c40f';
                        $bg    = 'rgba(241,196,15,0.15)';
                        break;
                    case 'error':
                        $color = '#e74c3c';
                        $bg    = 'rgba(231,76,60,0.15)';
                        break;
                    default:
                        $color = '#cececeff';
                        $bg    = 'rgba(104, 104, 104, 0.15)';
                        break;
                }

                echo '<span style="font-size: 12px;color:' . esc_attr($color) . ';background:' . esc_attr($bg) . ';border:1px solid ' . esc_attr($color) . ';padding:1px 8px;border-radius:4px;font-weight:normal;display:inline-block;font-family: monospace;"">' . $type . '</span>';
                break;
        }
    }

    /**
     * Obtiene un ticket por su ID
     * @param mixed $ticket_id
     * @return array{code: string, event: string, id: mixed, name: string}
     * @author Daniel Lucia
     */
    private function getTicketById($ticket_id)
    {

        $ticket = [
            'id'    => $ticket_id,
            'code'  => '',
            'name'  => '',
            'event' => '',
        ];

        if ((int)$ticket_id > 0) {
            $ticket = [
                'id'    => $ticket_id,
                'code'  => get_post_meta($ticket_id, 'code', true),
                'name'  => get_post_meta($ticket_id, 'name', true),
                'event' => get_post_meta($ticket_id, 'event', true),
            ];
        }

        return $ticket;
    }

    /**
     * Inserta un nuevo log para un ticket.
     * @param int $ticket_id
     * @param string $ticket_code
     * @param string $text
     * @param string $type (info, success, warning, error)
     * @return int|WP_Error
     */
    private function insertLog($ticket_id, $customer_name, $event,  $ticket_code, $text, $type = 'info')
    {
        $log = [
            'post_type'    => 'dl-tickets-log',
            'post_title'   => $text,
            'post_status'  => 'publish',
            'meta_input'   => [
                'ticket_code'   => $ticket_code,
                'ticket_name'   => $customer_name,
                'ticket_event'   => $event,
                'ticket_id' => $ticket_id,
                'log_type'    => $type,
            ],
        ];

        return wp_insert_post($log);
    }

    /**
     * Inserta un log cuando se crea un ticket
     * @param int $ticket_id
     * @param string $ticket_code
     */
    public function logTicketCreated($ticket_id, $ticket)
    {
        $this->insertLog(
            $ticket_id,
            $ticket['name'],
            $ticket['event'],
            $ticket['code'],
            __('Ticket successfully created.', 'dl-ticket-manager-log'),
            'success'
        );
    }

    /**
     * Registra un cambio de estado en un ticket
     * @param mixed $ticket_id
     * @param mixed $new_status
     * @return void
     * @author Daniel Lucia
     */
    public function logTicketStatusChanged($ticket_id, $new_status)
    {
        $ticket = $this->getTicketById($ticket_id);

        $this->insertLog(
            $ticket_id,
            $ticket['name'],
            $ticket['event'],
            $ticket['code'],
            sprintf(__('Ticket status changed to %s', 'dl-ticket-manager-log'), $new_status),
            'success'
        );
    }

    public function validationEvent($type, $data, $order_id, $ticket_data, $response) {

        //Si el ticket es confirmado, lanzamos el evento para cambiar el estado
        if ($type == 'ticket_confirmed') {
            return $this->logTicketStatusChanged($ticket_data['id'], 'confirmed');
        }

        $ticket = $this->getTicketById((int)$ticket_data['id']);
        $text = $response['message'] ?? '';
        
        $this->insertLog(
            (int)$ticket_data['id'],
            $ticket['name'],
            $ticket['event'],
            $ticket['code'],
            $text,
            'error'
        );

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
