@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Edit PDC Collection</h4>
        </div>

        <div class="card-body">

            <!-- Update Form -->
            <form action="{{ route('pdc-collections.update', $collection->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">

                    <!-- Collection Number (readonly) -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Collection Number</label>
                        <input type="text" class="form-control" 
                               value="{{ $collection->collection_number }}" disabled>
                    </div>

                    <!-- Payment Date -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" 
                            value="{{ old('payment_date', $collection->payment_date) }}" required>
                        @error('payment_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- Amount Paid -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" step="0.01" name="amount_paid" class="form-control"
                               value="{{ old('amount_paid', $collection->amount_paid) }}" required>
                        @error('amount_paid') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- Remarks -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $collection->remarks) }}</textarea>
                    </div>

                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('pdc-collections.index') }}" class="btn btn-secondary">
                        Back
                    </a>

                    <button type="submit" class="btn btn-success">
                        Update Collection
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
