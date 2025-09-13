@extends('layouts.master')
@section('title', 'Edit Collection')

@section('content')
<div class="app-title">
    <h1><i class="fa fa-edit"></i> Edit Collection</h1>
</div>

<div class="tile">
    <form action="{{ route('collection.update', $collection->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Invoice #</label>
            <input type="text" class="form-control" value="{{ $collection->invoice->invoice_number }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Customer</label>
            <input type="text" class="form-control" value="{{ $collection->invoice->customer->name }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount Paid</label>
            <input type="number" step="0.01" name="amount_paid" class="form-control" value="{{ $collection->amount_paid }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" rows="3" class="form-control">{{ $collection->remarks }}</textarea>
        </div>

        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update</button>
        <a href="{{ route('collection.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
