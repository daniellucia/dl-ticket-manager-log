<?php

defined('ABSPATH') || exit;

class TmLogConfig
{

    /**
     * Registramos opciones para el log
     * @return void
     * @author Daniel Lucia
     */
    public function registerSettings()
    {
        add_settings_field(
            'dl_ticket_manager_log_retention',
            __('Log retention (months)', 'dl-ticket-manager-log'),
            [$this, 'renderLogRetentionField'],
            'dl-ticket-manager-settings',
            'dl_ticket_manager_section'
        );
        register_setting('dl_ticket_manager_settings', 'dl_ticket_manager_log_retention');
    }

    /**
     * Renderiza el campo de retención de logs
     * @return void
     * @author Daniel Lucia
     */
    public function renderLogRetentionField()
    {
        $value = get_option('dl_ticket_manager_log_retention', 8);
        echo '<input type="number" name="dl_ticket_manager_log_retention" value="' . esc_attr($value) . '" min="1" style="width:80px;" /> ';
        echo __('Number of months to keep logs (default: 8)', 'dl-ticket-manager-log');
    }

    /**
     * Elimina los logs antiguos según la configuración de retención.
     * @return void
     * @author Daniel Lucia
     */
    public function maybeDeleteOldLogs()
    {
        $months = intval(get_option('dl_ticket_manager_log_retention', 8));
        if ($months < 1) {
            $months = 8;
        }

        // Solo ejecuta una vez a la semana
        if (get_transient('dl_ticket_manager_log_cleaned_weekly')) {
            return;
        }

        $date_query = [
            [
                'column' => 'post_date',
                'before' => date('Y-m-d', strtotime("-{$months} months")),
            ]
        ];

        $old_logs = get_posts([
            'post_type'      => 'dl-tickets-log',
            'post_status'    => 'publish',
            'date_query'     => $date_query,
            'fields'         => 'ids',
            'posts_per_page' => -1,
        ]);

        if (!empty($old_logs)) {
            foreach ($old_logs as $log_id) {
                wp_delete_post($log_id, true);
            }
        }

        set_transient('dl_ticket_manager_log_cleaned_weekly', true, WEEK_IN_SECONDS);
    }
}
