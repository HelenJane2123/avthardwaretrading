@extends('layouts.master')

@section('title', 'Collection History | ')

@section('content')

@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-history"></i> Collection History</h1>
            <p class="text-muted mb-0">View collection history and payment details.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Collections</li>
            <li class="breadcrumb-item active">History</li>
        </ul>
    </div>

    <div class="mb-3">
        <a href="{{ route('collection.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-arrow-left"></i> Back to Collections
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <div class="card mb-3">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center" style="font-size: 15px;">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold me-2"><i class="fa fa-info-circle"></i> Collection Status:</span>
                            <span class="mb-0 font-weight-bold ms-4" style="color: {{ $collection->invoice->outstanding_balance <= 0 ? '#28a745' : '#e67e22' }};">
                                @if($collection->invoice->outstanding_balance <= 0)
                                    Paid in full
                                @else
                                    Outstanding balance: ₱{{ number_format($collection->invoice->outstanding_balance, 2) }}
                                @endif
                            </span>
                        </div>
                        <div class="text-end">
                            @if($collection->invoice->outstanding_balance <= 0)
                                <span class="badge badge-success p-2" style="font-size: 14px;">Paid</span>
                            @else
                                <span class="badge badge-warning p-2" style="font-size: 14px;">Outstanding</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="fa fa-file-text"></i> Invoice Details</strong>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th width="30%">Invoice #</th>
                                <td>{{ $collection->invoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th>Invoice Date</th>
                                <td>{{ \Carbon\Carbon::parse($collection->invoice->invoice_date)->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Due Date</th>
                                <td>{{ \Carbon\Carbon::parse($collection->invoice->due_date)->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Invoice Total</th>
                                <td>₱{{ number_format($collection->invoice->grand_total, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Outstanding Balance</th>
                                <td>₱{{ number_format($collection->invoice->outstanding_balance, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="fa fa-user"></i> Customer</strong>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th width="25%">Name</th>
                                <td>{{ $collection->invoice->customer->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $collection->invoice->customer->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $collection->invoice->customer->mobile ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Payment Mode</th>
                                <td>{{ $collection->invoice->paymentMode->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="fa fa-money"></i> This Collection</strong>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th width="30%">Collection #</th>
                                <td>{{ $collection->collection_number }}</td>
                            </tr>
                            <tr>
                                <th>Payment Date</th>
                                <td>{{ \Carbon\Carbon::parse($collection->payment_date)->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Amount Paid</th>
                                <td>₱{{ number_format($collection->amount_paid, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td>{{ $collection->remarks ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="fa fa-history"></i> Collection History</strong>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount Paid</th>
                                        <th>Check #</th>
                                        <th>Bank</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($collection->invoice->collections->sortByDesc('payment_date') as $payment)
                                        <tr @if($payment->id === $collection->id) class="table-success" @endif>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                                            <td class="text-right">₱{{ number_format($payment->amount_paid, 2) }}</td>
                                            <td>{{ $payment->check_number ?? '-' }}</td>
                                            <td>{{ $payment->bank_name ?? '-' }}</td>
                                            <td>{{ $payment->remarks ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
