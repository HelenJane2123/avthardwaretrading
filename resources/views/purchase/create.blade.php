@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Add Purchase</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Purchase</li>
                <li class="breadcrumb-item"><a href="#">Add Purchase</a></li>
            </ul>
        </div>


         <div class="row">
             <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Purchase Order</h3>
                    <div class="tile-body">
                        <!-- <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-primary mb-3" onclick="printPurchaseOrder()">
                                <i class="fa fa-print"></i> Print Purchase Order
                            </button>
                        </div> -->
                        <form method="POST" action="{{ route('invoice.store') }}">
                            @csrf
                            <div class="row mb-4">
                                {{-- Supplier --}}
                                <div class="col-md-4 form-group">
                                    <label>Supplier</label>
                                    <select name="supplier_id" class="form-control" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Purchase Date --}}
                                <div class="col-md-3 form-group">
                                    <label>Purchase Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- PO Number --}}
                                <div class="col-md-3 form-group">
                                    <label for="po_number">PO Number</label>
                                    <input type="text" name="po_number" id="po_number" class="form-control" readonly>
                                </div>

                                {{-- Salesman --}}
                                <div class="col-md-4 form-group">
                                    <label for="salesman">Salesman</label>
                                    <input type="text" name="salesman" id="salesman" class="form-control" placeholder="Enter salesman's name">
                                </div>

                                {{-- Payment Term --}}
                                <div class="col-md-3 form-group">
                                    <label for="payment_term">Payment Term</label>
                                    <select name="payment_term" id="payment_term" class="form-control">
                                        <option value="">Select Term</option>
                                        <option value="Cash">Cash</option>
                                        <option value="7 Days">7 Days</option>
                                        <option value="15 Days">15 Days</option>
                                        <option value="30 Days">30 Days</option>
                                    </select>
                                </div>
                            </div>
                            <div id="supplier-info" class="mt-3 p-3 border rounded bg-light" style="display: none;">
                                <h5 class="mb-3">Supplier Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Supplier Code:</strong><br><span id="info-supplier-code"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong><br><span id="info-name"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Phone:</strong><br><span id="info-phone"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong><br><span id="info-email"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Address:</strong><br><span id="info-address"></span></p>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product</th>
                                        <th>Quantity Ordered</th>
                                        <th>Unit Price</th>
                                        <th>Discount (%)</th>
                                        <th>Amount</th>
                                        <th><a class="btn btn-success btn-sm addRow"><i class="fa fa-plus"></i></a></th>
                                    </tr>
                                </thead>
                                <tbody id="po-body">
                                    <tr>
                                        <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
                                        <td>
                                            <select name="product_id[]" class="form-control productname">
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="qty[]" class="form-control qty">
                                            <small class="text-muted stock-info">
                                                <span class="stock"></span> pcs —
                                                <span class="status badge"></span>
                                            </small>
                                        </td>
                                        <td><input type="number" step="0.01" name="price[]" class="form-control price"></td>
                                        <td><input type="number" step="0.01" name="dis[]" class="form-control dis"></td> <!-- Tax will go here -->
                                        <td><input type="number" step="0.01" name="amount[]" class="form-control amount" readonly></td>
                                        <td><a class="btn btn-danger btn-sm remove"><i class="fa fa-remove"></i></a></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Discount Type</th>
                                        <th colspan="2">
                                            <select id="discount_type" class="form-control">
                                                <option value="per_item" selected>Per Item</option>
                                                <option value="overall">Overall</option>
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Subtotal</th>
                                        <td colspan="2"><input type="text" class="form-control" id="subtotal" readonly></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Tax/Discount</th>
                                        <td colspan="2"><input type="text" class="form-control" id="tax"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Shipping</th>
                                        <td colspan="2"><input type="number" class="form-control" id="shipping" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Other Charges</th>
                                        <td colspan="2"><input type="number" class="form-control" id="other" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Total</th>
                                        <td colspan="2"><input type="text" class="form-control" id="grand_total" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="form-group mt-3">
                                <label>Comments or Special Instructions</label>
                                <textarea name="comments" rows="4" class="form-control" placeholder="Enter any notes or delivery instructions..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">Submit Purchase Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection
