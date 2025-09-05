<?php

namespace Dl\TicketsLog;

defined('ABSPATH') || exit;

class Columns
{

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
            'user_ip'     => __('User IP', 'dl-ticket-manager-log'),
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
            case 'user_ip':
                echo '<pre style="margin: 0; font-size: 12px;">';
                echo esc_html(get_post_meta($post_id, 'user_ip', true));
                echo '</pre>';
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
     * Añadimos columnas personalizadas a la lista de tickets
     * @param mixed $columns
     * @author Daniel Lucia
     */
    public function addCustomColumnsTickets($columns)
    {
        $columns['log'] = __('Log', 'dl-ticket-manager');

        return $columns;
    }

    /**
     * Renderizamos las columnas personalizadas en la lista de tickets
     * @param mixed $column
     * @param mixed $post_id
     * @return void
     * @author Daniel Lucia
     */
    function renderCustomColumnsTickets($column, $post_id)
    {
        switch ($column) {
            case 'log':
                $url = $this->getLogSearchUrl(get_post_meta($post_id, 'code', true));
                echo '<a href="' . esc_url($url) . '">' . __('View log', 'dl-ticket-manager-log') . '</a>';
                break;
        }
    }

    /**
     * Añade estilos CSS para cambiar el ancho de las columnas
     * @return void
     * @author Daniel Lucia
     */
    public function customColumnStyles()
    {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'dl-tickets-log') {
            echo '<style>
                .wp-list-table th.column-ticket_id { width: 5%; }
                .wp-list-table th.column-ticket_code { width: 8%; }
                .wp-list-table th.column-ticket_name { width: 12%; }
                .wp-list-table th.column-ticket_event { width: 14%; }
                .wp-list-table th.column-user_ip { width: 4%; }
                .wp-list-table th.column-log_type { width: 4%; }
                .wp-list-table th.column-date { width: 10%; }
            </style>';
        }

        if ($screen && $screen->post_type === 'dl-ticket') {
            echo '<style>
                .wp-list-table th.column-log { width: 80px; }
            </style>';
        }
    }


    /**
     * Obtiene la URL de búsqueda de logs para un ticket específico
     * @param mixed $ticket_code
     * @return string
     * @author Daniel Lucia
     */
    private function getLogSearchUrl($ticket_code)
    {
        $base_url = admin_url('edit.php');
        $args = [
            's'           => $ticket_code,
            'post_status' => 'all',
            'post_type'   => 'dl-tickets-log',
            'action'      => '-1',
            'm'           => '0',
            'paged'       => '1',
            'action2'     => '-1',
        ];
        return add_query_arg($args, $base_url);
    }
}
