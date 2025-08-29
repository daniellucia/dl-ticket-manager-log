<?php

defined('ABSPATH') || exit;

class TMLogHooks
{

    /**
     * Inserta un log cuando se crea un ticket
     * @param int $ticket_id
     * @param string $ticket_code
     */
    public function ticketCreated($ticket_id, $ticket)
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
    public function ticketStatusChanged($ticket_id, $new_status)
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

    /**
     * Maneja los eventos de validación de tickets
     * @param mixed $type
     * @param mixed $data
     * @param mixed $order_id
     * @param mixed $ticket_data
     * @param mixed $response
     * @author Daniel Lucia
     */
    public function validationEvent($type, $data, $order_id, $ticket_data, $response)
    {

        //Si el ticket es confirmado, lanzamos el evento para cambiar el estado
        if ($type == 'ticket_confirmed') {
            return $this->ticketStatusChanged($ticket_data['id'], 'confirmed');
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

        $user_ip = $_SERVER['REMOTE_ADDR'];

        $log = [
            'post_type'    => 'dl-tickets-log',
            'post_title'   => $text,
            'post_status'  => 'publish',
            'meta_input'   => [
                'ticket_code'   => $ticket_code,
                'ticket_name'   => $customer_name,
                'ticket_event'   => $event,
                'user_ip'   => $user_ip,
                'ticket_id' => $ticket_id,
                'log_type'    => $type,
            ],
        ];

        return wp_insert_post($log);
    }
}
