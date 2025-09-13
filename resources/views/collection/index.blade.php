
@extends('layouts.master')

@section('titel', 'Collection | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

@section('content')
<div class="app-title">
    <h1><i class="fa fa-money"></i> Collections</h1>
</div>

@if(session()->has('message'))
    <div class="alert alert-success">{{ session('message') }}</div>
@endif

<div class="tile">
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="bg-dark text-white">
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Payment Date</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th width="15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collections as $collection)
                <tr>
                    <td>{{ $collection->invoice->invoice_number }}</td>
                    <td>{{ $collection->invoice->customer->name }}</td>
                    <td>{{ $collection->payment_date }}</td>
                    <td>{{ number_format($collection->amount_paid,2) }}</td>
                    <td>{{ number_format($collection->balance,2) }}</td>
                    <td><span class="badge bg-{{ $collection->payment_status == 'paid' ? 'success' : ($collection->payment_status == 'partial' ? 'warning' : 'danger') }}">{{ ucfirst($collection->payment_status) }}</span></td>
                    <td>
                        <a href="{{ route('collection.edit', $collection->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                        <form action="{{ route('collection.destroy', $collection->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">No collections found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
