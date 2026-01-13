@extends('layouts.master')

@section('titel', 'Supplier Products | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">

    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-building text-primary"></i> Supplier: <strong>{{ $supplier->name }}</strong></h1>
            <p class="text-muted mb-0">Here is the list of all items supplied by <strong>{{ $supplier->name }}</strong>.</p>
        </div>
        <div>
            <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('supplier.supplier-products.export', $supplier->id) }}" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-2">
        <button class="btn btn-sm btn-primary mr-2" id="editSupplierBtn">
            <i class="fa fa-edit"></i> Edit Supplier
        </button>
        <form action="{{ route('supplier.destroy', $supplier->id) }}"
            method="POST"
            id="deleteSupplierForm">
            @csrf
            @method('DELETE')
            <button type="button"
                    class="btn btn-sm btn-danger"
                    id="deleteSupplierBtn">
                <i class="fa fa-trash"></i> Delete Supplier
            </button>
        </form>
    </div>

    <div class="tile mb-4">
        <div class="tile-body">
            <h5 class="text-dark mb-3">Supplier Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Code:</strong> {{ $supplier->supplier_code }}</p>
                    <p><strong>Address:</strong> {{ $supplier->address }}</p>
                    <p><strong>Contact:</strong> {{ $supplier->mobile }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> {{ $supplier->email }}</p>
                    <p><strong>Tax:</strong> {{ $supplier->tax }}</p>
                    <p><strong>Details:</strong> {{ $supplier->details }}</p>
                </div>
            </div>
        </div>
    </div>
     <!-- Flash Message -->
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session()->has('error'))
            <div class="alert alert-dange alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle"></i> {{ session()->get('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="tile">
        <div class="tile-body">
            <h5 class="text-dark mb-3">Product List</h5>
            <div class="d-flex justify-content-end mb-2">
                <button class="btn btn-sm btn-success mr-2" id="addItemBtn">
                    <i class="fa fa-plus"></i> Add Item
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="productsTable">
                    <thead class="thead-dark medium">
                        <tr class="bg-light">
                            <th>Image</th>
                            <th>Item Code</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Unit Cost</th>
                            <th>Discount</th>
                            <th>Net Cost</th>
                            <th>Volume Less</th>
                            <th>Regular Less</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="medium">
                        @forelse($supplier->items as $item)
                            <tr id="item-row-{{ $item->id }}">
                                <td>
                                    @if($item->item_image)
                                        <img src="{{ asset('storage/' . $item->item_image) }}" width="60" class="img-thumbnail">
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $item->item_code }}</td>
                                <td>{{ $item->category->name ?? 'N/A' }}</td>
                                <td>{{ $item->item_description }}</td>
                                <td>{{ $item->unit->name ?? 'N/A' }}</td>
                                <td>₱{{ number_format($item->item_price, 2) }}</td>
                                <td>
                                    @php
                                        $discounts = [];
                                        foreach([$item->discount_1, $item->discount_2, $item->discount_3] as $discount) {
                                            if ($discount > 0) {
                                                $discounts[] = rtrim(rtrim($discount, ''), '.') . '%';
                                            }
                                        }
                                    @endphp
                                    @if(count($discounts))
                                        {{ ucfirst($item->discount_less_add) }} {{ implode(' ', $discounts) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>₱{{ number_format($item->net_price, 2) }}</td>
                                <td>{{ $item->volume_less }}</td>
                                <td>{{ $item->regular_less }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-item"
                                            onclick="openEditModal(this)"
                                            data-item-id="{{ $item->id }}"
                                            data-category-id="{{ $item->category->id ?? '' }}"
                                            data-description="{{ $item->item_description }}"
                                            data-unit-id="{{ $item->unit->id ?? '' }}"
                                            data-item-price="{{ $item->item_price }}"
                                            data-net-price="{{ $item->net_price }}"
                                            data-discount-less-add="{{ $item->discount_less_add }}"
                                            data-discount-1="{{ $item->discount_1 }}"
                                            data-discount-2="{{ $item->discount_2 }}"
                                            data-discount-3="{{ $item->discount_3 }}"
                                            data-volume-less="{{ $item->volume_less }}"
                                            data-regular-less="{{ $item->regular_less }}"
                                            data-image="{{ $item->item_image ? asset('storage/'.$item->item_image) : '' }}"
                                            title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-item"
                                            data-id="{{ $item->id }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <!-- <tr>
                                <td class="text-center text-muted">No products available.</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr> -->
                            @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Supplier Modal -->
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <form method="POST" action="{{ route('supplier.update', $supplier->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Supplier Details</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="supplier_code">Supplier Code</label>
                            <input type="text" class="form-control" id="supplier_code" value="{{ $supplier->supplier_code }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="name">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="mobile">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mobile" name="mobile" value="{{ $supplier->mobile }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $supplier->email }}">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2">{{ $supplier->address }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="details">Additional Details</label>
                            <textarea class="form-control" id="details" name="details" rows="2">{{ $supplier->details }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Item Edit Modal -->
    <div class="modal fade" id="itemEditModal" tabindex="-1" role="dialog" aria-labelledby="itemEditModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="itemEditForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="item_id" id="modal_item_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Category</label>
                                <select name="category_id" id="modal_category" class="form-control form-control-sm">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Description</label>
                                <input type="text" name="item_description" id="modal_description" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Unit</label>
                                <select name="unit_id" id="modal_unit" class="form-control form-control-sm">
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Unit Cost</label>
                                <input type="number" name="item_price" id="modal_price" class="form-control form-control-sm" step="0.01">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Net Cost</label>
                                <input type="number" name="net_price" id="modal_net_cost" class="form-control form-control-sm" step="0.01">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Discount</label>
                                <select name="discount_less_add" id="modal_discount_type" class="form-control form-control-sm mb-1">
                                    <option value="less">Less (-)</option>
                                    <option value="add">Add (+)</option>
                                </select>
                                <select name="discount_1" id="modal_discount_1" class="form-control form-control-sm mb-1">
                                    <option value="0">Discount 1 (%)</option>
                                    @foreach($discounts_items as $discount)
                                    <option value="{{ $discount->name }}">{{ $discount->name }}%</option>
                                    @endforeach
                                </select>
                                <select name="discount_2" id="modal_discount_2" class="form-control form-control-sm mb-1">
                                    <option value="0">Discount 2 (%)</option>
                                    @foreach($discounts_items as $discount)
                                    <option value="{{ $discount->name }}">{{ $discount->name }}%</option>
                                    @endforeach
                                </select>
                                <select name="discount_3" id="modal_discount_3" class="form-control form-control-sm">
                                    <option value="0">Discount 3 (%)</option>
                                    @foreach($discounts_items as $discount)
                                    <option value="{{ $discount->name }}">{{ $discount->name }}%</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Image</label>
                                <input type="file" name="item_image" id="modal_image" class="form-control form-control-sm">
                                <img id="modal_image_preview" src="" class="img-thumbnail mt-2" style="display:none; max-width:120px;">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Volume Less</label>
                                <input type="text" name="volume_less" id="modal_volume_less" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Regular Less</label>
                                <input type="text" name="regular_less" id="modal_regular_less" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="itemAddModal" tabindex="-1" role="dialog" aria-labelledby="itemAddModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="itemAddForm" enctype="multipart/form-data" action="{{ route('supplier-items.store') }}">
                @csrf
                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Item</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Item Code (auto) -->
                            <div class="form-group col-md-4">
                                <label>Item Code</label>
                                <input type="text" name="item_code" id="new_item_code" class="form-control form-control-sm" readonly>
                            </div>
                            <!-- Category -->
                            <div class="form-group col-md-4">
                                <label>Category</label>
                                <select name="category_id" id="new_category" class="form-control form-control-sm">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Description -->
                            <div class="form-group col-md-4">
                                <label>Description</label>
                                <input type="text" name="item_description" id="new_description" class="form-control form-control-sm">
                            </div>
                            <!-- Unit -->
                            <div class="form-group col-md-4">
                                <label>Unit</label>
                                <select name="unit_id" id="new_unit" class="form-control form-control-sm">
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Price -->
                            <div class="form-group col-md-4">
                                <label>Unit Cost</label>
                                <input type="number" name="item_price" id="new_price" class="form-control form-control-sm" step="0.01">
                            </div>
                            <!-- Net Cost -->
                            <div class="form-group col-md-4">
                                <label>Net Cost</label>
                                <input type="number" name="net_price" id="new_net_cost" class="form-control form-control-sm" step="0.01">
                            </div>
                            <!-- Discounts -->
                            <div class="form-group col-md-4">
                                <label>Discount</label>
                                <select name="discount_less_add" id="new_discount_type" class="form-control form-control-sm mb-1">
                                    <option value="less">Less (-)</option>
                                    <option value="add">Add (+)</option>
                                </select>
                                <select name="discount_1" id="new_discount_1" class="form-control form-control-sm mb-1">
                                    <option value="0">Discount 1 (%)</option>
                                    @foreach($discounts_items as $discount)
                                        <option value="{{ $discount->name }}">{{ $discount->name }}%</option>
                                    @endforeach
                                </select>
                                <select name="discount_2" id="new_discount_2" class="form-control form-control-sm mb-1">
                                    <option value="0">Discount 2 (%)</option>
                                    @foreach($discounts_items as $discount)
                                        <option value="{{ $discount->name }}">{{ $discount->name }}%</option>
                                    @endforeach
                                </select>
                                <select name="discount_3" id="new_discount_3" class="form-control form-control-sm">
                                    <option value="0">Discount 3 (%)</option>
                                    @foreach($discounts_items as $discount)
                                        <option value="{{ $discount->name }}">{{ $discount->name }}%</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Image -->
                            <div class="form-group col-md-4">
                                <label>Image</label>
                                <input type="file" name="item_image" class="form-control form-control-sm">
                            </div>
                            <!-- Volume & Regular Less -->
                            <div class="form-group col-md-4">
                                <label>Volume Less</label>
                                <input type="text" name="volume_less" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Regular Less</label>
                                <input type="text" name="regular_less" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Add Item</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</main>
@endsection

@push('js')
<script src="{{ asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
<script src="{{ asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('#productsTable').DataTable({
        language: {
            emptyTable: "No products available."
        }
    });

    $('#itemAddModal').on('shown.bs.modal', function () {
        $('#new_category').select2({
            placeholder: "Select Category",
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $('#itemAddModal') 
        });

        $('#new_unit').select2({
            placeholder: "Select Unit",
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $('#itemAddModal') 
        });
    });
    $('#itemEditModal').on('shown.bs.modal', function () {
        $('#modal_category').select2({
            dropdownParent: $('#itemEditModal'),
            width: '100%'
        });

        $('#modal_unit').select2({
            dropdownParent: $('#itemEditModal'),
            width: '100%'
        });
    });
    // Supplier modal
    $('#editSupplierBtn').click(function() {
        $('#supplierModal').modal('show');
    });

    // Delete supplier
    $('#deleteSupplierBtn').click(function() {
        Swal.fire({
            title: 'Delete Supplier?',
            text: 'This will delete supplier and all items!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then(result => {
            if (result.value) {
                $('#deleteSupplierForm').submit();
            }
        });
    });

    // Open item edit modal
    function openEditModal(button) {
        let itemId = $(button).data('item-id');
        $('#modal_item_id').val(itemId);
        $('#modal_category').val($(button).data('category-id'));
        $('#modal_description').val($(button).data('description'));
        $('#modal_unit').val($(button).data('unit-id'));
        $('#modal_price').val($(button).data('item-price'));
        $('#modal_net_cost').val($(button).data('net-price'));
        $('#modal_discount_type').val($(button).data('discount-less-add'));
        $('#modal_discount_1').val($(button).data('discount-1'));
        $('#modal_discount_2').val($(button).data('discount-2'));
        $('#modal_discount_3').val($(button).data('discount-3'));
        $('#modal_volume_less').val($(button).data('volume-less'));
        $('#modal_regular_less').val($(button).data('regular-less'));
        let image = $(button).data('image');
        if(image) {
            $('#modal_image_preview').attr('src', image).show();
        } else {
            $('#modal_image_preview').hide();
        }

        // Set form action dynamically
        $('#itemEditForm').attr('action', `/supplier-items/${itemId}`);
        $('#itemEditModal').modal('show');
    }

    // Image live preview
    $('#modal_image').on('change', function(e) {
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#modal_image_preview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(this.files[0]);
    });

    // Delete item
    $('.delete-item').click(function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Delete Item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then(result => {
            if (result.value) {
                $('<form>', {
                    method: 'POST',
                    action: `/supplier-items/${id}`
                })
                .append('@csrf')
                .append('@method("DELETE")')
                .appendTo('body')
                .submit();
            }
        });
    });

    // AJAX update for item
    $('#itemEditForm').submit(function(e){
        e.preventDefault();

        let formData = new FormData(this);
        let actionUrl = $(this).attr('action');
        console.log(actionUrl);

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                // Close modal
                $('#itemEditModal').modal('hide');

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Item updated successfully'
                }).then(() => {
                    // Refresh the page to reflect updated item
                    location.reload();
                });

                // Update the row in the table dynamically
                let item = response.item;
                let row = $('#item-row-' + item.id);

                let discounts = [];
                if(item.discount_1 > 0) discounts.push(item.discount_1 + '%');
                if(item.discount_2 > 0) discounts.push(item.discount_2 + '%');
                if(item.discount_3 > 0) discounts.push(item.discount_3 + '%');

                let discountText = discounts.length ? item.discount_less_add.charAt(0).toUpperCase() + item.discount_less_add.slice(1) + ' ' + discounts.join(' ') : '-';

                let imageHtml = item.item_image 
                    ? `<img src="/storage/${item.item_image}" width="60" class="img-thumbnail">`
                    : '<span class="text-muted">N/A</span>';

                row.html(`
                    <td>${imageHtml}</td>
                    <td>${item.item_code}</td>
                    <td>${item.category_name ?? 'N/A'}</td>
                    <td>${item.item_description}</td>
                    <td>${item.unit_name ?? 'N/A'}</td>
                    <td>₱${parseFloat(item.item_price).toFixed(2)}</td>
                    <td>${discountText}</td>
                    <td>₱${parseFloat(item.net_price).toFixed(2)}</td>
                    <td>${item.volume_less ?? ''}</td>
                    <td>${item.regular_less ?? ''}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-item" 
                                onclick="openEditModal(this)"
                                data-item-id="${item.id}"
                                data-category-id="${item.category_id}"
                                data-category-name="${item.category_name ?? ''}"
                                data-description="${item.item_description}"
                                data-unit-id="${item.unit_id ?? ''}"
                                data-unit-name="${item.unit_name ?? ''}"
                                data-item-price="${item.item_price}"
                                data-net-price="${item.net_price}"
                                data-discount-less-add="${item.discount_less_add}"
                                data-discount-1="${item.discount_1}"
                                data-discount-2="${item.discount_2}"
                                data-discount-3="${item.discount_3}"
                                data-volume-less="${item.volume_less ?? ''}"
                                data-regular-less="${item.regular_less ?? ''}"
                                data-image="${item.item_image ? '/storage/' + item.item_image : ''}">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-item" data-id="${item.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                `);
            },
            error: function(err){
                let msg = '';
                if(err.responseJSON && err.responseJSON.errors){
                    $.each(err.responseJSON.errors, function(key, value){
                        msg += value + '\n';
                    });
                } else {
                    msg = 'Something went wrong. Please try again.';
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    $('#addItemBtn').click(function() {
        let supplierId = {{ $supplier->id }};
        $.get(`/supplier/${supplierId}/last-item-code`, function(data) {
            $('#new_item_code').val(data.new_code);
            $('#itemAddModal').modal('show');
        });
    });
    // Compute net cost on price or discount change
    function computeNetCost() {
        let price = parseFloat($('#modal_price').val()) || 0;
        let d1 = parseFloat($('#modal_discount_1').val()) || 0;
        let d2 = parseFloat($('#modal_discount_2').val()) || 0;
        let d3 = parseFloat($('#modal_discount_3').val()) || 0;
        let type = $('#modal_discount_type').val();
        let net = price;
        
        if (type === 'less') {
            if (d1 > 0) net -= net * (d1 / 100);
            if (d2 > 0) net -= net * (d2 / 100);
            if (d3 > 0) net -= net * (d3 / 100);
        } else {
            if (d1 > 0) net = net * d1;
            if (d2 > 0) net = net * d2;
            if (d3 > 0) net = net * d3;
        }
        
        $('#modal_net_cost').val(net.toFixed(2));
    }
    console.log('compute cost', computeNetCost());
    $('#modal_price, #modal_discount_1, #modal_discount_2, #modal_discount_3, #modal_discount_type').on('change keyup', computeNetCost);

    function computeAddNetCost() {
        let price = parseFloat($('#new_price').val()) || 0;
        let d1 = parseFloat($('#new_discount_1').val()) || 0;
        let d2 = parseFloat($('#new_discount_2').val()) || 0;
        let d3 = parseFloat($('#new_discount_3').val()) || 0;
        let type = $('#new_discount_type').val();

        let net = price;

        if (type === 'less') {
            if (d1 > 0) net -= net * (d1 / 100);
            if (d2 > 0) net -= net * (d2 / 100);
            if (d3 > 0) net -= net * (d3 / 100);
        } else {
            if (d1 > 0) net *= net * (d1 / 100);
            if (d2 > 0) net *= net * (d2 / 100);
            if (d3 > 0) net *= net * (d3 / 100);
        }

        $('#new_net_cost').val(net.toFixed(2));
    }

    // Trigger calculation on change or keyup
    $('#new_price, #new_discount_1, #new_discount_2, #new_discount_3, #new_discount_type')
        .on('change keyup', computeAddNetCost);
</script>
@endpush
