@extends('dashboards.layouts.dashboards')

@section('title', __('views.dashboards.words.management.title'))

@section('content')
    <br><br><br>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Add a new word</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li>
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                        </li>
                        <li>
                            <a class="close-link">
                                <i class="fa fa-close"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br>
                    <form id="demo-form2" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="">
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="newEnglishWord">
                                English <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="newEnglishWord" class="form-control col-md-7 col-xs-12" type="text" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="newFrenchWord">
                                French <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="newFrenchWord" class="form-control col-md-7 col-xs-12" type="text" name="newFrenchWord" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="newEnglishDefinition">
                                English definition <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea id="newEnglishDefinition" class="form-control" type="text" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="newFrenchDefinition">
                                French definition <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <textarea id="newFrenchDefinition" class="form-control" type="text" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="newPicture">
                                Picture <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="newPicture" class="form-control col-md-7 col-xs-12" name="newPicture" style="padding-bottom: 40px;" type="file" accept=".jpeg,.png,.jpg,.JPG,.PNG">
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 newWordButtons">
                                <button id="newWordAdd" class="btn btn-success" type="button">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    {{ Html::script(mix('assets/dashboards/js/pnotify.js')) }}
    {{ Html::script(mix('assets/dashboards/js/words_management.js')) }}
@endsection

@section('styles')
    @parent
    {{ Html::style(mix('assets/dashboards/css/pnotify.css')) }}
    {{ Html::style(mix('assets/dashboards/css/words_management.css')) }}
@endsection