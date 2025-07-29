@extends('layouts.master')

@section('title', 'Customer | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-edit"></i> Add Customer</h1>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Customer</li>
            <li class="breadcrumb-item"><a href="#">Add Customer</a></li>
        </ul>
    </div>

    <div class="">
        <a class="btn btn-primary" href="{{ route('customer.index') }}">
            <i class="fa fa-edit"> </i> Manage Customers
        </a>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">Customer</h3>
                <div class="tile-body">
                    <form method="POST" action="{{ route('customer.store') }}">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="control-label">Customer Code</label>
                                <input name="customer_code" class="form-control @error('customer_code') is-invalid @enderror" id="customer_code" type="text" readonly>
                                @error('customer_code')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Customer Name</label>
                                <input name="name" class="form-control @error('name') is-invalid @enderror" type="text" placeholder="Enter Customer's Name">
                                @error('name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label class="control-label">Contact</label>
                                <input name="mobile" class="form-control @error('mobile') is-invalid @enderror" type="text" placeholder="Enter Contact Number">
                                @error('mobile')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label class="control-label">Address</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror"></textarea>
                                @error('address')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label class="control-label">Details</label>
                                <textarea name="details" class="form-control @error('details') is-invalid @enderror"></textarea>
                                @error('details')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="control-label">Customer Credit Balance</label>
                                <input name="previous_balance" class="form-control @error('previous_balance') is-invalid @enderror" type="text" placeholder="Example: 1000000">
                                @error('previous_balance')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                           <div class="form-group col-md-3">
                                <label class="control-label">Tax ID</label>
                                <input name="tax"
                                    class="form-control @error('tax') is-invalid @enderror"
                                    type="text"
                                    placeholder="123-456-789-000">
                                @error('tax')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mt-3">
                                <button class="btn btn-success float-right" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i> Add Customer Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
@push('js')
<script src="https://cdn.jsdelivr.net/npm/philippine-location-json-for-geer@1.1.11/build/phil.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Auto-generate customer code
        function generateCustomerCode() {
            const randomPart = Math.random().toString(36).substring(2, 6).toUpperCase(); // Example: A1B2
            const timestampPart = new Date().getTime().toString().slice(-4); // Example: 4583
            return `CUST-${randomPart}${timestampPart}`; // Final: CUST-A1B24583
        }

        const codeField = document.getElementById("customer_code");
        if (codeField && !codeField.value) {
            codeField.value = generateCustomerCode();
        }

        document.addEventListener("DOMContentLoaded", function () {
            const provinces = phil.getProvinces();
            console.log("Provinces loaded:", provinces);
        });
    });
</script>
@endpush