<?php

namespace Webkul\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;

class TicketMessage extends Model
{
    protected $table = 'ticket_messages';

    protected $fillable = [
        'customer_id',
        'owner_id',
        'ticket_id',
        'message'
    ];

    /**
     * Get the customer who opened the ticket.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::modelClass(), 'customer_id');
    }

    /**
     * Get the customer support assigned to resolve the ticket.
     */
    public function owner()
    {
        return $this->belongsTo(Admin::modelClass(), 'owner_id');
    }

    /**
     * Original Ticket
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::modelClass(), 'ticket_id');
    }
}
