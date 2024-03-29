{{-- product modal --}}
<div class="modal fade" id="purchaseEntryModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Purchase Invoice Entry</h5>
                <button type="button" id="purchase_entry_close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="purchaseEntryForm" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <div id="purchase_entry_err"></div>
                        
                        <div class="row">
                            <div class="col-md-12">

                                <div class="card">
                                    <div class="card-header">
                                        
                                        <div class="row">
                                            <div class="col-md-9"><b>Supplier</b></div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{MyApp::IMPORT_CSV_FILE}}" id="import_csv_file">
                                                    <label class="form-check-label" for="flexCheckDefault"><b>Import From CSV File</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="row">
                                                            <div class="col-md-12" id="supplier_div">
                                                                {{-- <select id="supplier_id" name="supplier_id" class="form-select form-select-sm" onchange="supplierDetail(this.value);"> --}}
                                                                <select id="supplier_id" name="supplier_id" class="form-select form-select-sm">
                                                                    <option selected disabled value="0">Supplier</option>                                          
                                                                    @foreach ($suppliers as $list)
                                                                    <option value="{{$list->id}}" state-type="{{$list->state_type}}"> {{ucwords($list->supplier_name)}} </option>
                                                                    @endforeach
                                                                </select>  
        
                                                            </div>
                                                            
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="col-md-12">
                                                                <textarea class="form-control" id="supplier_address" style="height: 70px;"  placeholder="Address" disabled readonly></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="text" name="supplier_code" id="supplier_code" class="form-control form-control-sm" placeholder="Supplier Code" readonly disabled>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-1">
                                                            <div class="col-md-12">
                                                                <input type="text"  name="gst_no"  id="gst_no" class="form-control form-control-sm" placeholder="GSTIN" readonly disabled>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="text"  name="payment_days"  id="payment_days" class="form-control form-control-sm" placeholder="Payment Days">
                                                            </div>
                                                        </div>
                                                       
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <input type="text" name="bill_no"  id="bill_no" class="form-control form-control-sm" placeholder="Bill no">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date"  name="bill_date"  id="bill_date" class="form-control form-control-sm" placeholder="Bill Date">
                                                    </div>
                                                </div>

                                                <div class="row mt-2 direct_entry">
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <select id="category_id" name="category_id" class="form-select select_chosen_80" onchange="getSubCategoryByCategory(this.value);">
                                                                <option selected disabled value="0">Category</option>
                                                                @foreach ($categories as $list)
                                                                <option value="{{$list->id}}" size-type="{{$list->size_type}}"> {{ucwords($list->category)}} </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                <i class="fas fa-plus cursor_pointer" id="categoryBtn"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <select id="sub_category_id" name="sub_category_id" class="form-select select_chosen_80">
                                                                <option selected disabled >Sub Category</option>
                                                            </select>
                                                            <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                <i class="fas fa-plus cursor_pointer" id="subCategoryBtn"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-2 direct_entry">
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <select id="brand_id" name="brand_id" class="form-select select_chosen_80" >
                                                                <option selected disabled value="0">Brand</option>
                                                                @foreach ($brands as $list)
                                                                    <option value="{{$list->id}}"> {{ucwords($list->brand_name)}} </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                <i class="fas fa-plus cursor_pointer" id="addBrandBtn"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <select id="style_no_id" name="style_no_id" class="form-select select_chosen_80">
                                                                <option selected disabled >Style No</option>
                                                            </select>
                                                            <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                <i class="fas fa-plus cursor_pointer" id="styleNoBtn"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- <div class="row">
                                                    <div class="col-md-6 ">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="text" name="bill_no"  id="bill_no" class="form-control form-control-sm" placeholder="Bill no">
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row mt-2 ">
                                                            <div class="col-md-12">
                                                                <div class="input-group">
                                                                    <select id="category_id" name="category_id" class="form-select select_chosen_80" onchange="getSubCategoryByCategory(this.value);">
                                                                        <option selected disabled value="0">Category</option>
                                                                        @foreach ($categories as $list)
                                                                        <option value="{{$list->id}}" size-type="{{$list->size_type}}"> {{ucwords($list->category)}} </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                        <i class="fas fa-plus cursor_pointer" id="categoryBtn"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 ">
                                                            <div class="col-md-12">
                                                                <div class="input-group">
                                                                    <select id="sub_category_id" name="sub_category_id" class="form-select select_chosen_80">
                                                                        <option selected disabled >Sub Category</option>
                                                                    </select>
                                                                    <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                        <i class="fas fa-plus cursor_pointer" id="subCategoryBtn"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
        
                                                    <div class="col-md-6 ">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="date"  name="bill_date"  id="bill_date" class="form-control form-control-sm" placeholder="Bill Date">
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 ">
                                                            <div class="col-md-12">
                                                                <div class="input-group">
                                                                    <select id="brand_id" name="brand_id" class="form-select select_chosen_80" >
                                                                        <option selected disabled value="0">Brand</option>
                                                                        @foreach ($brands as $list)
                                                                            <option value="{{$list->id}}"> {{ucwords($list->brand_name)}} </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                        <i class="fas fa-plus cursor_pointer" id="addBrandBtn"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 ">
                                                            <div class="col-md-12">
                                                                <div class="input-group">
                                                                    <select id="style_no_id" name="style_no_id" class="form-select select_chosen_80">
                                                                        <option selected disabled >Style No</option>
                                                                    </select>
                                                                    <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                                        <i class="fas fa-plus cursor_pointer" id="styleNoBtn"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                       
                                                    </div>

                                                </div> --}}

                                                <div class="row import_csv hide mt-2">
                                                    <div class="col-md-12">
                                                        <input type="file" name="pt_file" id="pt_file" accept=".csv" class="form-control form-control-sm">
                                                    </div>
                                                    {{-- <div class="col-md-3">
                                                        <div class="d-grid gap-2">
                                                            <button type="button" id="savePurchaseEntryExcelBtn" class="btn btn-primary btn-sm ">Load PT File Data</button>
                                                        </div>
                                                    </div>--}}
                                                    <small> <b>PT File Headers -</b> bill no, caegory, sub category, brand, style no, color, size, qty, sale rate, mrp, tax(%), tax(Rs), barcode</small> 
                                                </div>
                                        
                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row import_csv">
                            <div id="show_pt_file_data"></div>
                        </div>
                        
                        <div class="row direct_entry">

                            <div class="div col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-6">
                                                <b>Details</b>
                                            </div>
                                            <div class="col-6">
                                                <div class="card-tools">
                                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end ">
                                                        <button type="button" class="btn  btn-sm" data-card-widget="collapse" title="Collapse" style="background-color: #ABEBC6;">
                                                          <i class="fas fa-minus"></i>
                                                        </button>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                           
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive" style="height: 320px;" >
                                        <table class="table table-head-fixed text-nowrap" id="show_purchase_entry">
                                            
                                        </table>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <div class="row">

                                            <div class="col-md-5"><b>Total Qty : <span id="show_total_qty"></span></b> </div>
                                            <div class="col-md-7"><b>Total Value : <span id="show_total_value"></span></b> </div>
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>

                            <div class="col-md-8">
                                {{-- <div class="card"> --}}
                                    
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-6">
                                                    <b>Product</b>
                                                </div>
                                                {{-- <div class="col-6 d-flex justify-content-end">
                                                    <button class="btn btn-primary btn-sm"  id="addItemBtn"> Add New</button>
                                                </div> --}}
                                            </div>
                                        </div>
                                        <div class="card-body" id="product_section">
                                           <div class="row item_list" id="item_list">

                                            <div class="row">

                                                <div class="col-md-3">

                                                    <div class="input-group">
                                                        <select id="color" name="color" data-placeholder='Select color' class="form-select form-select-sm color_code select_chosen_70">
                                                            <option selected disabled >Color</option>
                                                            @foreach ($colors as $list)
                                                            <option value="{{$list->color}}">{{ucwords($list->color)}}</option>
                                                            @endforeach
                                                        </select>
                                                        <span class="input-group-text" style=" padding: 3px 5px 3px 5px;">
                                                            <i class="fas fa-plus cursor_pointer" id="addNewColorBtn"></i>
                                                        </span>
                                                    </div>

                                                    <div id="take_photo" class="take_photo mt-2" >
                                                        {{-- <img class="card-img-top img-thumbnail after_capture_frame" src="{{asset('public/assets/images/user-img.jpg')}}" style="width: 60px; height:60px;" /> --}}
                                                        {{-- <img class="card-img-top img-thumbnail after_capture_frame" src="" style="width: 60px; height:60px;" /> --}}
                                                    </div>                                
                                                    <input type="hidden" name="product_image" id="product_image" class="product_image" value="">
                                                    <div class="d-grid gap-2 mt-2">
                                                        <button class="btn btn-primary btn-sm captureLivePhotoBtn" type="button">Live Camera</button>
                                                    </div>
                                                    <div class="mt-1 mb-1 text-center">
                                                        <span>OR</span>                                                        
                                                    </div>
                                                    <div >
                                                        <input class="form-control form-control-sm" id="formFileSm" type="file" >
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-9">
                                        
                                                    <div class="card ">
                                                        <div class="card-body table-responsive" style="overflow-x: scroll;">
                                                            <table class="table" id="show_size" >
                                                                
                                                            </table>
                                                        </div>
                                                    </div>
                                                        
                                                </div>
                                        
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="card card-body" >
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <small  ><b>Qty</b> </small>
                                                        <input type="text" name="" id="total_qty" class="form-control form-control-sm" placeholder="QTY" readonly disabled>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small  ><b>Value</b> </small>
                                                        <input type="text" name="" id="total_price" class="form-control form-control-sm" placeholder="Value" readonly disabled>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <small  ><b>Discount</b> </small>
                                                        <input type="text" name="discount" id="discount" class="form-control form-control-sm" placeholder="Discount" value="0">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small  ><b>Taxable</b> </small>
                                                        <input type="text" name="" id="taxable" class="form-control form-control-sm" placeholder="Taxable" value="0" readonly disabled>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <small  ><b>SGST</b> </small>
                                                        <input type="text" name="" id="total_sgst" class="form-control form-control-sm sgst" placeholder="SGST" value="0"  readonly disabled>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small  ><b>CGST</b> </small>
                                                        <input type="text" name="" id="total_cgst" class="form-control form-control-sm cgst" placeholder="CGST" value="0" readonly disabled>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small ><b>IGST</b> </small>
                                                        <input type="text" name="" id="total_igst" class="form-control form-control-sm igst" placeholder="IGST" value="0" readonly disabled>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small ><b>Amount</b> </small>
                                                        <input type="text" name="" id="total_amount" class="form-control form-control-sm total_amount" placeholder="Amount" readonly disabled>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <input type="hidden" name="size_type_id" id="size_type_id">
                                    <input type="hidden" name="purchase_id" id="purchase_id">
                                    <input type="hidden" name="purchase_entry_id" id="purchase_entry_id">

                                    <div class="card-footer ">
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end ">
                                            @if (session('LOGIN_ROLE') != MyApp::BARCODE)
                                                <button type="button" id="savePurchaseEntryBtn" class="btn btn-primary btn-sm ">Save </button>
                                                <button type="button" id="updatePurchaseEntryBtn" class="btn btn-primary btn-sm hide">Update</button>
                                            @endif
                                        </div>
                                    </div>
                                        
                                </div>
                                {{-- </div> --}}
                                
                            </div>
                            

                        </div >

                    </form >
                
            </div>
        </div>
    </div>
