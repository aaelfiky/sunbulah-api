@extends('admin::layouts.content')

@section('page_title')
    {{ __('admin::app.catalog.recipes.title') }}
@stop

@section('content')

    <div class="content" style="height: 100%;">
        <div class="page-header">
            <div class="page-title">
                <h1>{{ __('admin::app.catalog.recipes.title') }}</h1>
            </div>

            <div class="page-action">
                <div class="export-import" @click="showModal('downloadDataGrid')">
                    <i class="export-icon"></i>
                    <span >
                        {{ __('admin::app.export.export') }}
                    </span>
                </div>

                <a href="{{ route('admin.catalog.recipes.create') }}" class="btn btn-lg btn-primary">
                    {{ __('admin::app.catalog.recipes.add-recipe-btn-title') }}
                </a>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.catalog.recipes.list.before') !!}

        <div class="page-content">
            @inject('recipes', 'Webkul\Admin\DataGrids\RecipeDataGrid')

            {!! $recipes->render() !!}
        </div>

        <!-- {!! view_render_event('bagisto.admin.catalog.recipes.list.after') !!} -->
    </div>

    <modal id="downloadDataGrid" :is-open="modalIds.downloadDataGrid">
        <h3 slot="header">{{ __('admin::app.export.download') }}</h3>
        <div slot="body">
            <export-form></export-form>
        </div>
    </modal>

@stop

@push('scripts')
<script>
    $(document).ready(function () {
        
    });
    
</script>
@endpush