@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            
            $('tbody').delegate('.productname', 'change', function () {
                var  tr = $(this).parent().parent();
                tr.find('.qty').focus();
            })

            $('tbody').delegate('.productname', 'change', function () {
                var tr = $(this).parent().parent();
                var productId = tr.find('.productname').val();
                var supplierId = $('select[name="supplier_id"]').val(); // Get the selected supplier ID

                $.ajax({
                    type: 'GET',
                    url: '{{ route('findPricePurchase') }}',
                    dataType: 'json',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        'id': productId,
                        'supplier_id': supplierId // Pass supplier_id to backend
                    },
                    success: function (data) {
                        tr.find('.price').val(data.price);
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            $('tbody').delegate('.qty,.price,.dis', 'keyup', function () {

                var tr = $(this).parent().parent();
                var qty = tr.find('.qty').val();
                var price = tr.find('.price').val();
                var dis = tr.find('.dis').val();
                var amount = (qty * price)-(qty * price * dis)/100;
                tr.find('.amount').val(amount);
                total();
            });
            function total(){
                var total = 0;
                $('.amount').each(function (i,e) {
                    var amount =$(this).val()-0;
                    total += amount;
                })
                $('.total').html(total);
            }

            $('.addRow').on('click', function () {
                addRow();
                calculateTotals();
            });
            const productOptions = `{!! 
                $products->map(function($product){
                    return '<option value="'.$product->id.'">'.$product->name.'</option>';
                })->implode('') 
            !!}`;
            function addRow() {
                var addRow = `<tr>
                    <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
                    <td>
                        <select name="product_id[]" class="form-control productname">
                            <option value="">Select Product</option>
                            ${productOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="qty[]" class="form-control qty">
                        <small class="text-muted stock-info">
                            <span class="stock"></span> pcs — <span class="status badge badge-secondary"></span>
                        </small>
                    </td>
                    <td><input type="text" name="price[]" class="form-control price"></td>
                    <td><input type="text" name="dis[]" class="form-control dis"></td>
                    <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
                    <td><a class="btn btn-danger remove"><i class="fa fa-remove"></i></a></td>
                </tr>`;

                $('tbody').append(addRow);
            }


            $('.remove').live('click', function () {
                var l =$('tbody tr').length;
                if(l==1){
                    alert('you cant delete last one');
                    calculateTotals();
                }
                else{
                    $(this).parent().parent().remove();
                }

            });

            //Populate Supplier Information
            $('select[name="supplier_id"]').on('change', function () {
                var supplierId = $(this).val();
                if (supplierId) {
                    $.ajax({
                        url: '/supplier/' + supplierId + '/info',
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $('#supplier-info').show();
                            $('#info-supplier-code').text(data.supplier_code);
                            $('#info-name').text(data.name);
                            $('#info-address').text(data.address);
                            $('#info-phone').text(data.phone);
                            $('#info-email').text(data.email);
                        },
                        error: function () {
                            $('#supplier-info').hide();
                            alert('Could not fetch supplier info.');
                        }
                    });
                } else {
                    $('#supplier-info').hide();
                }
            });

            // get the latest PO number in database
            $.ajax({
                url: '/api/po/latest',
                method: 'GET',
                success: function (response) {
                    let newPoNumber;

                    if (response.po_number) {
                        // Extract the numeric part, increment, then pad again
                        const numPart = parseInt(response.po_number.replace('PO', ''), 10);
                        const nextNum = numPart + 1;
                        newPoNumber = 'PO' + nextNum.toString().padStart(4, '0');
                    } else {
                        // If no existing PO, start from PO0001
                        newPoNumber = 'PO0001';
                    }

                    $('#po_number').val(newPoNumber);
                },
                error: function () {
                    alert("Failed to generate PO number.");
                    $('#po_number').val('PO0001'); // fallback default
                }
            });
        });

        //Populate Product details
        $(document).on('change', '.productname', function () {
            var $row = $(this).closest('tr');
            var productId = $(this).val();

            if (productId) {
                $.ajax({
                    url: '/getproduct/' + productId,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $row.find('.code').val(data.code);
                        $row.find('.price').val(data.price);
                        $row.find('.dis').val(data.tax); // Fill tax in discount field
                        $row.find('.stock').text(data.stock);
                        var badgeClass = 'badge-secondary';
                        if (data.status === 'Low Stock') badgeClass = 'badge-warning';
                        if (data.status === 'Out of Stock') badgeClass = 'badge-danger';
                        if (data.status === 'In Stock') badgeClass = 'badge-success';

                        $row.find('.status')
                            .text(data.status)
                            .removeClass('badge-secondary badge-warning badge-danger badge-success')
                            .addClass(badgeClass);
                    }
                });
            } else {
                $row.find('.code, .price, .dis').val('');
            }

            calculateTotals();
        });
        function calculateTotals() {
            let subtotal = 0;
            let discountType = $('#discount_type').val();
            let overallDiscount = parseFloat($('#tax').val()) || 0;
            let totalDiscount = 0;

            $('#po-body tr').each(function () {
                const qty = parseFloat($(this).find('.qty').val()) || 0;
                const price = parseFloat($(this).find('.price').val()) || 0;
                const dis = parseFloat($(this).find('.dis').val()) || 0;

                let lineTotal = qty * price;

                if (discountType === 'per_item') {
                    let discountAmount = lineTotal * dis / 100;
                    lineTotal = lineTotal - discountAmount;
                    totalDiscount += discountAmount;
                }

                $(this).find('.amount').val(lineTotal.toFixed(2));
                subtotal += lineTotal;
            });

            if (discountType === 'overall') {
                totalDiscount = subtotal * overallDiscount / 100;
                subtotal = subtotal - totalDiscount;
            }

            const shipping = parseFloat($('#shipping').val()) || 0;
            const other = parseFloat($('#other').val()) || 0;
            const grandTotal = subtotal + shipping + other;

            $('#subtotal').val(subtotal.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));
        }

        // Trigger on keyup/input changes
        $(document).on('input', '.qty, .price, .dis, #shipping, #other, #tax', function () {
            calculateTotals();
        });
        $('#discount_type').on('change', function () {
            const type = $(this).val();
            if (type === 'overall') {
                $('.dis').prop('disabled', true);
            } else {
                $('.dis').prop('disabled', false);
            }
            $('#discount_label').text(type === 'per_item' ? 'Discount (Per Item)' : 'Discount (Overall %)');
            calculateTotals();
        });
    </script>

@endpush



