@extends('admin::layouts.master')

@section('page_title')
    Support
@stop

@section('content-wrapper')

    <div class="content full-page dashboard">
        <div class="page-header">
            <div class="page-title">
                <h1>Customer Support</h1>
            </div>

            <div class="page-action">
            </div>
        </div>

        <div class="page-content">
            @inject('tickets','Webkul\Admin\DataGrids\TicketDataGrid')
            {!! $tickets->render() !!}
        </div>
    </div>

@stop