</div>

<div class="hide">

    <table class="table" id="normal_size_type" >
        <tbody >
            <tr >
                <th>Size</th>
                <td>F/R </td>
                <td>XS </td>
                <td>S </td>
                <td>M </td>
                <td>L </td>
                <td>XL </td>
                <td>XXL </td>
                <td>3XL </td>
                <td>4XL </td>
                <td>5XL </td>
                <td>6XL </td>
               
            </tr>
            <tr>
                <th>Qty</th>
                <td><input type="text" id="fr_qty" name="fr_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="xs_qty" name="xs_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="s_qty" name="s_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="m_qty" name="m_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="l_qty" name="l_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="xl_qty" name="xl_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="xxl_qty" name="xxl_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="three_xl_qty" name="three_xl_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="four_xl_qty" name="four_xl_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="five_xl_qty" name="five_xl_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="six_xl_qty" name="six_xl_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                
            </tr>
            <tr>
                <th>Price</th>
                <td><input type="text" rel="popover" id="fr_price" name="fr_price" class="form-control form-control-sm fr_price price example-popover" placeholder="Price" ></td>
                <td><input type="text" rel="popover" id="xs_price" name="xs_price" class="form-control form-control-sm xs_price price example-popover" placeholder="Price" ></td>
                <td><input type="text" rel="popover" id="s_price" name="s_price" class="form-control form-control-sm s_price price" placeholder="Price"  ></td>
                <td><input type="text" rel="popover" id="m_price" name="m_price" class="form-control form-control-sm m_price price" placeholder="Price"  ></td>
                <td><input type="text" rel="popover" id="l_price" name="l_price" class="form-control form-control-sm l_price price" placeholder="Price"  ></td>
                <td><input type="text" rel="popover" id="xl_price" name="xl_price" class="form-control form-control-sm xl_price price" placeholder="Price"  ></td>
                <td><input type="text" rel="popover" id="xxl_price" name="xxl_price" class="form-control form-control-sm xxl_price price" placeholder="Price" ></td>
                <td><input type="text" rel="popover" id="three_xl_price" name="three_xl_price" class="form-control form-control-sm three_xl_price price" placeholder="Price"></td>
                <td><input type="text" rel="popover" id="four_xl_price" name="four_xl_price" class="form-control form-control-sm four_xl_price price" placeholder="Price"></td>
                <td><input type="text" rel="popover" id="five_xl_price" name="five_xl_price" class="form-control form-control-sm five_xl_price price" placeholder="Price"></td>
                <td><input type="text" rel="popover" id="six_xl_price" name="six_xl_price" class="form-control form-control-sm six_xl_price price" placeholder="Price"></td>
            </tr>
            <tr>
                <th>MRP</th>
                <td><input type="text" id="fr_mrp" name="fr_mrp" class="form-control form-control-sm fr_mrp mrp" placeholder="MRP" value=""></td>
                <td><input type="text" id="xs_mrp" name="xs_mrp" class="form-control form-control-sm xs_mrp mrp" placeholder="MRP" value=""></td>
                <td ><input type="text" id="s_mrp" name="s_mrp" class="form-control form-control-sm s_mrp mrp" placeholder="MRP" value=""></td>
                <td ><input type="text" id="m_mrp" name="m_mrp" class="form-control form-control-sm m_mrp mrp" placeholder="MRP" value=""></td>
                <td ><input type="text" id="l_mrp" name="l_mrp" class="form-control form-control-sm l_mrp mrp" placeholder="MRP" value=""></td>
                <td ><input type="text" id="xl_mrp" name="xl_mrp" class="form-control form-control-sm xl_mrp mrp" placeholder="MRP" value=""></td>
                <td ><input type="text" id="xxl_mrp" name="xxl_mrp" class="form-control form-control-sm xxl_mrp mrp" placeholder="MRP" value=""></td>
                <td><input type="text" id="three_xl_mrp" name="three_xl_mrp" class="form-control form-control-sm three_xl_mrp mrp" placeholder="MRP"></td>
                <td><input type="text" id="four_xl_mrp" name="four_xl_mrp" class="form-control form-control-sm four_xl_mrp mrp" placeholder="MRP"></td>
                <td><input type="text" id="five_xl_mrp" name="five_xl_mrp" class="form-control form-control-sm five_xl_mrp mrp" placeholder="MRP"></td>
                <td><input type="text" id="six_xl_mrp" name="six_xl_mrp" class="form-control form-control-sm six_xl_mrp mrp" placeholder="MRP"></td>
            </tr>
            
        </tbody>
    </table>

    <table class="table" id="kids_size_type" style="width: 650px;">
        <tbody>
            <tr >
                <th>Size</th>
                <td>18</td>
                <td>20</td>
                <td>22</td>
                <td>24</td>
                <td>26</td>
                <td>28</td>
                <td>30</td>
                <td>32</td>
                <td>34</td>
                <td>36</td>
            </tr>
            <tr>
                <th>Qty</th>
                <td><input type="text" id="k_18_qty" name="k_18_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_20_qty" name="k_20_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_22_qty" name="k_22_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_24_qty" name="k_24_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_26_qty" name="k_26_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_28_qty" name="k_28_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_30_qty" name="k_30_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_32_qty" name="k_32_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_34_qty" name="k_34_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="k_36_qty" name="k_36_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                
            </tr>
            <tr>
                <th>Price</th>
                <td><input type="text" id="k_18_price" name="k_18_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_20_price" name="k_20_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_22_price" name="k_22_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_24_price" name="k_24_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_26_price" name="k_26_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_28_price" name="k_28_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_30_price" name="k_30_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_32_price" name="k_32_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_34_price" name="k_34_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="k_36_price" name="k_36_price" class="form-control form-control-sm price" placeholder="Price"></td>
            </tr>
            <tr>
                <th>MRP</th>
                <td><input type="text" id="k_18_mrp" name="k_18_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_20_mrp" name="k_20_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_22_mrp" name="k_22_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_24_mrp" name="k_24_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_26_mrp" name="k_26_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_28_mrp" name="k_28_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_30_mrp" name="k_30_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_32_mrp" name="k_32_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_34_mrp" name="k_34_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
                <td><input type="text" id="k_36_mrp" name="k_36_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>
            </tr>
            
        </tbody>
    </table>

    <table class="table" id="without_size_type">
        <tbody>
            <tr >
                <th>Qty</th>
                <th>Price</th>
                <th>MRP</th>
            </tr>
            <tr>
                <td><input type="text" id="without_qty" name="without_qty" class="form-control form-control-sm qty" placeholder="Qty"></td>
                <td><input type="text" id="without_price" name="without_price" class="form-control form-control-sm price" placeholder="Price"></td>
                <td><input type="text" id="without_mrp" name="without_mrp" class="form-control form-control-sm mrp" placeholder="MRP"></td>                
            </tr>
        </tbody>
    </table>

