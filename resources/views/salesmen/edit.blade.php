@extends('layouts.master')

@section('title', 'Edit Salesman | ')

@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-user-edit"></i> Edit Salesman</h1>
                <p class="text-muted mb-0">Update salesman details and information</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Salesman</li>
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
                        <h3 class="tile-title">Salesman Information</h3>
                        <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
                    </div>

                    <div class="tile-body">
                        <form class="row g-3" method="POST" action="{{ route('salesmen.update', $salesman->id) }}">
                            @csrf
                            @method('PUT')

                            <!-- Salesman Code -->
                            <div class="form-group col-md-4">
                                <label class="control-label">Salesman Code</label>
                                <input type="text" name="salesman_code" 
                                       value="{{ $salesman->salesman_code }}" 
                                       class="form-control @error('salesman_code') is-invalid @enderror" 
                                       readonly>
                                @error('salesman_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label class="control-label">Salesman Name <span class="text-danger">*</span></label>
                                <input type="text" name="salesman_name" 
                                       value="{{ $salesman->salesman_name }}" 
                                       placeholder="Enter salesman's full name" 
                                       class="form-control @error('salesman_name') is-invalid @enderror">
                                @error('salesman_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Contact -->
                            <div class="form-group col-md-4">
                                <label class="control-label">Contact Number</label>
                                <input type="text" name="phone" 
                                       value="{{ $salesman->phone }}" 
                                       placeholder="Enter contact number" 
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group col-md-4">
                                <label class="control-label">Email Address</label>
                                <input type="email" name="email" 
                                       value="{{ $salesman->email }}" 
                                       placeholder="salesman@email.com"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="form-group col-md-4">
                                <label class="control-label">Address</label>
                                <textarea name="address" rows="2" 
                                          class="form-control @error('address') is-invalid @enderror"
                                          placeholder="Street, Barangay, City, Province">{{ $salesman->address }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label for="status">Status</label>
                                <select name="status" id="term" class="form-control col-md-20">
                                    <option value="1" {{ old('status', $salesman->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="2" {{ old('status', $salesman->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Submit -->
                            <div class="form-group col-md-12 mt-3 text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check-circle"></i> Update Salesman
                                </button>
                                <a href="{{ route('salesmen.index') }}" class="btn btn-secondary px-4">
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
