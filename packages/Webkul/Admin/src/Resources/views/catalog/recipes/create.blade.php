@extends('admin::layouts.content')

@section('page_title')
    {{ __('admin::app.catalog.recipes.add-title') }}
@stop

@section('css')
    <style>
        .table td .label {
            margin-right: 10px;
        }
        .table td .label:last-child {
            margin-right: 0;
        }
        .table td .label .icon {
            vertical-align: middle;
            cursor: pointer;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('content')
    <div class="content">
        <form method="POST" action="" @submit.prevent="onSubmit" enctype="multipart/form-data">

            <div class="page-header">
                <div class="page-title">
                    <h1>
                        <i class="icon angle-left-icon back-link" onclick="window.location = '{{ route('admin.catalog.recipes.index') }}'"></i>

                        {{ __('admin::app.catalog.recipes.add-title') }}
                    </h1>
                </div>

                <div class="page-action">
                    <button type="submit" class="btn btn-lg btn-primary">
                        {{ __('admin::app.catalog.recipes.save-btn-title') }}
                    </button>
                </div>
            </div>

            <div class="page-content">
                @csrf()

                <?php $familyId = request()->input('family') ?>

                {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.before') !!}

                <accordian :title="'{{ __('admin::app.catalog.products.general') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}

                        <div class="control-group" :class="[errors.has('name') ? 'has-error' : '']">
                            <label for="name" class="required">{{ __('admin::app.catalog.recipes.name') }}</label>
                            <input type="text" v-validate="" class="control" id="name" name="name" value="{{ request()->input('name') ?: old('name') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.name') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('name')">@{{ errors.first('name') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('slug') ? 'has-error' : '']">
                            <label for="slug" class="required">{{ __('admin::app.catalog.recipes.slug') }}</label>
                            <input type="text" class="control" id="slug" name="slug" value="{{ request()->input('slug') ?: old('slug') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.slug') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('slug')">@{{ errors.first('slug') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('preparation_time') ? 'has-error' : '']">
                            <label for="preparation_time">{{ __('admin::app.catalog.recipes.preparation-time') }}</label>
                            <input type="number" class="control" id="preparation_time" name="preparation_time" value="{{ request()->input('preparation_time') ?: old('preparation_time') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.preparation-time') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('preparation_time')">@{{ errors.first('preparation_time') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('serves') ? 'has-error' : '']">
                            <label for="serves">{{ __('admin::app.catalog.recipes.serves') }}</label>
                            <input type="number" class="control" id="serves" name="serves" value="{{ request()->input('serves') ?: old('serves') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.serves') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('serves')">@{{ errors.first('serves') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('cooking-time') ? 'has-error' : '']">
                            <label for="cooking_time">{{ __('admin::app.catalog.recipes.cooking-time') }}</label>
                            <input type="number" class="control" id="cooking_time" name="cooking_time" value="{{ request()->input('cooking_time') ?: old('cooking_time') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.cooking-time') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('cooking_time')">@{{ errors.first('cooking_time') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('main_product_id') ? 'has-error' : '']">
                            <label for="main_product_id">{{ __('admin::app.catalog.recipes.main-product') }}</label>
                            <select class="js-example-basic-single" style="width:300px" name="main_product_id">
                            </select>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image-desktop') }}</label>
                            <div class="image-wrapper">
                                <label for="10" class="image-item">
                                    <!-- <input type="hidden" name="image_desktop[image_0]"> -->
                                    <input type="file" accept="image/*" name="image_desktop[image_0]" id="10" aria-required="false" aria-invalid="false">
                                    <label class="remove-image">Remove Image</label>
                                </label>
                            </div>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image-mobile') }}</label>
                            <image-wrapper input-name="image_mobile" :multiple="false"></image-wrapper>
                        </div>



                        <div class="control-group" :class="[errors.has('video_link') ? 'has-error' : '']">
                            <label for="video_link">{{ __('admin::app.catalog.recipes.video-link') }}</label>
                            <input type="text" class="control" id="video_link" name="video_link" value="{{ request()->input('video_link') ?: old('video_link') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.video-link') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('video_link')">@{{ errors.first('video_link') }}</span>
                        </div>


                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>

                {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.after') !!}


                <accordian :title="'{{ __('admin::app.catalog.recipes.instructions') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}

                        <!-- <div class="control-group" :class="[errors.has('name') ? 'has-error' : '']">
                            <label for="name">{{ __('admin::app.catalog.recipes.name') }}</label>
                            <input type="text" class="control" id="name" name="name" value="{{ request()->input('name') ?: old('name') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.name') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('name')">@{{ errors.first('name') }}</span>
                        </div> -->
                        <instructions></instructions>

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>


                <accordian :title="'{{ __('admin::app.catalog.recipes.seo') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}

                        <div class="control-group" :class="[errors.has('seo_title') ? 'has-error' : '']">
                            <label for="seo_title">{{ __('admin::app.catalog.recipes.meta_title') }}</label>
                            <input type="text" class="control" id="seo_title" name="seo_title" value="{{ request()->input('seo_title') ?: old('seo_title') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.meta_title') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('seo_title')">@{{ errors.first('seo_title') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('seo_desc') ? 'has-error' : '']">
                            <label for="seo_desc">{{ __('admin::app.catalog.recipes.meta_description') }}</label>
                            <input type="text" class="control" id="seo_desc" name="seo_desc" value="{{ request()->input('seo_desc') ?: old('seo_desc') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.meta_description') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('seo_desc')">@{{ errors.first('seo_desc') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('seo_keywords') ? 'has-error' : '']">
                            <label for="seo_keywords">{{ __('admin::app.catalog.recipes.meta_keywords') }}</label>
                            <input type="text" class="control" id="seo_keywords" name="seo_keywords" value="{{ request()->input('seo_keywords') ?: old('seo_keywords') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.meta_keywords') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('seo_keywords')">@{{ errors.first('seo_keywords') }}</span>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image') }}</label>
                            <image-wrapper input-name="seo_image" :multiple="false"></image-wrapper>
                        </div>

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>

            </div>

        </form>
    </div>
@stop

@push('scripts')
    @include('admin::layouts.tinymce')
    <script>
        $(document).ready(function () {
            $('.label .cross-icon').on('click', function(e) {
                $(e.target).parent().remove();
            })

            $('.actions .trash-icon').on('click', function(e) {
                $(e.target).parents('tr').remove();
            });

            var base_url = window.location.origin;
            $('.js-example-basic-single').select2({
                placeholder: 'Select a product',
                ajax: {
                    url: `${base_url}/api/products`,
                    delay: 250,
                    data: function (params) {
                        var query = {
                            name: params.term,
                            limit: 10
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    },
                    processResults: function (data) {
                        // Transforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: data.data.map(d => {
                                return {id: d.id, text: d.name}
                            })
                        };
                    }
                }
            });
        });
    
    </script>

    <script type="text/x-template" id="instructions-template">
        <div class="control-group" :class="[errors.has('instructions') ? 'has-error' : '']">
            <label for="instructions" :class="isRequired ? 'required' : ''">{{ __('admin::app.catalog.recipes.instructions') }}</label>
            <textarea v-validate="isRequired ? 'required' : ''"  class="control" id="instructions" name="instructions" data-vv-as="&quot;{{ __('admin::app.catalog.categories.instructions') }}&quot;">{{ old('instructions') }}</textarea>
            <span class="control-error" v-if="errors.has('instructions')">@{{ errors.first('instructions') }}</span>
        </div>
    </script>

    <script>
        Vue.component('instructions', {
            template: '#instructions-template',

            inject: ['$validator'],

            data: function() {
                return {
                    isRequired: true,
                }
            },

            created: function () {
                let self = this;

                $(document).ready(function () {

                    tinyMCEHelper.initTinyMCE({
                        selector: 'textarea#instructions',
                        height: 200,
                        width: "100%",
                        plugins: 'image imagetools media wordcount save fullscreen code table lists link hr',
                        toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor link hr | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent  | removeformat | code | table',
                        uploadRoute: '{{ route('admin.tinymce.upload') }}',
                        csrfToken: '{{ csrf_token() }}',
                    });
                });
            },
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush