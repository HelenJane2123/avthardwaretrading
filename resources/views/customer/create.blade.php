@extends('layouts.master')

@section('title', 'Add Customer | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fa fa-user-plus"></i> Add Customer</h1>
            <small class="text-muted">Fill in the details below to register a new customer</small>
        </div>
         <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Customer</li>
            <li class="breadcrumb-item active">Add Customer</li>
        </ul>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a class="btn btn-outline-primary btn-sm shadow-sm" href="{{ route('customer.index') }}">
            <i class="fa fa-list"></i> Manage Customer
        </a>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="tile shadow-sm rounded">
                <h3 class="tile-title mb-3">Customer Information</h3>
                <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
                <div class="tile-body">
                    <form method="POST" id="customerForm" action="{{ route('customer.store') }}">
                        @csrf
                        <div class="row g-3">
                            <!-- Customer Code -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Customer Code</label>
                                <input name="customer_code" id="customer_code"
                                    class="form-control form-control-sm @error('customer_code') is-invalid @enderror"
                                    type="text" readonly>
                                @error('customer_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Customer Name -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Customer Name</label>
                                <input name="name"
                                    class="form-control form-control-sm @error('name') is-invalid @enderror"
                                    type="text" placeholder="Enter full name">
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contact -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Contact Number</label>
                                <input name="mobile"
                                    class="form-control form-control-sm @error('mobile') is-invalid @enderror"
                                    type="text" placeholder="Enter contact number">
                                @error('mobile')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Address</label>
                                <textarea name="address"
                                    class="form-control form-control-sm @error('address') is-invalid @enderror"
                                    rows="2" placeholder="Street, Barangay, City, Province"></textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Location</label>
                                <input type="location" name="location"
                                    class="form-control form-control-sm @error('location') is-invalid @enderror"
                                    placeholder="Enter Location">
                                @error('location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Email</label>
                                <input type="email" name="email"
                                    class="form-control form-control-sm @error('email') is-invalid @enderror"
                                    placeholder="customer@email.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Details -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Additional Details</label>
                                <textarea name="details"
                                    class="form-control form-control-sm @error('details') is-invalid @enderror"
                                    rows="2" placeholder="Notes or customer preferences"></textarea>
                                @error('details')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Tax ID -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Tax ID</label>
                                <input name="tax"
                                    class="form-control form-control-sm @error('tax') is-invalid @enderror"
                                    type="text" placeholder="123-456-789-000">
                                @error('tax')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="form-group col-md-4">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control form-control-sm col-md-20">
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="form-group mt-4 text-end">
                            <button class="btn btn-sm btn-success px-4" type="submit">
                                <i class="fa fa-save"></i> Save Customer
                            </button>
                            <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary px-4">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Auto-generate customer code
        function generateCustomerCode() {
            const randomPart = Math.random().toString(36).substring(2, 6).toUpperCase();
            const timestampPart = new Date().getTime().toString().slice(-4);
            return `CUST-${randomPart}${timestampPart}`;
        }

        const codeField = document.getElementById("customer_code");
        if (codeField && !codeField.value) {
            codeField.value = generateCustomerCode();
        }
    });
    document.getElementById('customerForm').addEventListener('submit', function (e) {
        const requiredFields = [
            { name: 'name', label: 'Customer Name' },
            { name: 'address', label: 'Address' },
            { name: 'location', label: 'Location' },
            { name: 'status', label: 'Status' }
        ];

        let missing = [];

        requiredFields.forEach(field => {
            const input = document.querySelector(`[name="${field.name}"]`);
            if (!input || !input.value.trim()) {
                missing.push(field.label);
                input && input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (missing.length > 0) {
            e.preventDefault();

            swal({
                title: 'Required Fields Missing',
                html: '<b>Please complete the following:</b><br><br>' + missing.join('<br>'),
                type: 'warning',
                confirmButtonText: 'OK'
            });

            return false;
        }
    });
</script>
@endpush
