<?php

namespace Webkul\Support\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Webkul\Sales\Models\Order;
use Webkul\Support\Models\Ticket;
use Webkul\Support\Models\TicketMessage;

class SupportController extends Controller
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_config = request('_config');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view($this->_config['view']);
    }

    public function getByOrderId($order_id)
    {
        if ($order_id) {
            $order = Order::findOrFail($order_id);
        }

        $tickets = Ticket::with(['messages'])->where('order_id', $order_id)->get();

        return response()->json(['data' => $tickets], 200);
    }

    public function updateByOrderId($order_id, Request $request)
    {
        if ($order_id) {
            $order = Order::findOrFail($order_id);
        }

        $comment = new TicketMessage([
            'message' => $request->comment,
            'customer_id' => auth()->guard('api')->user()->id
        ]);

        $ticket = Ticket::firstWhere('order_id', $order_id);

        if (!$ticket) {
            $ticket = Ticket::create([
                'order_id' => $order_id,
                'customer_id' => auth()->guard('api')->user()->id
            ]);
        }

        $ticket->messages()->save($comment);

        return response()->json(['data' => $ticket], 200);
    }
}