</div>

{{-- delete purchase entery item wise modal --}}
<div class="modal fade" id="deletePurchaseEntryItemModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> Delete Purchase Entry Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <center>
                    <h5>Are you sure?</h5>
                        <button type="button" id="yesPurchaseEntryItemBtn" class="btn btn-primary btn-sm mx-1 ">Yes</button>
                        <button type="button" class="btn btn-secondary mx-1 btn-sm" data-bs-dismiss="modal">No</button>
                    <hr>
                </center>
            </div>
        </div>
    </div>
</div>

{{-- delete purchase entry style wise modal --}}
<div class="modal fade" id="deletePurchaseEntryStyleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> Delete Purchase Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <center>
                    <h5>Are you sure?</h5>
                        <button type="button" id="yesPurchaseEntryItemStyleBtn" class="btn btn-primary btn-sm mx-1 ">Yes</button>
                        <button type="button" class="btn btn-secondary mx-1 btn-sm" data-bs-dismiss="modal">No</button>
                    <hr>
                </center>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> Delete User </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <center>
                    <h5>Are you sure?</h5>
                        <button type="button" id="yesDeleteProductBtn" class="btn btn-primary btn-sm mx-1 ">Yes</button>
                        <button type="button" class="btn btn-secondary mx-1 btn-sm" data-bs-dismiss="modal">No</button>
                    <hr>
                </center>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="captureLivePhotoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Take Live Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="my_camera" class="card pre_capture_frame" ></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm float-end" onClick="takePhoto()">Take</button>
            </div>
        </div>
    </div>
