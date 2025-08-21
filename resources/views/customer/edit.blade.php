@extends('layouts.master')

@section('title', 'Edit Customer | ')

@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-user-edit"></i> Edit Customer</h1>
                <p class="text-muted mb-0">Update customer details and information</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Customer</li>
                <li class="breadcrumb-item active">Edit</li>
            </ul>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-header mb-3">
                        <h3 class="tile-title">Customer Information</h3>
                        <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
                    </div>

                    <div class="tile-body">
                        <form class="row g-3" method="POST" action="{{ route('customer.update', $customer->id) }}">
                            @csrf
                            @method('PUT')

                            <!-- Customer Code -->
                            <div class="form-group col-md-4">
                                <label class="control-label">Customer Code</label>
                                <input type="text" name="customer_code" 
                                       value="{{ $customer->customer_code }}" 
                                       class="form-control @error('customer_code') is-invalid @enderror" 
                                       readonly>
                                @error('customer_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Customer Name -->
                            <div class="form-group col-md-4">
                                <label class="control-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" 
                                       value="{{ $customer->name }}" 
                                       placeholder="Enter customer's full name" 
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contact -->
                            <div class="form-group col-md-4">
                                <label class="control-label">Contact Number</label>
                                <input type="text" name="mobile" 
                                       value="{{ $customer->mobile }}" 
                                       placeholder="Enter contact number" 
                                       class="form-control @error('mobile') is-invalid @enderror">
                                @error('mobile')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group col-md-6">
                                <label class="control-label">Email Address</label>
                                <input type="email" name="email" 
                                       value="{{ $customer->email }}" 
                                       placeholder="customer@email.com"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="form-group col-md-6">
                                <label class="control-label">Address</label>
                                <textarea name="address" rows="2" 
                                          class="form-control @error('address') is-invalid @enderror"
                                          placeholder="Street, Barangay, City, Province">{{ $customer->address }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Details -->
                            <div class="form-group col-md-6">
                                <label class="control-label">Details / Notes</label>
                                <textarea name="details" rows="2" 
                                          class="form-control @error('details') is-invalid @enderror"
                                          placeholder="Notes or customer preferences">{{ $customer->details }}</textarea>
                                @error('details')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Tax -->
                            <div class="form-group col-md-6">
                                <label class="control-label">Tax ID</label>
                                <input type="text" name="tax" 
                                       value="{{ $customer->tax }}" 
                                       placeholder="123-456-789-000" 
                                       class="form-control @error('tax') is-invalid @enderror">
                                @error('tax')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <div class="form-group col-md-12 mt-3 text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check-circle"></i> Update Customer
                                </button>
                                <a href="{{ route('customer.index') }}" class="btn btn-secondary px-4">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div> <!-- /.tile-body -->
                </div>
            </div>
        </div>
    </main>
@endsection
