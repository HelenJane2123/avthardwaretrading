@extends('layouts.master')

@section('title', 'Customer | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Edit Customer</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Customer</li>
                <li class="breadcrumb-item"><a href="#">Edit Customer</a></li>
            </ul>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="row">
            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Edit Customer Form</h3>
                    <div class="tile-body">
                        <form class="row" method="POST" action="{{ route('customer.update', $customer->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-4">
                                <label class="control-label">Customer Code</label>
                                <input value="{{ $customer->customer_code }}" name="customer_code" class="form-control @error('customer_code') is-invalid @enderror" id="customer_code" type="text" readonly>
                                @error('customer_code')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Customer Name</label>
                                <input value="{{ $customer->name }}" name="name" class="form-control @error('name') is-invalid @enderror" type="text" placeholder="Enter Customer's Name">
                                @error('name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label class="control-label">Contact</label>
                                <input value="{{ $customer->mobile }}" name="mobile" class="form-control @error('mobile') is-invalid @enderror" type="text" placeholder="Enter Contact Number">
                                @error('mobile')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Email</label>
                                <input value="{{ $customer->email }}" name="email" class="form-control @error('email') is-invalid @enderror" type="email" placeholder="Enter Email">
                                @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Address</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror">{{ $customer->address }}</textarea>
                                @error('address')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Details</label>
                                <textarea name="details" class="form-control @error('details') is-invalid @enderror">{{ $customer->details }}</textarea>
                                @error('details')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Tax</label>
                                <input value="{{ $customer->tax }}" name="tax" class="form-control @error('tax') is-invalid @enderror" type="text" placeholder="123-456-789-000">
                                @error('tax')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                              <div class="form-group col-md-6">
                                <label class="control-label">Customer Credit Balance</label>
                                <input value="{{ $customer->previous_balance }}" name="previous_balance" class="form-control @error('previous_balance') is-invalid @enderror" type="text" placeholder="Example: 111">
                                @error('previous_balance')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mt-3">
                                <button class="btn btn-success" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i>Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
