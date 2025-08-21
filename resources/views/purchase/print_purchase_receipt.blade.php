@extends('layouts.master')

@section('titel', 'Supplier Products | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

    <div class="header">
        <h2>PURCHASE ORDER</h2>
        <table>
            <tr>
                <td>
                    <strong>[Company Name]</strong><br>
                    [Street Address]<br>
                    Phone: (000) 000-0000<br>
                    Website: www.company.com
                </td>
                <td class="right">
                    <strong>Date:</strong> {{ $purchase->date }}<br>
                    <strong>PO #:</strong> {{ $purchase->po_number }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td>
                    <strong>VENDOR</strong><br>
                    {{ $purchase->supplier->name }}<br>
                    {{ $purchase->supplier->address }}
                </td>
                <td>
                    <strong>SHIP TO</strong><br>
                    [Name]<br>
                    [Address]
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>ITEM #</th>
                <th>DESCRIPTION</th>
                <th>QTY</th>
                <th>UNIT PRICE</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->details as $detail)
            <tr>
                <td>{{ $detail->product->code }}</td>
                <td>{{ $detail->product->name }}</td>
                <td>{{ $detail->qty }}</td>
                <td>{{ number_format($detail->price, 2) }}</td>
                <td>{{ number_format($detail->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <table>
        <tr>
            <td class="right bold">SUBTOTAL:</td>
            <td class="right">{{ number_format($purchase->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="right bold">TAX:</td>
            <td class="right">{{ number_format($purchase->tax, 2) }}</td>
        </tr>
        <tr>
            <td class="right bold">SHIPPING:</td>
            <td class="right">{{ number_format($purchase->shipping, 2) }}</td>
        </tr>
        <tr>
            <td class="right bold">OTHER:</td>
            <td class="right">{{ number_format($purchase->other_charges, 2) }}</td>
        </tr>
        <tr>
            <td class="right bold">TOTAL:</td>
            <td class="right"><strong>{{ number_format($purchase->total, 2) }}</strong></td>
        </tr>
    </table>

    <br>
    <p><strong>Comments or Special Instructions:</strong><br>{{ $purchase->remarks }}</p>

@endsection
@push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">$('#productsTable').DataTable();</script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
@endpush
