<?php

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
}