</div>  

{{-- <div class="modal fade" id="generatePurchaseInvoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Purchase Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id='print_invoice'>
                <div id="show_purchase_invoice"> </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm float-end " id="printPurchaseInvoice" print-section='print_invoice'>Print</button>
            </div>
        </div>
    </div>
</div> --}}


<section>
    <div id="newcontent">
        <div class="modal fade" id="generatePurchaseInvoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

        </div>
    </div>
</section>

{{-- barcode modal --}}
<section>

    <div class="modal fade" id="barcodeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><b>Barcodes</b></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="view_barcode"> </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm float-end print" print-section='print_barcode'>Print</button>
                </div>
            </div>
        </div>
    </div>  
    
</section>

{{-- excel data entry modal start --}}

<!-- Button trigger modal -->

  <!-- Modal -->
<div class="modal fade" id="purchaseExcelModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Excel Purchase Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- <form id="purchaseExcelEntryForm" action="{{url('admin/export-excel-data')}}" method="post" enctype="multipart/form-data"> --}}
            <div class="modal-body">
                <form id="purchaseExcelEntryForm" >
                    @csrf
                    <div id="purchase_entry_err"></div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12" id="supplier_div">
                                    {{-- <select id="supplier_id" name="supplier_id" class="form-select form-select-sm" onchange="supplierDetail(this.value);"> --}}
                                    <select id="supplier_id" name="supplier_id" class="form-select form-select-sm">
                                        <option selected disabled value="0">Supplier</option>                                          
                                        @foreach ($suppliers as $list)
                                        <option value="{{$list->id}}" state-type="{{$list->state_type}}"> {{ucwords($list->supplier_name)}} </option>
                                        @endforeach
                                    </select>  

                                </div>
                                
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <textarea class="form-control" id="supplier_address" style="height: 70px;"  placeholder="Address" disabled readonly></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="supplier_code" id="supplier_code" class="form-control form-control-sm" placeholder="Supplier Code" readonly disabled>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-12">
                                    <input type="text"  name="gst_no"  id="gst_no" class="form-control form-control-sm" placeholder="GSTIN" readonly disabled>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-12">
                                    <input type="text"  name="payment_days"  id="payment_days" class="form-control form-control-sm" placeholder="Payment Days">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <input type="file"name="file" id="file" class="form-control form-control-sm">
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-sm mt-1" id="savePurchaseEntryExcelBtn">Save</button>
            </div>

        </div>
    </div>
