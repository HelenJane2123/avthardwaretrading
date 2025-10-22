@extends('layouts.master')

@section('title', 'Add Salesman | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fa fa-user-plus"></i> Add Salesman</h1>
            <small class="text-muted">Fill in the details below to register a new salesman</small>
        </div>
         <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Salesman</li>
            <li class="breadcrumb-item active">Add Salesman</li>
        </ul>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a class="btn btn-outline-primary shadow-sm" href="{{ route('salesmen.index') }}">
            <i class="fa fa-list"></i> Manage Salesman
        </a>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="tile shadow-sm rounded">
                <h3 class="tile-title mb-3">Salesman Information</h3>
                <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
                <div class="tile-body">
                    <form method="POST" action="{{ route('salesmen.store') }}">
                        @csrf
                        <div class="row g-3">
                            <!-- Customer Code -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Salesman Code</label>
                                <input name="salesman_code" id="salesman_code"
                                    class="form-control @error('salesman_code ') is-invalid @enderror"
                                    type="text" readonly>
                                @error('salesman_code ')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Salesman Name -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Salesman Name</label>
                                <input name="salesman_name"
                                    class="form-control @error('salesman_name') is-invalid @enderror"
                                    type="text" placeholder="Enter full name">
                                @error('salesman_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contact -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Contact Number</label>
                                <input name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    type="text" placeholder="Enter contact number">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Address</label>
                                <textarea name="address"
                                    class="form-control @error('address') is-invalid @enderror"
                                    rows="2" placeholder="Street, Barangay, City, Province"></textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group col-md-4">
                                <label class="control-label fw-bold">Email</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="saelsman@email.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="form-group col-md-4">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control col-md-20">
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="form-group mt-4 text-end">
                            <button class="btn btn-success px-4" type="submit">
                                <i class="fa fa-save"></i> Save Salesman
                            </button>
                            <a href="{{ route('salesmen.index') }}" class="btn btn-secondary px-4">
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
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Auto-generate salesman code
        function generateSalesmanCode() {
            const randomPart = Math.random().toString(36).substring(2, 6).toUpperCase();
            const timestampPart = new Date().getTime().toString().slice(-4);
            return `SALEMAN-${randomPart}${timestampPart}`;
        }

        const codeFieldsales = document.getElementById("salesman_code");
        if (codeFieldsales && !codeFieldsales.value) {
            codeFieldsales.value = generateSalesmanCode();
        }
    });
</script>
@endpush
