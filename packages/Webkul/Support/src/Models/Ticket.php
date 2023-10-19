<?php

namespace Webkul\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\User\Models\Admin;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'customer_id',
        'owner_id',
        'order_id',
        'status'
    ];

    /**
     * Get the customer who opened the ticket.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the customer support assigned to resolve the ticket.
     */
    public function owner()
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    /**
     * Get the customer support assigned to resolve the ticket.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }


    /**
     * Customer's relation with wishlist items
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id');
    }
}
