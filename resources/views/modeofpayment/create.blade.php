@extends('layouts.master')

@section('title', 'Mode of Payment| ')
@section('content')
@include('partials.header')
@include('partials.sidebar')
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-plus"></i> Add Mode of Payment</h1>
            <p class="text-muted">Create a new payment method to simplify transactions.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Mode of Payment</li>
            <li class="breadcrumb-item"><a href="#">Add Mode of Payment</a></li>
        </ul>
    </div>

    <div class="">
        <a class="btn btn-sm btn-outline-primary" href="{{ route('modeofpayment.index') }}">
            <i class="fa fa-cogs"> </i> Manage Mode of Payment
        </a>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">Mode of Payment</h3>
                @if(session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
                <div class="tile-body">
                    <form method="POST" action="{{ route('modeofpayment.store') }}">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter Name" oninput="toggleTermDropdown()" value="{{ old('name') }}" class="form-control form-control-sm" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            
                            <div id="term-container" style="display: none;">
                                <label for="term">Payment Term</label>
                                <select name="term" id="term" class="form-control form-control-sm col-md-20">
                                    <option value="30">30 days</option>
                                    <option value="45">45 days</option>
                                    <option value="90">90 days</option>
                                    <option value="60">60 days</option>
                                    <option value="120">120 days</option>
                                    <option value="150">150 days</option>
                                    <option value="160">160 days</option>
                                    <option value="180">180 days</option>
                                </select>
                            </div>

                             <div class="form-group col-md-6">
                                <label class="control-label">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm @error('description') is-invalid @enderror">
                                @error('description')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                           
                            <div class="form-group col-md-12 mt-3">
                                <button class="btn btn-sm btn-success float-right" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i> Add Mode of Payment 
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
function toggleTermDropdown() {
    const nameValue = document.getElementById('name').value.trim().toLowerCase();
    const termContainer = document.getElementById('term-container');
    
    if (nameValue === 'pdc/check') {
        termContainer.style.display = 'block';
    } else {
        termContainer.style.display = 'none';
    }
}

// Call once on page load in case form is prefilled (e.g. after validation errors)
document.addEventListener('DOMContentLoaded', () => {
    toggleTermDropdown();
});
</script>
@endsection