</div>





{{-- <div class='accordion accordion-flush' id='accordionFlushExample'>
    <table class='table table-striped'>
        <thead>
            <tr style='position: sticky;z-index: 1;'>
                <th>SN</th>
                <th>Style</th>
                <th>Color</th>
                
            </tr>
        </thead>
        <tbody >
            @php
               $count = 0; 
               $orders = 5;
               @endphp
               @for ($i = 0; $i < $orders; $i++)
                   
               
                <tr class='accordion-button collapsed' data-bs-toggle='collapse' data-bs-target='#collapse_{{$i}}' aria-expanded='false' aria-controls='flush-collapseOne'>
                    
                    <td>sfjsf</td>
                    <td>sfjsf</td>
                    <td>sfjsf</td>
                    
                </tr> 
                <tr>
                    <td colspan='3'>
                        <div id='collapse_{{$i}}' class='accordion-collapse collapse' aria-labelledby='flush-headingOne' data-bs-parent='#accordionFlushExample'>
                            <div class='accordion-body'>
                                <table class="table table-striped table-hover ">
                                    <thead>
                                        <tr>
                                            <th> SN</th>
                                            <th> Size</th>
                                            <th> Qty</th>
                                            <th> Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>XXL</td>
                                            <td>5</td>
                                            <td>1299</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                @endfor                                               
            </tbody>
    </table>  
</div> --}}

