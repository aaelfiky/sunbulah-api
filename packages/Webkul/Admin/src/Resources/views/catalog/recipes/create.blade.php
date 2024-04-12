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
        .select2-search__field {
            font-family: 'Montserrat,sans-serif' !important;
        }
        .d-none {
            display: none!important;
        }
        .d-flex {
            display: flex!important;
        }
        .d-inline-block {
            display: inline-block!important;
        }
        .d-block {
            display: block!important;
        }
        .image-fit {
            object-fit: cover;
        }
        .image-wrapper .image-item {
            width: 250px;
            height: 250px;
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
            @php
                $locale = core()->checkRequestedLocaleCodeInRequestedChannel();
                $channel = core()->getRequestedChannelCode();
                $channelLocales = core()->getAllLocalesByRequestedChannel()['locales'];
            @endphp
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

                        <div class="control-group" :class="[errors.has('{{$locale}}[author]') ? 'has-error' : '']">
                            <label for="{{$locale}}_author">{{ __('admin::app.catalog.recipes.author') }}
                                <span class="locale">[{{ $locale }}]</span>
                            </label>
                            <input type="text" class="control" id="name" name="{{$locale}}[author]['name']"
                                value="{{ request()->input('author')['name'] ?? '' }}"
                                data-vv-as="&quot;{{ __('admin::app.catalog.recipes.author') }}&quot;" v-slugify-target="'author'"/>
                            <span class="control-error" v-if="errors.has('{{$locale}}[author]')">@{{ errors.first('{!!$locale!!}[author]') }}</span>
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

                        <div class="control-group" :class="[errors.has('products') ? 'has-error' : '']">
                            <label for="products" style="margin-bottom:1rem;">{{ __('admin::app.catalog.recipes.related-products') }}</label>
                            <select class="js-example-basic-multiple control" style="width:300px; margin-top:2rem;" name="products[]" multiple="multiple">
                            </select>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image-desktop') }}</label>
                            <div class="image-wrapper">
                                <label for="10" class="image-item">
                                    <input type="hidden" name="image_desktop[image_0]">
                                    <input type="file" accept="image/*" name="image_desktop[image_0]" id="10" aria-required="false" aria-invalid="false">
                                    <label class="remove-image">Remove Image</label>
                                </label>
                            </div>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image-mobile') }}</label>
                            <div class="image-wrapper">
                                <label for="10" class="image-item">
                                    <input type="hidden" name="image_mobile[image_0]">
                                    <input type="file" accept="image/*" name="image_mobile[image_0]" id="10" aria-required="false" aria-invalid="false">
                                    <label class="remove-image">Remove Image</label>
                                </label>
                            </div>
                        </div>


                        <div class="control-group" :class="[errors.has('video_link') ? 'has-error' : '']">
                            <label for="video_link">{{ __('admin::app.catalog.recipes.video-link') }}</label>
                            <input type="text" class="control" id="video_link" name="video_link" value="{{ request()->input('video_link') ?: old('video_link') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.video-link') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('video_link')">@{{ errors.first('video_link') }}</span>
                        </div>


                        <div class="control-group {!! $errors->has('videos.*') ? 'has-error' : '' !!}">
                            <label>{{ __('admin::app.catalog.products.video') }} (Click to update Video)</label>
                            <div class="image-wrapper" style="display:flex; flex-direction: column;">
                                <label style="cursor: pointer;" class="image-item">
                                    <input type="hidden" name="{{$locale}}[video]"/>
                                    <input type="file"  accept="video/*" name="{{$locale}}[video]" id="recipe-video" ref="videoInput"/>
                                    <video class="preview d-none" width="200" height="160" controls>
                                        <source src="" type="video/mp4">
                                        {{ __('admin::app.catalog.products.not-support-video') }}
                                    </video>
                                </label>
                            </div>
                        </div>

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>

                {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.after') !!}

                <accordian :title="'{{ __('admin::app.catalog.recipes.recipe-card') }}'" :active="true">
                    <div slot="body">
                        <div class="control-group" :class="[errors.has('recipe-card') ? 'has-error' : '']">
                            <label for="recipe_card_decsription">{{ __('admin::app.catalog.recipes.description') }}</label>
                            <textarea class="control" id="recipe_card_decsription" name="{{$locale}}[recipe_card][decsription]">{{ old($locale)['recipe_card']['description'] ?? '' }}</textarea>
                            <span class="control-error" v-if="errors.has('recipe_card')">@{{ errors.first('recipe_card') }}</span>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image') }}</label>
                            <div class="image-wrapper">
                                <label for="10" class="image-item">
                                    <input type="hidden" name="{{$locale}}[recipe_card][image]">
                                    <input type="file" accept="image/*" name="{{$locale}}[recipe_card][image]" id="10" aria-required="false" aria-invalid="false">
                                    <label class="remove-image">Remove Image</label>
                                </label>
                            </div>
                        </div>
                    </div>
                </accordian>

                <accordian :title="'{{ __('admin::app.catalog.recipes.instructions') }}'" :active="true">
                    <div slot="body">

                        <div class="control-group instructions-container" :class="[errors.has('instructions') ? 'has-error' : '']">
                            <div class="instruction-container">
                                <label class="input-label" for="instructions">{{ __('admin::app.catalog.recipes.instruction') }} #1</label>
                                <input type="text" style="margin-bottom: 1rem;" class="control" id="instructions" name="{{$locale}}[instructions][]" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.instructions') }}&quot;"/>
                                <span class="control-error" v-if="errors.has('instructions')">@{{ errors.first('instructions') }}</span>
                                <img class="remove-instruction-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                            </div>
                        </div>
                        <label class="btn btn-lg btn-primary add-instruction" style="display: inline-block; width: auto;">Add Instruction</label>

                    </div>
                </accordian>

                <accordian :title="'{{ __('admin::app.catalog.recipes.ingredients') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}
                        
                        <div class="control-group ingredients-container" :class="[errors.has('ingredients') ? 'has-error' : '']">
                            <div class="ingredient-container">
                                <label class="input-label" for="ingredients">{{ __('admin::app.catalog.recipes.ingredient') }} #1</label>
                                <input type="text" style="margin-bottom: 1rem;" class="control" id="ingredients" name="{{$locale}}[ingredients][]" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.ingredients') }}&quot;"/>
                                <span class="control-error" v-if="errors.has('ingredients')">@{{ errors.first('ingredients') }}</span>
                                <img class="remove-ingredient-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                            </div>
                        </div>

                        <label class="btn btn-lg btn-primary add-ingredient" style="display: inline-block; width: auto;">Add Ingredient</label>
                        <br>
                        <br>

                        <hr>
                        <div class="control-group extra-ingredients-container" :class="[errors.has('extra_ingredients') ? 'has-error' : '']">
                            <div class="extra-ingredient-sub-container new-extra-ingredient-container">
                                <label class="input-label extra-section-label" for="extra_ingredients"><b>Extra {{ __('admin::app.catalog.recipes.ingredient') }} #1</b></label>
                                <br>

                                <label class="input-label" for="extra_title">Title</label>
                                <input type="text" class="control" name="{{$locale}}[extra_ingredients][title]">
                                <br><br>
                                <span class="extra-ingredient-repeated">
                                    <label class="input-label extra-ingredient-label" for="extra_title">Ingredient </label>
                                    <input type="text" style="margin-bottom: 1rem;" class="control extra-ingredient-value" id="ingredient"
                                            name="{{$locale}}[extra_ingredients][ingredient][]"/>
                                    <img class="remove-extra-ingredient-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                                </span>
                            </div>
                            <br>
                            <label class="btn btn-lg btn-primary add-extra-ingredient" style="display: inline-block; width: auto;">Add Ingredient</label>

                        </div>
                        
                        <label class="btn btn-lg btn-primary add-extra-ingredient-component" style="display: inline-block; width: auto;">Add Extra Ingredient Component</label>

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>


                <accordian :title="'{{ __('admin::app.catalog.recipes.tags') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}
                        
                        <div class="control-group tags-container" :class="[errors.has('tags') ? 'has-error' : '']">
                            
                            <div class="tag-container" data-id="0">
                                <label class="input-label" for="tags">{{ __('admin::app.catalog.recipes.tag') }} #1</label>
                                <input type="text" style="margin-bottom: 1rem;" class="control" id="tags" name="new_tags[]" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.tags') }}&quot;"/>
                                <span class="control-error" v-if="errors.has('tags')">@{{ errors.first('tags') }}</span>
                                <img class="remove-tag-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                            </div>
                        
                            <div class="tag-container d-none" data-id="0">
                                <label class="input-label" for="tags">{{ __('admin::app.catalog.recipes.tag') }} #1</label>
                                <input type="text" style="margin-bottom: 1rem;" class="control" id="tags" name="new_tags[]" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.tags') }}&quot;"/>
                                <span class="control-error" v-if="errors.has('tags')">@{{ errors.first('tags') }}</span>
                                <img class="remove-tag-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                            </div>
                            <div class="control-group d-none existing-tags-extra" :class="[errors.has('tags') ? 'has-error' : '']">
                                <label for="tags" style="margin-bottom:1rem;">{{ __('admin::app.catalog.recipes.tags') }}</label>
                                <select class="js-multiple-tag control" style="width:300px; margin-top:2rem;" name="tags[]" multiple="multiple">
                                </select>
                            </div>
                            
                        </div>
                        <label class="btn btn-lg btn-primary add-tag" style="display: inline-block; width: auto;">Add New Tag</label>
                        <label class="btn btn-lg btn-primary select-tag" style="display: inline-block; width: auto;">Select Existing Tag</label>

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>


                <!-- ******************* START TOPIC CONTAINER ******************* -->
                <accordian :title="'{{ __('admin::app.catalog.recipes.topic') }}'" :active="true">
                    <div slot="body">
                        <div class="control-group existing-topic-container" :class="[errors.has('topics') ? 'has-error' : '']">
                            <label for="topic_id" style="margin-bottom:1rem;">{{ __('admin::app.catalog.recipes.select-topic') }}</label>
                            <select class="js-single-topic control" style="width:300px; margin-top:1rem;" name="topic_id">
                            </select>
                        </div>
                        
                        <div class="control-group new-topic-container d-none" data-id="0">
                            <label class="input-label" for="topic">New {{ __('admin::app.catalog.recipes.topic') }}</label>
                            <input type="text" style="margin-bottom: 1rem;" class="control" id="topic" name="new_topic" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.topic') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('topic')">@{{ errors.first('topic') }}</span>
                            <!-- <img class="remove-topic-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" /> -->
                        </div>

                        <label class="btn btn-lg btn-primary add-topic" style="display: inline-block; width: auto;">Add New Topic</label>
                        <label class="btn btn-lg btn-primary select-topic" style="display: inline-block; width: auto;">Select Existing Topic</label>

                    </div>
                </accordian>
                <!-- ******************* END TOPIC CONTAINER ******************* -->


                <!-- ******************* START SEO CONTAINER ******************* -->
                <accordian :title="'{{ __('admin::app.catalog.recipes.seo') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.recipe.create_form_accordian.general.controls.before') !!}

                        <div class="control-group">
                            <label for="{{$locale}}['seo']['title']">{{ __('admin::app.catalog.recipes.meta_title') }}</label>
                            <input type="text" class="control" 
                                id="{{$locale}}[seo][title]"
                                name="{{$locale}}[seo][title]"
                                value="{{ old($locale)['seo']['title'] ?? '' }}"/>
                        </div>

                        <div class="control-group">
                            <label for="{{$locale}}[seo][description]">{{ __('admin::app.catalog.recipes.meta_description') }}</label>
                            <input type="text" class="control"
                                id="{{$locale}}[seo][description]"
                                name="{{$locale}}[seo][description]"
                                value="{{ old($locale)['seo']['description'] ?? '' }}"/>
                        </div>

                        <div class="control-group">
                            <label for="{{$locale}}[seo][keywords]">{{ __('admin::app.catalog.recipes.meta_keywords') }}</label>
                            <input type="text" class="control"
                                id="{{$locale}}[seo][keywords]"
                                name="{{$locale}}[seo][keywords]"
                                value="{{ old($locale)['seo']['keywords'] ?? '' }}"/>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image') }}</label>
                            <image-wrapper input-name="{{$locale}}[seo][image]" :multiple="false"></image-wrapper>
                        </div>

                        {!! view_render_event('bagisto.admin.catalog.recipe.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>
                <!-- ******************* END SEO CONTAINER ******************* -->

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

            $('.js-example-basic-multiple').select2({
                placeholder: 'Select related products',
                allowClear: true,
                tags: true,
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
                                return {id: d.id, text:  `${d.category ?? ''} - ${d.name}`}
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