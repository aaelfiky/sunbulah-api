@extends('admin::layouts.content')

@section('page_title')
    Edit Ticket
@stop

@section('css')
<style>
        .ticket-discussion-container {
            display: flex;
            flex-direction: column;
            gap: 3rem;
            margin-bottom: 10rem;
            max-height: 20rem;
            overflow-y: scroll;
        }
        .ticket-message-customer {
            margin-left: auto;
            background-color: #d3d3d35e;
        }

        .ticket-message-owner {
            margin-right: auto;
            background-color: #90ee9040;
        }

        .ticket-message-customer, .ticket-message-owner {
            position: relative;
            padding: 1rem 2rem;
            border-radius: 10px;
        }

        .ticket-message-owner > span, .ticket-message-customer > span {
            position: absolute;
            bottom: -1.8rem;
            display: inline-block;
            right: 1px;
            width: 110px;
            text-align: right;
            font-size: 10px;
            color: darkgrey;
        }

    </style>
@stop

@section('content')
    
    <div class="content">

    {{--<!-- action="{{ route('admin.roles.update', $role->id) }}" --> --}}
        <form method="POST" action="{{ route('admin.support.update', $ticket->id) }}" @submit.prevent="onSubmit">
            <div class="page-header">
                <div class="page-title">
                    <h1>
                        <i class="icon angle-left-icon back-link" onclick="window.location = '{{ route('admin.support.index') }}'"></i>

                        Edit Ticket | #{{$ticket->id}}
                    </h1>
                </div>

                <div class="page-action">
                    <button type="submit" class="btn btn-lg btn-primary">
                        Update Ticket
                    </button>
                </div>
            </div>

            <div class="page-content">
                <div class="form-container">
                    @csrf()

                    <input name="_method" type="hidden" value="PUT">

                    <accordian :title="'{{ __('admin::app.users.roles.general') }}'" :active="true">
                        <div slot="body">
                            <div class="control-group" :class="[errors.has('name') ? 'has-error' : '']">
                                <label for="name" class="required">Status</label>
                                <select class="control" name="status" id="status">
                                    <option value="New" {{ $ticket->status == 'New' ? 'selected' : '' }}>
                                        New
                                    </option>
                                    <option value="Active" {{ $ticket->status == 'Active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="Resolved" {{ $ticket->status == 'Resolved' ? 'selected' : '' }}>
                                        Resolved
                                    </option>
                                    <option value="Closed" {{ $ticket->status == 'Closed' ? 'selected' : '' }}>
                                        Closed
                                    </option>
                                    <option value="Rejected" {{ $ticket->status == 'Rejected' ? 'selected' : '' }}>
                                        Rejected
                                    </option>
                                </select>
                                <!-- <span class="control-error" v-if="errors.has('name')">@{{ errors.first('name') }}</span> -->
                            </div>

                            
                        </div>
                    </accordian>

                    <accordian title="Discussion" :active="true">
                        <div slot="body">
                            <div class="ticket-discussion-container">
                                @foreach($ticket->messages as $message)
                                    @if(isset($message->customer_id))
                                        <div class="ticket-message-customer">
                                            {{$message->message}}
                                            <span>{{$message->created_at}}</span>
                                        </div>
                                    @else
                                        <div class="ticket-message-owner">
                                            {{$message->message}}
                                            <span>{{$message->created_at}}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <textarea name="comment" style="width:100%;border: 2px solid #c7c7c7;padding:1rem;border-radius: 3px;" rows="10"> Reply here...</textarea>
                        </div>
                        
                    </accordian>
                </div>
            </div>
        </form>
    </div>
@stop

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#permission_type').on('change', function(e) {
                if ($(e.target).val() == 'custom') {
                    $('.tree-wrapper').removeClass('hide')
                } else {
                    $('.tree-wrapper').addClass('hide')
                }

            })
        });
    </script>
@endpush