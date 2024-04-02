@extends('admin::layouts.content')

@section('page_title')
    {{ __('admin::app.catalog.recipes.edit-title') }}
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
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
    </style>
@stop

@section('content')
    <div class="content">
        @php
            $locale = core()->checkRequestedLocaleCodeInRequestedChannel();
            $channel = core()->getRequestedChannelCode();
            $channelLocales = core()->getAllLocalesByRequestedChannel()['locales'];
        @endphp

        {!! view_render_event('bagisto.admin.catalog.recipe.edit.before', ['recipe' => $recipe]) !!}

        <form method="POST" action="" @submit.prevent="onSubmit" enctype="multipart/form-data">

            <div class="page-header">

                <div class="page-title">
                    <h1>
                        <i class="icon angle-left-icon back-link"
                           onclick="window.location = '{{ route('admin.catalog.recipes.index') }}'"></i>

                        {{ __('admin::app.catalog.recipes.edit-title') }}
                    </h1>

                    <div class="control-group">
                        <select class="control" id="locale-switcher" name="locale">
                            @foreach ($channelLocales as $localeModel)

                                <option
                                    value="{{ $localeModel->code }}" {{ ($localeModel->code) == $locale ? 'selected' : '' }}>
                                    {{ $localeModel->name }}
                                </option>

                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="page-action">
                    <button type="submit" class="btn btn-lg btn-primary">
                        {{ __('admin::app.catalog.recipes.save-btn-title') }}
                    </button>
                </div>
            </div>

            <div class="page-content">
                @csrf()

                <input name="_method" type="hidden" value="PUT">

               
                {!! view_render_event(
                  'bagisto.admin.catalog.recipe.edit_form_accordian.additional_views.before',
                   ['recipe' => $recipe])
                !!}

                <accordian :title="'{{ __('admin::app.catalog.recipes.general') }}'" :active="true">
                    <div slot="body">
                        {!! view_render_event('bagisto.admin.catalog.recipe.edit_form_accordian.attributes.controls.before', ['recipe' => $recipe]) !!}

                        <div class="control-group" :class="[errors.has('slug') ? 'has-error' : '']">
                            <label for="slug" class="required">{{ __('admin::app.catalog.recipes.slug') }}</label>
                            <input type="text" v-validate="'required'" class="control" id="slug" name="slug" value="{{ old('slug') ?: $recipe->slug }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.slug') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('slug')" v-text="errors.first('slug')"></span>
                        </div>

                        {{--@foreach ($allLocales as $locale)
                            <div class="control-group">
                                <label for="locale-{{ $locale->code }}">{{ $locale->name . ' (' . $locale->code . ')' }}</label>
                                <input type="text" class="control" id="locale-{{ $locale->code }}" name="<?php echo $locale->code; ?>[name]" value="{{ old($locale->code)['name'] ?? ($recipe->translate($locale->code)->name ?? '') }}"/>
                            </div>
                        @endforeach--}}

                        {!! view_render_event('bagisto.admin.catalog.recipe.edit_form_accordian.attributes.controls.after', ['recipe' => $recipe]) !!}
                    </div>
                </accordian>

                <accordian :title="'{{ __('admin::app.catalog.recipes.display') }}'" :active="true">
                    <div slot="body">

                        <div class="control-group" :class="[errors.has('{{$locale}}[name]') ? 'has-error' : '']">
                            <label for="name" class="required">{{ __('admin::app.catalog.categories.name') }}
                                <span class="locale">[{{ $locale }}]</span>
                            </label>
                            <input type="text" v-validate="'required'" class="control" id="name" name="{{$locale}}[name]" value="{{ old($locale)['name'] ?? ($recipe->translate($locale)['name'] ?? '') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.name') }}&quot;" v-slugify-target="'slug'"/>
                            <span class="control-error" v-if="errors.has('{{$locale}}[name]')">@{{ errors.first('{!!$locale!!}[name]') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('{{$locale}}[author]') ? 'has-error' : '']">
                            <label for="{{$locale}}_author">{{ __('admin::app.catalog.recipes.author') }}
                                <span class="locale">[{{ $locale }}]</span>
                            </label>
                            <input type="text" class="control" id="name" name="{{$locale}}[author]['name']"
                                value="{{ old($locale)['author']['name'] ?? ($recipe->translate($locale)['author']['name'] ?? '') }}"
                                data-vv-as="&quot;{{ __('admin::app.catalog.recipes.author') }}&quot;" v-slugify-target="'author'"/>
                            <span class="control-error" v-if="errors.has('{{$locale}}[author]')">@{{ errors.first('{!!$locale!!}[author]') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('preparation_time') ? 'has-error' : '']">
                            <label for="{{$locale}}[preparation_time]">{{ __('admin::app.catalog.recipes.preparation-time') }}</label>
                            <input type="number" class="control" id="{{$locale}}[preparation_time]" name="{{$locale}}[preparation_time]" value="{{ old($locale)['preparation_time'] ?? ($recipe->translate($locale)['preparation_time'] ?? '') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.preparation-time') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('{{$locale}}[preparation_time]')">@{{ errors.first('preparation_time') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('serves') ? 'has-error' : '']">
                            <label for="{{$locale}}[serves]">{{ __('admin::app.catalog.recipes.serves') }}</label>
                            <input type="number" class="control" id="{{$locale}}[serves]" name="{{$locale}}[serves]" value="{{ old($locale)['serves'] ?? ($recipe->translate($locale)['serves'] ?? '') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.serves') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('serves')">@{{ errors.first('serves') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('cooking-time') ? 'has-error' : '']">
                            <label for="{{$locale}}[cooking_time]">{{ __('admin::app.catalog.recipes.cooking-time') }}</label>
                            <input type="number" class="control" id="{{$locale}}[cooking_time]" name="{{$locale}}[cooking_time]" value="{{ old($locale)['cooking_time'] ?? ($recipe->translate($locale)['cooking_time'] ?? '') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.cooking-time') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('cooking_time')">@{{ errors.first('cooking_time') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('main_product_id') ? 'has-error' : '']">
                            <label for="main_product_id" style="margin-bottom:1rem;">{{ __('admin::app.catalog.recipes.main-product') }}</label>
                            <select class="js-example-basic-single control" style="width:300px; margin-top:1rem;" name="{{$locale}}[main_product_id]">
                            </select>
                        </div>



                        <div class="control-group" :class="[errors.has('products') ? 'has-error' : '']">
                            <label for="products" style="margin-bottom:1rem;">{{ __('admin::app.catalog.recipes.related-products') }}</label>
                            <select class="js-example-basic-multiple control" style="width:300px; margin-top:2rem;" name="products[]" multiple="multiple">
                            </select>
                        </div>



                        {{--@foreach ($allLocales as $locale)
                            <div class="control-group">
                                <label for="locale-{{ $locale->code }}">{{ $locale->name . ' (' . $locale->code . ')' }}</label>
                                <input type="text" class="control" id="locale-{{ $locale->code }}" name="<?php echo $locale->code; ?>[name]" value="{{ old($locale->code)['name'] ?? ($recipe->translate($locale->code)->name ?? '') }}"/>
                            </div>
                        @endforeach--}}

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image') }}</label>
                            <div class="image-wrapper">
                                <label for="10" class="image-item {{isset($recipe->translate($locale)->image_desktop)? 'has-image': ''}}">
                                    <input type="hidden" name="image_desktop[image_0]">
                                    <input type="file" accept="image/*" name="image_desktop[image_0]" id="10" aria-required="false" aria-invalid="false">
                                    @if(isset($recipe->translate($locale)->image_desktop))
                                        <img src="{{ asset('storage/recipe/' . $recipe->id . '_' . $recipe->id . '/' .$recipe->translate($locale)->image_desktop) }}" class="preview">
                                    @endif
                                    <label class="remove-image">Remove Image</label>
                                </label>
                            </div>
                        </div>

                    </div>
                </accordian>

                <accordian :title="'{{ __('admin::app.catalog.recipes.recipe-card') }}'" :active="true">
                    <div slot="body">
                        <div class="control-group" :class="[errors.has('recipe-card') ? 'has-error' : '']">
                            <label for="recipe_card_decsription">{{ __('admin::app.catalog.recipes.description') }}</label>
                            <textarea class="control" id="recipe_card_decsription" name="{{$locale}}[recipe_card][decsription]">{{ old($locale)['recipe_card']['description'] ?? ($recipe->translate($locale)['recipe_card']['description'] ?? '') }}</textarea>
                            <span class="control-error" v-if="errors.has('recipe_card')">@{{ errors.first('recipe_card') }}</span>
                        </div>
                    </div>
                </accordian>

                <accordian :title="'{{ __('admin::app.catalog.recipes.instructions') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}
                        
                        <div class="control-group instructions-container" :class="[errors.has('instructions') ? 'has-error' : '']">
                            @php
                                $translated_instructions = $recipe->translate($locale)["instructions"];
                            @endphp
                            @foreach($translated_instructions as $instruction)
                                <div class="instruction-container" data-id="{{ $loop->index }}">
                                    <label class="input-label" for="instructions">{{ __('admin::app.catalog.recipes.instruction') }} #{{ $loop->index + 1 }}</label>
                                    <input type="text" style="margin-bottom: 1rem;" class="control" id="instructions" name="{{$locale}}[instructions][]" value="{{ $instruction }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.instructions') }}&quot;"/>
                                    <span class="control-error" v-if="errors.has('instructions')">@{{ errors.first('instructions') }}</span>
                                    <img class="remove-instruction-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                                </div>
                            @endforeach
                        </div>
                        <label class="btn btn-lg btn-primary add-instruction" style="display: inline-block; width: auto;">Add Instruction</label>

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>

                <accordian :title="'{{ __('admin::app.catalog.recipes.ingredients') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}
                        
                        <div class="control-group ingredients-container" :class="[errors.has('ingredients') ? 'has-error' : '']">
                            @php
                                $translated_ingredients = $recipe->translate($locale)["ingredients"];
                            @endphp
                            @foreach($translated_ingredients as $ingredient)
                                <div class="ingredient-container" data-id="{{ $loop->index }}">
                                    <label class="input-label" for="ingredients">{{ __('admin::app.catalog.recipes.ingredient') }} #{{ $loop->index + 1 }}</label>
                                    <input type="text" style="margin-bottom: 1rem;" class="control" id="ingredients" name="{{$locale}}[ingredients][]" value="{{ $ingredient }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.ingredients') }}&quot;"/>
                                    <span class="control-error" v-if="errors.has('ingredients')">@{{ errors.first('ingredients') }}</span>
                                    <img class="remove-ingredient-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                                </div>
                            @endforeach
                        </div>
                        <label class="btn btn-lg btn-primary add-ingredient" style="display: inline-block; width: auto;">Add Ingredient</label>

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>

                <accordian :title="'{{ __('admin::app.catalog.recipes.tags') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}
                        
                        <div class="control-group tags-container" :class="[errors.has('tags') ? 'has-error' : '']">
                            @php
                                $translated_tags = $recipe->tags;
                            @endphp
                            @if(isset($translated_tags))
                                <div class="control-group existing-tags" :class="[errors.has('tags') ? 'has-error' : '']">
                                    <label for="tags" style="margin-bottom:1rem;">{{ __('admin::app.catalog.recipes.tags') }}</label>
                                    <select class="js-multiple-tag control" style="width:300px; margin-top:2rem;" name="tags[]" multiple="multiple">
                                    @foreach($translated_tags as $tag)
                                        <option selected value="{{$tag->id}}"> {{$tag->name}} </option>
                                    @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="tag-container" data-id="0">
                                    <label class="input-label" for="tags">{{ __('admin::app.catalog.recipes.tag') }} #1</label>
                                    <input type="text" style="margin-bottom: 1rem;" class="control" id="tags" name="new_tags[]" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.tags') }}&quot;"/>
                                    <span class="control-error" v-if="errors.has('tags')">@{{ errors.first('tags') }}</span>
                                    <img class="remove-tag-btn" style="display: inline-block; width: 20px; cursor:pointer;" src="{{asset('images/trash.png')}}" />
                                </div>
                            @endif
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


                <accordian :title="'{{ __('admin::app.catalog.recipes.seo') }}'" :active="true">
                    <div slot="body">

                        {!! view_render_event('bagisto.admin.catalog.recipe.create_form_accordian.general.controls.before') !!}

                        <div class="control-group" :class="[errors.has('seo_title') ? 'has-error' : '']">
                            <label for="seo_title">{{ __('admin::app.catalog.recipes.meta_title') }}</label>
                            <input type="text" class="control" id="seo_title" name="seo_title" value="{{ old($locale)['seo_title'] ?? ($recipe->translate($locale)['seo']['title'] ?? '') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.meta_title') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('seo_title')">@{{ errors.first('seo_title') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('seo_desc') ? 'has-error' : '']">
                            <label for="seo_desc">{{ __('admin::app.catalog.recipes.meta_description') }}</label>
                            <input type="text" class="control" id="seo_desc" name="seo_desc" value="{{ old($locale)['seo_desc'] ?? ($recipe->translate($locale)['seo']['description'] ?? '') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.meta_description') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('seo_desc')">@{{ errors.first('seo_desc') }}</span>
                        </div>

                        <div class="control-group" :class="[errors.has('seo_keywords') ? 'has-error' : '']">
                            <label for="seo_keywords">{{ __('admin::app.catalog.recipes.meta_keywords') }}</label>
                            <input type="text" class="control" id="seo_keywords" name="seo_keywords" value="{{ old($locale)['seo_keywords'] ?? ($recipe->translate($locale)['seo']['keywords'] ?? '') }}" data-vv-as="&quot;{{ __('admin::app.catalog.recipes.meta_keywords') }}&quot;"/>
                            <span class="control-error" v-if="errors.has('seo_keywords')">@{{ errors.first('seo_keywords') }}</span>
                        </div>

                        <div class="control-group">
                            <label>{{ __('admin::app.catalog.recipes.image') }}</label>
                            <image-wrapper input-name="seo_image" :multiple="false"></image-wrapper>
                        </div>

                        {!! view_render_event('bagisto.admin.catalog.recipe.create_form_accordian.general.controls.after') !!}

                    </div>
                </accordian>
                

            </div>

        </form>

        {!! view_render_event('bagisto.admin.catalog.recipe.edit.after', ['recipe' => $recipe]) !!}
    </div>
@stop

@push('scripts')
    @include('admin::layouts.tinymce')
    <!-- <script src="{{asset('js/multiselect-dropdown.js')}}"></script> -->
    <script>
        $(document).ready(function () {
            $('#channel-switcher, #locale-switcher').on('change', function (e) {
                $('#channel-switcher').val()

                if (event.target.id == 'channel-switcher') {
                    let locale = "{{ app('Webkul\Core\Repositories\ChannelRepository')->findOneByField('code', $channel)->locales->first()->code }}";

                    $('#locale-switcher').val(locale);
                }

                var query = '?channel=' + ($('#channel-switcher').val() ?? "default") + '&locale=' + $('#locale-switcher').val();

                window.location.href = "{{ route('admin.catalog.recipes.edit', $recipe->id)  }}" + query;
            });


            $("img.remove-instruction-btn").on("click", function (e) {
                let length = $('.instruction-container').length;
                if (length > 1) {
                    $(this).parent().remove(); 
                }
            });

            $("label.add-instruction").on("click", function (e) {
                let lastIndex = $('.instruction-container').length;
                let last = $(".instruction-container:last").clone();
                last.find(".input-label").text("Instruction #" + (lastIndex+1));
                last.find("input").val("");
                last.find("img").on("click", function (e) {
                    $(this).parent().remove(); 
                });
                last.appendTo(".instructions-container");
            });

            $("img.remove-ingredient-btn").on("click", function (e) {
                let length = $('.ingredient-container').length;
                if (length > 1) {
                    $(this).parent().remove(); 
                }
            });

            $("label.add-ingredient").on("click", function (e) {
                let lastIndex = $('.ingredient-container').length;
                let last = $(".ingredient-container:last").clone();
                last.find(".input-label").text("ingredient #" + (lastIndex+1));
                last.find("input").val("");
                last.find("img").on("click", function (e) {
                    $(this).parent().remove(); 
                });
                last.appendTo(".ingredients-container");
            });

            $("img.remove-tag-btn").on("click", function (e) {
                let length = $('.tag-container').length;
                if (length > 1) {
                    $(this).parent().remove(); 
                }
            });

            $("label.add-tag").on("click", function (e) {
                let wasHidden = false;
                if ($('.tag-container').hasClass('d-none')) {
                    wasHidden = true;
                    $('.tag-container').removeClass('d-none');
                    $('.tag-container').addClass('d-block');
                    $('.existing-tags').removeClass('d-block');
                    $('.existing-tags').addClass('d-none');
                    $('.existing-tags-extra').removeClass('d-block');
                    $('.existing-tags-extra').addClass('d-none');
                }
                if (!wasHidden) {
                    let lastIndex = $('.tag-container').length;
                    let last = $(".tag-container:last").clone();
                    last.find(".input-label").text("Tag #" + (lastIndex+1));
                    last.find("input").val("");
                    last.find("img").on("click", function (e) {
                        $(this).parent().remove(); 
                    });
                    last.appendTo(".tags-container");
                }
            });

            $("label.select-tag").on("click", function (e) {
                $('.tag-container').removeClass('d-block');
                $('.tag-container').addClass('d-none');
                $('.existing-tags').removeClass('d-none');
                $('.existing-tags').addClass('d-block');
            });

            tinyMCEHelper.initTinyMCE({
                selector: 'textarea#description, textarea#short_description',
                height: 200,
                width: "100%",
                plugins: 'image imagetools media wordcount save fullscreen code table lists link hr',
                toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor link hr | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent  | removeformat | code | table',
                image_advtab: true,
                uploadRoute: '{{ route('admin.tinymce.upload') }}',
                csrfToken: '{{ csrf_token() }}',
            });

            var base_url = window.location.origin;
            $('.js-example-basic-single').select2({
                placeholder: 'Select a product',
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
                                return {id: d.id, text: d.name}
                            })
                        };
                    }
                }
            });



            $('.js-multiple-tag').select2({
                placeholder: 'Select tags',
                allowClear: true,
                tags: true,
                ajax: {
                    url: `${base_url}/api/tags`,
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

    {{-- <!-- <script type="text/x-template" id="instructions-template">
        <div class="control-group" :class="[errors.has('instructions') ? 'has-error' : '']">
            <label for="instructions" :class="isRequired ? 'required' : ''">{{ __('admin::app.catalog.recipes.instructions') }}</label>
            <textarea v-validate="isRequired ? 'required' : ''"  class="control" id="instructions" name="instructions" data-vv-as="&quot;{{ __('admin::app.catalog.categories.instructions') }}&quot;">
                {{ old($locale)['instructions'] ?? ($recipe->translate($locale)['instructions'] ?? '') }}
            </textarea>
            <span class="control-error" v-if="errors.has('instructions')">@{{ errors.first('instructions') }}</span>
        </div>
    </script> --> --}}

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
