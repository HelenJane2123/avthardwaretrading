@extends('layouts.master')

@section('title', 'Mode of Payment | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Edit Mode of Payment</h1>
                <p class="text-muted">Update the details of an existing payment method to keep your transactions organized.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Mode of Payment</li>
                <li class="breadcrumb-item"><a href="#">Edit Mode of Payment</a></li>
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
                    <h3 class="tile-title">Edit Mode of Payment</h3>
                    <div class="tile-body">
                        <form class="row" method="POST" action="{{ route('modeofpayment.update', $modeofpayment->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group col-md-6">
                                <label class="control-label">Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter Name" oninput="toggleTermDropdown()" value="<?php echo  e(old('name', $modeofpayment->name)); ?>" class="form-control form-control-sm" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div id="term-container" style="display: none;">
                                <label for="term">Payment Term</label>
                                <select name="term" id="term" class="form-control form-control-sm col-md-20">
                                    <option value="30" {{ old('term', $modeofpayment->term) == '30' ? 'selected' : '' }}>30 days</option>
                                    <option value="45" {{ old('term', $modeofpayment->term) == '45' ? 'selected' : '' }}>45 days</option>
                                    <option value="60" {{ old('term', $modeofpayment->term) == '60' ? 'selected' : '' }}>60 days</option>
                                    <option value="90" {{ old('term', $modeofpayment->term) == '90' ? 'selected' : '' }}>90 days</option>
                                    <option value="120" {{ old('term', $modeofpayment->term) == '120' ? 'selected' : '' }}>120 days</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Description</label>
                                <input value="<?php echo e(old('description', $modeofpayment->description)); ?>" name="description" class="form-control form-control-sm <?php $__errorArgs = ['description']; ?>">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mt-3">
                                <button class="btn btn-sm btn-success" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i>Update
                                </button>
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
