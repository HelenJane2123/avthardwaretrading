@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">PDC Collections</h3>
        <a href="{{ route('pdc.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New PDC
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>List of Post-Dated Checks</strong>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Collection #</th>
                            <th>Client</th>
                            <th>Check Number</th>
                            <th>Bank</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($pdcs as $pdc)
                        <tr>
                            <td>{{ $pdc->collection_number }}</td>
                            <td>{{ $pdc->client->name ?? 'N/A' }}</td>
                            <td>{{ $pdc->check_number }}</td>
                            <td>{{ $pdc->bank }}</td>
                            <td>₱{{ number_format($pdc->amount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($pdc->due_date)->format('M d, Y') }}</td>

                            <td>
                                @if ($pdc->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif ($pdc->status === 'partially_paid')
                                    <span class="badge bg-info">Partially Paid</span>
                                @else
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('pdc.show', $pdc->collection_number) }}" 
                                   class="btn btn-sm btn-secondary">
                                    <i class="bi bi-eye"></i> View
                                </a>

                                <a href="{{ route('pdc.payments', $pdc->collection_number) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-wallet2"></i> Payments
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @if ($pdcs->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted">No PDC records found.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
