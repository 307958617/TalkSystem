@extends('layouts.app')
@section('css')
    <!-- 引入croppie的css样式，不然图片显示不正常 -->
    <link href="https://cdn.bootcss.com/croppie/2.4.1/croppie.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                暂时不处理
            </div>
            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Upload Avatar</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <!-- 下面是引入的vue组件UploadAvatar.vue -->
                    <upload-avatar avatar="{{ Auth::user()->avatar }}"></upload-avatar>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>
    </div>

@endsection


