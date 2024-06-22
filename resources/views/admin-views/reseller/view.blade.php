@extends('layouts.back-end.app')

@section('title', $seller->name )

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('Reseller Details')}}
            </h2>
        </div>
        <div class="page-header border-0 mb-4">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <ul class="nav nav-tabs flex-wrap page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active"
                           href="{{ route('admin.sellers.view',$seller->id) }}">{{translate('Reseller OverView')}}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card card-top-bg-element mb-5">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 justify-content-between">
                    <div class="media flex-column flex-sm-row gap-3">
                        <img class="avatar avatar-170 rounded-0"
                             src="{{ getValidImage(path: 'storage/app/public/profile/'.$seller->image, type: 'backend-basic') }}"
                             alt="{{translate('image')}}">
                        <div class="media-body">
                        </div>
                    </div>
                    @if ($seller->reseller_status=="pending")
                        <div class="d-flex justify-content-sm-end flex-wrap gap-2 mb-3">
                            <form class="d-inline-block" action="{{route('admin.reseller.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit"
                                        class="btn btn-danger px-5">{{translate('reject')}}</button>
                            </form>
                            <form class="d-inline-block" action="{{route('admin.reseller.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit"
                                        class="btn btn-success px-5">{{translate('approve')}}</button>
                            </form>
                        </div>
                    @endif
                    @if ($seller->reseller_status=="approved")
                        <div class="d-flex justify-content-sm-end flex-wrap gap-2 mb-3">
                            <form class="d-inline-block" action="{{route('admin.reseller.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="suspended">
                                <button type="submit"
                                        class="btn btn-danger px-5">{{translate('Suspend this reseller')}}</button>
                            </form>
                        </div>
                    @endif
                    @if ($seller->reseller_status=="suspended" || $seller->reseller_status=="rejected")
                        <div class="d-flex justify-content-sm-end flex-wrap gap-2 mb-3">
                            <form class="d-inline-block" action="{{route('admin.sellers.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit"
                                        class="btn btn-success px-5">{{translate('active')}}</button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xxl-3">
                        <h4 class="mb-3 text-capitalize">{{translate('Seller Information')}}</h4>
    
                        <div class="pair-list">
                            <div>
                                <span class="key">{{translate('name')}}</span>
                                <span>:</span>
                                <span class="value">{{$seller->f_name}} {{$seller->l_name}}</span>
                            </div>
    
                            <div>
                                <span class="key">{{translate('email')}}</span>
                                <span>:</span>
                                <span class="value">{{$seller->email}}</span>
                            </div>
    
                            <div>
                                <span class="key">{{translate('phone')}}</span>
                                <span>:</span>
                                <span class="value">{{$seller->phone}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xxl-6 float-left">
                        @if ($seller->reseller_status=="approved")
                            <form action="{{route('admin.reseller.updateStatus')}}" method="POST">
                                <div class="row mb-3">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$seller->id}}">
                                    <div class="col-md-12">
                                        <label for="" class="font-weight-bold">Reseller Domain<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="sub_domain" value="{{$seller->sub_domain ?? ''}}" required >
                                    </div>
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6 mt-2">
                                        <button type="submit"
                                            class="btn btn-success px-5 float-right">{{translate('Submit')}}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>    
                </div>
                
                <hr>
            </div>
        </div>
    </div>
@endsection