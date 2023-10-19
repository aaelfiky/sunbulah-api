<?php

namespace Webkul\Support\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Webkul\Support\Models\Ticket;
use Webkul\Support\Models\TicketMessage;

class SupportController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
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
        $this->middleware('admin');

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view($this->_config['view']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $ticket = Ticket::with('messages')->findOrFail($id);
        return view($this->_config['view'], compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update($id, Request $request)
    {
        $ticket = Ticket::with('messages')->findOrFail($id);
        if ($ticket) {
            $ticket->update(['status' => $request->status]);
            if (isset($request->comment)) {
                $comment = new TicketMessage([
                    'message' => $request->comment,
                    'owner_id' => auth()->guard('admin')->user()->id
                ]);
                $ticket->messages()->save($comment);
            }
        }
        return view($this->_config['view'], compact('ticket'));   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
    }
}
