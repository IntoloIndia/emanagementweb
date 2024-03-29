<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Purchase;
use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\StyleNo;
use App\Models\Size;
use App\Models\Color;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\Offer;
use App\Models\ApplyOffer;
use App\Models\BusinessDetails;
use App\Models\User;
use App\Models\Role;
use App\MyApp;

use Validator;
use Picqer;
use DNS1D;
use DNS2D;
use QrCode;

use App\Imports\ImportProduct;
use App\Exports\ExportProduct;

use Maatwebsite\Excel\Facades\Excel;


class PurchaseEntryController extends Controller
{
    
    public function index()
    {
        $categories = Category::all();
        $sizes = Size::all();
        $colors = Color::all();
        $brands = Brand::all();
        $suppliers = Supplier::all();

        //  DNS2D::getBarcodeHTML('4445645656', 'QRCODE');

        // $barcode = 'data:image/png;base64,' . DNS2D::getBarcodePNG('4', 'PDF417')  ;
        // return $barcode;
        // $product_code = rand(0000000001,9999999999);
        // // $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
        //$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
        // // $barcode = $generator->getBarcode($product_code, $generator::TYPE_CODE_128, 3, 40);
        //$barcode = '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode('081231723897', $generator::TYPE_CODE_128)) . '">';
        //return $barcode;



        $purchases = Purchase::Join('suppliers','suppliers.id','=','purchases.supplier_id')
                // ->orderBy('purchases.bill_date', 'desc')
                ->orderBy('purchases.id', 'desc')
                ->get(['purchases.*',
                    'suppliers.supplier_name',
                ]);


        return view('purchase.index',[
            'suppliers' => $suppliers,
            "categories"=>$categories,
            'sizes' => $sizes,
            'colors'=> $colors,
            'brands'=> $brands,
            'purchases' => $purchases,
        ]);
    }

    public function savePurchaseEntry(Request $req)
    {
        if ($req->size_type_id == MyApp::NORMAL_SIZE) {
            $result = $this->purchaseEntryNormalSizeValidation($req);
        }elseif ($req->size_type_id == MyApp::KIDS_SIZE) {
            $result = $this->purchaseEntryKidsSizeValidation($req);
        }elseif ($req->size_type_id == MyApp::WITHOUT_SIZE) {
            $result = $this->purchaseEntryWithoutSizeValidation($req);
        }


        if ($result['status'] == 400) {
            return response()->json([   
                'status'=>$result['status'],
                'errors'=>$result['errors'],
            ]);
        }

        $purchase_id = 0;
        $supplier_id = $req->input('supplier_id');
        $bill_no = $req->input('bill_no');

        $data = Purchase::where(['supplier_id'=>$supplier_id,'bill_no'=>$bill_no])->first('id');
        
        if ($data == null) {
            $model = new Purchase;
            
            $model->supplier_id = $supplier_id;
            $model->bill_no = $bill_no;
            $model->bill_date = $req->input('bill_date');
            $model->payment_days = $req->input('payment_days');
            $model->time = date('g:i A');
            $model->save();
            
            $purchase_id = $model->id;
        }else{
            $purchase_id = $data->id;
        }
            
        $category_id = $req->input('category_id');
        $sub_category_id = $req->input('sub_category_id');
        $brand_id = $req->input('brand_id');
        $style_no_id = $req->input('style_no_id');
        $color = $req->input('color');
        $product_image = $req->input('product_image');
        
        $purchase_entry_data = PurchaseEntry::where(['purchase_id'=>$purchase_id,'style_no_id'=>$style_no_id, 'color'=>$color])->first('id');
        $purchase_entry_id = 0;
        if ($purchase_entry_data == null) {
            $purchase_entry = new PurchaseEntry;

            $purchase_entry->purchase_id = $purchase_id;
            $purchase_entry->category_id = $category_id;
            $purchase_entry->sub_category_id = $sub_category_id;
            $purchase_entry->brand_id = $brand_id;
            $purchase_entry->style_no_id = $style_no_id;
            $purchase_entry->color = $color;
            if ($product_image) {
                $purchase_entry->img = $product_image;
            }

            $purchase_entry->save();

            $purchase_entry_id = $purchase_entry->id;
        }else{
            $purchase_entry_id = $purchase_entry_data->id;
        }


        if ($req->size_type_id == MyApp::NORMAL_SIZE) {
            $result = $this->saveNormalSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id);
        }elseif ($req->size_type_id == MyApp::KIDS_SIZE) {
            $result = $this->saveKidsSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id);
        }elseif ($req->size_type_id == MyApp::WITHOUT_SIZE) {
            $result = $this->saveWithoutSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id);
        }

        if ($result['status'] == 400) {
            return response()->json([   
                'status'=>$result['status'],
                'errors'=>$result['errors'],
            ]);
        }else{
            return response()->json([   
                'status'=>$result['status'],
                'html'=>$result['html'],
            ]);
        }

    }

    public function purchaseEntryNormalSizeValidation($req)
    {
        if( ( $req->input('fr_qty') == "") && ( $req->input('xs_qty') == "") && ($req->input('s_qty') == "") && ($req->input('m_qty') == "") && ($req->input('l_qty') == "") && ($req->input('xl_qty') == "") && ($req->input('xxl_qty') == "") && ($req->input('three_xl_qty') == "") && ($req->input('four_xl_qty') == "") && ($req->input('five_xl_qty') == "") && ($req->input('six_xl_qty') == "")){

            return [
                'status'=>400,
                'errors'=>['Please enter atleast 1 product detail'],
            ];
        }

        if($req->input('fr_qty') == "" &&  $req->input('fr_price') == "" && $req->input('fr_mrp') == "")
        {
            $fr_qty_validation = '';
            $fr_price_validation = '';
            $fr_mrp_validation = '';
        }else{
            $fr_qty_validation = 'required';
            $fr_price_validation = 'required';
            $fr_mrp_validation = 'required';
        }

        if($req->input('xs_qty') == "" &&  $req->input('xs_price') == "" && $req->input('xs_mrp') == "")
        {
            $xs_qty_validation = '';
            $xs_price_validation = '';
            $xs_mrp_validation = '';
        }else{
            $xs_qty_validation = 'required';
            $xs_price_validation = 'required';
            $xs_mrp_validation = 'required';
        }

        if($req->input('s_qty') == "" &&  $req->input('s_price') == "" && $req->input('s_mrp') == "")
        {
            $s_qty_validation = '';
            $s_price_validation = '';
            $s_mrp_validation = '';
        }else{
            $s_qty_validation = 'required';
            $s_price_validation = 'required';
            $s_mrp_validation = 'required';
        }

        if($req->input('m_qty') == "" &&  $req->input('m_price') == "" && $req->input('m_mrp') == "")
        {
            $m_qty_validation = '';
            $m_price_validation = '';
            $m_mrp_validation = '';
        }else{
            $m_qty_validation = 'required';
            $m_price_validation = 'required';
            $m_mrp_validation = 'required';
        }
        if($req->input('l_qty') == "" &&  $req->input('l_price') == "" && $req->input('l_mrp') == "")
        {
            $l_qty_validation = '';
            $l_price_validation = '';
            $l_mrp_validation = '';
        }else{
            $l_qty_validation = 'required';
            $l_price_validation = 'required';
            $l_mrp_validation = 'required';
        }
        if($req->input('xl_qty') == "" &&  $req->input('xl_price') == "" && $req->input('xl_mrp') == "")
        {
            $xl_qty_validation = '';
            $xl_price_validation = '';
            $xl_mrp_validation = '';
        }else{
            $xl_qty_validation = 'required';
            $xl_price_validation = 'required';
            $xl_mrp_validation = 'required';
        }
        if($req->input('xxl_qty') == "" &&  $req->input('xxl_price') == "" && $req->input('xxl_mrp') == "")
        {
            $xxl_qty_validation = '';
            $xxl_price_validation = '';
            $xxl_mrp_validation = '';
        }else{
            $xxl_qty_validation = 'required';
            $xxl_price_validation = 'required';
            $xxl_mrp_validation = 'required';
        }

        if($req->input('three_xl_qty') == "" &&  $req->input('three_xl_price') == "" && $req->input('three_xl_mrp') == "")
        {
            $three_xl_qty_validation = '';
            $three_xl_price_validation = '';
            $three_xl_mrp_validation = '';
        }else{
            $three_xl_qty_validation = 'required';
            $three_xl_price_validation = 'required';
            $three_xl_mrp_validation = 'required';
        }

        if($req->input('four_xl_qty') == "" &&  $req->input('four_xl_price') == "" && $req->input('four_xl_mrp') == "")
        {
            $four_xl_qty_validation = '';
            $four_xl_price_validation = '';
            $four_xl_mrp_validation = '';
        }else{
            $four_xl_qty_validation = 'required';
            $four_xl_price_validation = 'required';
            $four_xl_mrp_validation = 'required';
        }

        if($req->input('five_xl_qty') == "" &&  $req->input('five_xl_price') == "" && $req->input('five_xl_mrp') == "")
        {
            $five_xl_qty_validation = '';
            $five_xl_price_validation = '';
            $five_xl_mrp_validation = '';
        }else{
            $five_xl_qty_validation = 'required';
            $five_xl_price_validation = 'required';
            $five_xl_mrp_validation = 'required';
        }

        if($req->input('six_xl_qty') == "" &&  $req->input('six_xl_price') == "" && $req->input('six_xl_mrp') == "")
        {
            $six_xl_qty_validation = '';
            $six_xl_price_validation = '';
            $six_xl_mrp_validation = '';
        }else{
            $six_xl_qty_validation = 'required';
            $six_xl_price_validation = 'required';
            $six_xl_mrp_validation = 'required';
        }

        $validator = Validator::make($req->all(),[
            'supplier_id' => 'required|max:191',
            'bill_no'=>'required|max:191',
            // 'bill_no'=>'required|unique:purchase_entries,bill_no,'.$req->input('bill_no'),
            'bill_date'=>'required|max:191',
            'payment_days'=>'required|max:191',
            'category_id'=>'required|max:191',
            'sub_category_id'=>'required|max:191',
            'brand_id'=>'required|max:191',
            'style_no_id'=>'required|max:191',
            'color'=>'required|max:191',
            'fr_qty'=>$fr_qty_validation,
            'fr_price'=>$fr_price_validation,
            'fr_mrp'=>$fr_mrp_validation,
            'xs_qty'=>$xs_qty_validation,
            'xs_price'=>$xs_price_validation,
            'xs_mrp'=>$xs_mrp_validation,
            's_qty'=>$s_qty_validation,
            's_price'=>$s_price_validation,
            's_mrp'=>$s_mrp_validation,
            'm_qty'=>$m_qty_validation,
            'm_price'=>$m_price_validation,
            'm_mrp'=>$m_mrp_validation,
            'l_qty'=>$l_qty_validation,
            'l_price'=>$l_price_validation,
            'l_mrp'=>$l_mrp_validation,
            'xl_qty'=>$xl_qty_validation,
            'xl_price'=>$xl_price_validation,
            'xl_mrp'=>$xl_mrp_validation,
            'xxl_qty'=>$xxl_qty_validation,
            'xxl_price'=>$xxl_price_validation,
            'xxl_mrp'=>$xxl_mrp_validation,
            'three_xl_qty'=>$three_xl_qty_validation,
            'three_xl_price'=>$three_xl_price_validation,
            'three_xl_mrp'=>$three_xl_mrp_validation,
            'four_xl_qty'=>$four_xl_qty_validation,
            'four_xl_price'=>$four_xl_price_validation,
            'four_xl_mrp'=>$four_xl_mrp_validation,
            'five_xl_qty'=>$five_xl_qty_validation,
            'five_xl_price'=>$five_xl_price_validation,
            'five_xl_mrp'=>$five_xl_mrp_validation,
            'six_xl_qty'=>$six_xl_qty_validation,
            'six_xl_price'=>$six_xl_price_validation,
            'six_xl_mrp'=>$six_xl_mrp_validation,
        ]);

        if($validator->fails())
        {
            return [
                'status'=>400,
                'errors'=>$validator->messages(),
            ];
        }else{
            return [
                'status'=>200,
            ];
        }

    }

    public function purchaseEntryKidsSizeValidation($req)
    {
        if(( $req->input('k_18_qty') == "") && ($req->input('k_20_qty') == "") && ($req->input('k_22_qty') == "") && ($req->input('k_24_qty') == "") && ($req->input('k_26_qty') == "") && ($req->input('k_28_qty') == "") && ($req->input('k_30_qty') == "") && ($req->input('k_32_qty') == "") && ($req->input('k_34_qty') == "") && ($req->input('k_36_qty') == "") ){
            
            return [
                'status'=>400,
                'errors'=>['Please enter atleast 1 product detail'],
            ];
        }

        if($req->input('k_18_qty') == "" &&  $req->input('k_18_price') == "" && $req->input('k_18_mrp') == "")
        {
            $k_18_qty_validation = '';
            $k_18_price_validation = '';
            $k_18_mrp_validation = '';
        }else{
            $k_18_qty_validation = 'required';
            $k_18_price_validation = 'required';
            $k_18_mrp_validation = 'required';
        }

        if($req->input('k_20_qty') == "" &&  $req->input('k_20_price') == "" && $req->input('k_20_mrp') == "")
        {
            $k_20_qty_validation = '';
            $k_20_price_validation = '';
            $k_20_mrp_validation = '';
        }else{
            $k_20_qty_validation = 'required';
            $k_20_price_validation = 'required';
            $k_20_mrp_validation = 'required';
        }

        if($req->input('k_22_qty') == "" &&  $req->input('k_22_price') == "" && $req->input('k_22_mrp') == "")
        {
            $k_22_qty_validation = '';
            $k_22_price_validation = '';
            $k_22_mrp_validation = '';
        }else{
            $k_22_qty_validation = 'required';
            $k_22_price_validation = 'required';
            $k_22_mrp_validation = 'required';
        }
        if($req->input('k_24_qty') == "" &&  $req->input('k_24_price') == "" && $req->input('k_24_mrp') == "")
        {
            $k_24_qty_validation = '';
            $k_24_price_validation = '';
            $k_24_mrp_validation = '';
        }else{
            $k_24_qty_validation = 'required';
            $k_24_price_validation = 'required';
            $k_24_mrp_validation = 'required';
        }
        if($req->input('k_26_qty') == "" &&  $req->input('k_26_price') == "" && $req->input('k_26_mrp') == "")
        {
            $k_26_qty_validation = '';
            $k_26_price_validation = '';
            $k_26_mrp_validation = '';
        }else{
            $k_26_qty_validation = 'required';
            $k_26_price_validation = 'required';
            $k_26_mrp_validation = 'required';
        }
        if($req->input('k_28_qty') == "" &&  $req->input('k_28_price') == "" && $req->input('k_28_mrp') == "")
        {
            $k_28_qty_validation = '';
            $k_28_price_validation = '';
            $k_28_mrp_validation = '';
        }else{
            $k_28_qty_validation = 'required';
            $k_28_price_validation = 'required';
            $k_28_mrp_validation = 'required';
        }

        if($req->input('k_30_qty') == "" &&  $req->input('k_30_price') == "" && $req->input('k_30_mrp') == "")
        {
            $k_30_qty_validation = '';
            $k_30_price_validation = '';
            $k_30_mrp_validation = '';
        }else{
            $k_30_qty_validation = 'required';
            $k_30_price_validation = 'required';
            $k_30_mrp_validation = 'required';
        }

        if($req->input('k_32_qty') == "" &&  $req->input('k_32_price') == "" && $req->input('k_32_mrp') == "")
        {
            $k_32_qty_validation = '';
            $k_32_price_validation = '';
            $k_32_mrp_validation = '';
        }else{
            $k_32_qty_validation = 'required';
            $k_32_price_validation = 'required';
            $k_32_mrp_validation = 'required';
        }

        if($req->input('k_34_qty') == "" &&  $req->input('k_34_price') == "" && $req->input('k_34_mrp') == "")
        {
            $k_34_qty_validation = '';
            $k_34_price_validation = '';
            $k_34_mrp_validation = '';
        }else{
            $k_34_qty_validation = 'required';
            $k_34_price_validation = 'required';
            $k_34_mrp_validation = 'required';
        }

        if($req->input('k_36_qty') == "" &&  $req->input('k_36_price') == "" && $req->input('k_36_mrp') == "")
        {
            $k_36_qty_validation = '';
            $k_36_price_validation = '';
            $k_36_mrp_validation = '';
        }else{
            $k_36_qty_validation = 'required';
            $k_36_price_validation = 'required';
            $k_36_mrp_validation = 'required';
        }

        $validator = Validator::make($req->all(),[
            'supplier_id' => 'required|max:191',
            'bill_no'=>'required|max:191',
            // 'bill_no'=>'required|unique:purchase_entries,bill_no,'.$req->input('bill_no'),
            'bill_date'=>'required|max:191',
            'payment_days'=>'required|max:191',
            'category_id'=>'required|max:191',
            'sub_category_id'=>'required|max:191',
            'brand_id'=>'required|max:191',
            'style_no_id'=>'required|max:191',
            'color'=>'required|max:191',
            'k_18_qty'=>$k_18_qty_validation,
            'k_18_price'=>$k_18_price_validation,
            'k_18_mrp'=>$k_18_mrp_validation,
            'k_20_qty'=>$k_20_qty_validation,
            'k_20_price'=>$k_20_price_validation,
            'k_20_mrp'=>$k_20_mrp_validation,
            'k_22_qty'=>$k_22_qty_validation,
            'k_22_price'=>$k_22_price_validation,
            'k_22_mrp'=>$k_22_mrp_validation,
            'k_24_qty'=>$k_24_qty_validation,
            'k_24_price'=>$k_24_price_validation,
            'k_24_mrp'=>$k_24_mrp_validation,
            'k_26_qty'=>$k_26_qty_validation,
            'k_26_price'=>$k_26_price_validation,
            'k_26_mrp'=>$k_26_mrp_validation,
            'k_28_qty'=>$k_28_qty_validation,
            'k_28_price'=>$k_28_price_validation,
            'k_28_mrp'=>$k_28_mrp_validation,
            'k_30_qty'=>$k_30_qty_validation,
            'k_30_price'=>$k_30_price_validation,
            'k_30_mrp'=>$k_30_mrp_validation,
            'k_32_qty'=>$k_32_qty_validation,
            'k_32_price'=>$k_32_price_validation,
            'k_32_mrp'=>$k_32_mrp_validation,
            'k_34_qty'=>$k_34_qty_validation,
            'k_34_price'=>$k_34_price_validation,
            'k_34_mrp'=>$k_34_mrp_validation,
            'k_36_qty'=>$k_36_qty_validation,
            'k_36_price'=>$k_36_price_validation,
            'k_36_mrp'=>$k_36_mrp_validation,
        ]);

        if($validator->fails())
        {
            return [
                'status'=>400,
                'errors'=>$validator->messages(),
            ];
        }else{
            return [
                'status'=>200,
            ];
        }
    }

    public function purchaseEntryWithoutSizeValidation($req)
    {
        if(( $req->input('without_qty') == "") ){
            return [
                'status'=>400,
                'errors'=>['Please enter atleast 1 product detail'],
            ];
        }

        if($req->input('without_qty') == "" &&  $req->input('without_price') == "" && $req->input('without_mrp') == "")
        {
            $without_qty_validation = '';
            $without_price_validation = '';
            $without_mrp_validation = '';
        }else{
            $without_qty_validation = 'required';
            $without_price_validation = 'required';
            $without_mrp_validation = 'required';
        }

        $validator = Validator::make($req->all(),[
            'supplier_id' => 'required|max:191',
            'bill_no'=>'required|max:191',
            // 'bill_no'=>'required|unique:purchase_entries,bill_no,'.$req->input('bill_no'),
            'bill_date'=>'required|max:191',
            'payment_days'=>'required|max:191',
            'category_id'=>'required|max:191',
            'sub_category_id'=>'required|max:191',
            'brand_id'=>'required|max:191',
            'style_no_id'=>'required|max:191',
            'color'=>'required|max:191',
            'without_qty'=>$without_qty_validation,
            'without_price'=>$without_price_validation,
            'without_mrp'=>$without_mrp_validation,
        ]);

        if($validator->fails())
        {
            return [
                'status'=>400,
                'errors'=>$validator->messages(),
            ];
        }else{
            return [
                'status'=>200,
            ];
        }
    }

    public function saveNormalSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id)
    {
        $fr_qty = $req->input('fr_qty');
        $xs_qty = $req->input('xs_qty');
        $s_qty = $req->input('s_qty');
        $m_qty = $req->input('m_qty');
        $l_qty = $req->input('l_qty');
        $xl_qty = $req->input('xl_qty');
        $xxl_qty = $req->input('xxl_qty');
        $three_xl_qty = $req->input('three_xl_qty');
        $four_xl_qty = $req->input('four_xl_qty');
        $five_xl_qty = $req->input('five_xl_qty');
        $six_xl_qty = $req->input('six_xl_qty');

        $fr_price = $req->input('fr_price');
        $xs_price = $req->input('xs_price');
        $s_price = $req->input('s_price');
        $m_price = $req->input('m_price');
        $l_price = $req->input('l_price');
        $xl_price = $req->input('xl_price');
        $xxl_price = $req->input('xxl_price');
        $three_xl_price = $req->input('three_xl_price');
        $four_xl_price = $req->input('four_xl_price');
        $five_xl_price = $req->input('five_xl_price');
        $six_xl_price = $req->input('six_xl_price');

        $fr_mrp = $req->input('fr_mrp');
        $xs_mrp = $req->input('xs_mrp');
        $s_mrp = $req->input('s_mrp');
        $m_mrp = $req->input('m_mrp');
        $l_mrp = $req->input('l_mrp');
        $xl_mrp = $req->input('xl_mrp');
        $xxl_mrp = $req->input('xxl_mrp');
        $three_xl_mrp = $req->input('three_xl_mrp');
        $four_xl_mrp = $req->input('four_xl_mrp');
        $five_xl_mrp = $req->input('five_xl_mrp');
        $six_xl_mrp = $req->input('six_xl_mrp');

        $qty = 0;
        $size = "";
        $price = 0;
        $mrp = 0;
        $discount = 0 ;
        if ($req->input('discount') > 0) {
            $discount = $req->input('discount');
        }

        if ($fr_qty > 0) {
            $qty = $fr_qty;
            $size = 'fr';
            $price = $fr_price;
            $mrp = $fr_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount);                
        }

        if ($xs_qty > 0) {
            $qty = $xs_qty;
            $size = 'xs';
            $price = $xs_price;
            $mrp = $xs_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount);                
        }

        if ($s_qty > 0) {
            $qty = $s_qty;
            $size = 's';
            $price = $s_price;
            $mrp = $s_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount);             
        }

        if ($m_qty > 0) {
            $qty = $m_qty;
            $size = 'm';
            $price = $m_price;
            $mrp = $m_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($l_qty > 0) {
            $qty = $l_qty;
            $size = 'l';
            $price = $l_price;
            $mrp = $l_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($xl_qty > 0) {
            $qty = $xl_qty;
            $size = 'xl';
            $price = $xl_price;
            $mrp = $xl_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($xxl_qty > 0) {
            $qty = $xxl_qty;
            $size = 'xxl';
            $price = $xxl_price;
            $mrp = $xxl_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($three_xl_qty > 0) {
            $qty = $three_xl_qty;
            $size = '3xl';
            $price = $three_xl_price;
            $mrp = $three_xl_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($four_xl_qty > 0) {
            $qty = $four_xl_qty;
            $size = '4xl';
            $price = $four_xl_price;
            $mrp = $four_xl_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($five_xl_qty > 0) {
            $qty = $five_xl_qty;
            $size = '5xl';
            $price = $five_xl_price;
            $mrp = $five_xl_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($six_xl_qty > 0) {
            $qty = $six_xl_qty;
            $size = '6xl';
            $price = $six_xl_price;
            $mrp = $six_xl_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        $purchase_entry_result = $this->getPurchaseEntry($supplier_id, $bill_no);

        if ($purchase_entry_result['status'] == 400) {
            return [
                'status'=>$purchase_entry_result['status'],
                'errors'=>['This product is already exists if you want to change detail go to update product.'],
            ];
        }
        return [   
            'status'=>200,
            'html'=>$purchase_entry_result['html'],
        ];
      
    }

    public function saveKidsSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id)
    {
        $k_18_qty = $req->input('k_18_qty');
        $k_20_qty = $req->input('k_20_qty');
        $k_22_qty = $req->input('k_22_qty');
        $k_24_qty = $req->input('k_24_qty');
        $k_26_qty = $req->input('k_26_qty');
        $k_28_qty = $req->input('k_28_qty');
        $k_30_qty = $req->input('k_30_qty');
        $k_32_qty = $req->input('k_32_qty');
        $k_34_qty = $req->input('k_34_qty');
        $k_36_qty = $req->input('k_36_qty');

        $k_18_price = $req->input('k_18_price');
        $k_20_price = $req->input('k_20_price');
        $k_22_price = $req->input('k_22_price');
        $k_24_price = $req->input('k_24_price');
        $k_26_price = $req->input('k_26_price');
        $k_28_price = $req->input('k_28_price');
        $k_30_price = $req->input('k_30_price');
        $k_32_price = $req->input('k_32_price');
        $k_34_price = $req->input('k_34_price');
        $k_36_price = $req->input('k_36_price');

        $k_18_mrp = $req->input('k_18_mrp');
        $k_20_mrp = $req->input('k_20_mrp');
        $k_22_mrp = $req->input('k_22_mrp');
        $k_24_mrp = $req->input('k_24_mrp');
        $k_26_mrp = $req->input('k_26_mrp');
        $k_28_mrp = $req->input('k_28_mrp');
        $k_30_mrp = $req->input('k_30_mrp');
        $k_32_mrp = $req->input('k_32_mrp');
        $k_34_mrp = $req->input('k_34_mrp');
        $k_36_mrp = $req->input('k_36_mrp');

        $qty = 0;
        $size = "";
        $price = 0;
        $mrp = 0;
        $discount = 0 ;
        if ($req->input('discount') > 0) {
            $discount = $req->input('discount');
        }

        if ($k_18_qty > 0) {
            $qty = $k_18_qty;
            $size = '18';
            $price = $k_18_price;
            $mrp = $k_18_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount);                
        }

        if ($k_20_qty > 0) {
            $qty = $k_20_qty;
            $size = '20';
            $price = $k_20_price;
            $mrp = $k_20_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount);             
        }

        if ($k_22_qty > 0) {
            $qty = $k_22_qty;
            $size = '22';
            $price = $k_22_price;
            $mrp = $k_22_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($k_24_qty > 0) {
            $qty = $k_24_qty;
            $size = '24';
            $price = $k_24_price;
            $mrp = $k_24_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($k_26_qty > 0) {
            $qty = $k_26_qty;
            $size = '26';
            $price = $k_26_price;
            $mrp = $k_26_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($k_28_qty > 0) {
            $qty = $k_28_qty;
            $size = '28';
            $price = $k_28_price;
            $mrp = $k_28_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($k_30_qty > 0) {
            $qty = $k_30_qty;
            $size = '30';
            $price = $k_30_price;
            $mrp = $k_30_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($k_32_qty > 0) {
            $qty = $k_32_qty;
            $size = '32';
            $price = $k_32_price;
            $mrp = $k_32_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($k_34_qty > 0) {
            $qty = $k_34_qty;
            $size = '34';
            $price = $k_34_price;
            $mrp = $k_34_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        if ($k_36_qty > 0) {
            $qty = $k_36_qty;
            $size = '36';
            $price = $k_36_price;
            $mrp = $k_36_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount); 
        }

        $purchase_entry_result = $this->getPurchaseEntry($supplier_id, $bill_no);

        if ($purchase_entry_result['status'] == 400) {
            return [
                'status'=>$purchase_entry_result['status'],
                'errors'=>['This product is already exists if you want to change detail go to update product.'],
            ];
        }
        return [   
            'status'=>200,
            'html'=>$purchase_entry_result['html'],
        ];
      
    }

    public function saveWithoutSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id)
    {
        $without_qty = $req->input('without_qty');
        $without_price = $req->input('without_price');
        $without_mrp = $req->input('without_mrp');

        $qty = 0;
        $size = "";
        $price = 0;
        $mrp = 0;
        $discount = 0 ;
        if ($req->input('discount') > 0) {
            $discount = $req->input('discount');
        }

        if ($without_qty > 0) {
            $qty = $without_qty;
            $size = 0;
            $price = $without_price;
            $mrp = $without_mrp;
            $result = $this->saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount);                
        }

        $purchase_entry_result = $this->getPurchaseEntry($supplier_id, $bill_no);

        if ($purchase_entry_result['status'] == 400) {
            return [
                'status'=>$purchase_entry_result['status'],
                'errors'=>['This product is already exists if you want to change detail go to update product.'],
            ];
        }
        return [   
            'status'=>200,
            'html'=>$purchase_entry_result['html'],
        ];

    }

  
    public function saveItem($supplier_id, $purchase_entry_id, $qty, $size, $price, $mrp, $discount){


        $item_exist = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id, 'size'=>$size])->exists();
        if ($item_exist == true) {
            return $result = [
                'status'=>400
            ];
        }

        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

        $first = rand(001,999);
        $second = rand(001,999);
        $month = date('m');
        $year = date('y');

        $taxable = 0;
        $total_discount_amount = 0;

        // $taxable = calculateDiscount($price, $discount);

        if ($discount > 0) {
           $discount_amount = ($price * $discount) / 100;
           $taxable = ($price - $discount_amount) * $qty;
           $total_discount_amount = ($discount_amount * $qty);
        }else{
            $taxable = $price * $qty;
        }

        $supplier = Supplier::where(['id'=>$supplier_id])->first('state_type');
        
        $gst = calculateGst($supplier->state_type, $taxable);
        $total_gst = $gst['sgst'] + $gst['cgst'] + $gst['igst'];
        $amount = $taxable + $total_gst ;

        $purchase_item = new PurchaseEntryItem;
        $purchase_item->purchase_entry_id = $purchase_entry_id;
        $purchase_item->size = $size;
        $purchase_item->qty = $qty;
        $purchase_item->price = $price;
        $purchase_item->mrp = $mrp;
        $purchase_item->discount = $discount;
        $purchase_item->discount_amount = $total_discount_amount;
        $purchase_item->taxable = $taxable;
        $purchase_item->sgst = $gst['sgst'];
        $purchase_item->cgst = $gst['cgst'];
        $purchase_item->igst = $gst['igst'];
        $purchase_item->amount = $amount;
        

        if ($purchase_item->save()) {
            $model = PurchaseEntryItem::find($purchase_item->id);

            $product_code = $month . $first . $purchase_item->id . $second . $year;               
            $barcode_img = 'data:image/png;base64,' . base64_encode($generator->getBarcode($product_code, $generator::TYPE_CODE_128, 3, 50)) ;
            $model->barcode = $product_code;
            $model->barcode_img = $barcode_img;
            $model->save();

            $stock_type = MyApp::PLUS_MANAGE_STOCK;
            $res = manageStock($stock_type, $purchase_entry_id, $size, $qty);
        
        }
            // $voucher_type = MyApp::PURCHASE_ENTRY;
            // $res = manageStock($voucher_type, $purchase_entry_id, $size, $qty);
            return $result = [
                'status'=>200
            ];
        // return 'ok';
    }

    public function getPurchaseEntry($supplier_id, $bill_no){

        $data = Purchase::where(['supplier_id'=>$supplier_id, 'bill_no'=>$bill_no])->first('id');

        $html = "";
        if ($data == null) {
            $html .="<div class='alert alert-warning text-light my-2' role='alert'>";
                $html .="<span>Purchase entry is not available</span>";
            $html .="</div>";

            return $result = [
                'status'=>200,
                'html'=>$html
            ] ;
        }  

            // $purchase_entry_items = array();
            $purchase_entry = PurchaseEntry::Join('style_nos','style_nos.id','=','purchase_entries.style_no_id')
                // ->Join('categories','categories.id','=','purchase_entries.category_id')
                // ->join('sub_categories','sub_categories.id','=','purchase_entries.sub_category_id')
                ->where('purchase_entries.purchase_id', '=', $data->id)
                ->get(['purchase_entries.*','style_nos.style_no']);


                $html .="<div class='accordion accordion-flush' id='accordionFlushExample'>";
                $html .="<table class='table table-striped'>";
                    $html .="<thead>";
                        $html .="<tr style='position: sticky;z-index: 1;'>";
                            $html .="<th>SN</th>";
                            $html .="<th>Style</th>";
                            $html .="<th>Color</th>";
                            $html .="<th>Qty</th>";
                            $html .="<th>Amount</th>";
                            
                        $html .="</tr>";
                    $html .="</thead>";
                    $html .="<tbody >";
                    $total_qty = 0;
                    $total_value = 0;
                    foreach ($purchase_entry as $key => $list) {
                        $result = getSumOfPurchaseEntryItems($list->id);
                        $total_qty = $total_qty + $result['qty'];
                        $total_value = round($total_value + $result['amount'] ,2 , PHP_ROUND_HALF_EVEN) ;

                        $html .="<tr class='accordion-button collapsed' data-bs-toggle='collapse' data-bs-target='#collapse_".$list->id."' aria-expanded='false' aria-controls='flush-collapseOne'>";
                            $html .="<td>".++$key."</td>";
                            $html .="<td>".ucwords($list->style_no)."</td>";
                            $html .="<td>".ucwords($list->color)."</td>";
                            $html .="<td>".$result['qty']."</td>";
                            $html .="<td>".$result['amount']."</td>";
                        $html .="</tr> ";

                        $html .="<tr>";
                            $html .="<td colspan='5'>";
                                $html .="<div id='collapse_".$list->id."' class='accordion-collapse collapse' aria-labelledby='flush-headingOne' data-bs-parent='#accordionFlushExample'>";
                                    $html .="<div class='accordion-body table-responsive' >";
                                        $html .="<table class='table  '>";
                                            $html .="<thead>";
                                                $html .="<tr>";
                                                    $html .="<th> SN</th>";
                                                    $html .="<th> Size</th>";
                                                    $html .="<th> Qty</th>";
                                                    $html .="<th> Price</th>";
                                                $html .="</tr>";
                                            $html .="</thead>";
                                            $html .="<tbody>";
                                            $purchase_entry_items = $this->getPurchaseEntryItems($list->id);

                                            foreach ($purchase_entry_items['items'] as $key1 => $item) {
                                                $html .="<tr>";
                                                    $html .="<td>".++$key1."</td>";
                                                    $html .="<td>".$item->size."</td>";
                                                    $html .="<td>".$item->qty."</td>";
                                                    $html .="<td>".$item->price."</td>";
                                                $html .="</tr>";
                                            }

                                            $html .="</tbody>";
                                        $html .="</table>";
                                    $html .="</div>";
                                $html .="</div>";
                            $html .="</td>";
                        $html .="</tr>";
                    }                                               
                    $html .="</tbody>";
                $html .="</table>";  
                $html .="</div>"; 


        return $result = [
            'status'=>200,
            'html'=>$html,
            'total_qty'=>$total_qty,
            'total_value'=>$total_value,
        ] ;


    }

    public function viewPurchaseEntry($purchase_id)
    {
        $purchase_entry = PurchaseEntry::Join('style_nos','style_nos.id','=','purchase_entries.style_no_id')
                ->Join('categories','categories.id','=','purchase_entries.category_id')
                ->join('sub_categories','sub_categories.id','=','purchase_entries.sub_category_id')
                ->join('brands','brands.id','=','purchase_entries.brand_id')
                ->where('purchase_entries.purchase_id', $purchase_id)
                ->get(['purchase_entries.*','style_nos.style_no', 'categories.category','sub_categories.sub_category','brands.brand_name']);

            $html = "";
            $html .= "<div class='card'>";

                $html .= "<div class='card-header'>";
                $html .= "<div class='row'>";
                    $html .= "<div class='col-md-8 col-lg-8 col-xl-8'>";
                        $html .= "<h3 class='card-title'>Purchase Entry</h3>";
                    $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
    
                $html .= "<div class='card-body table-responsive p-0'style='height: 500px;' >";
                $html .="<div class='accordion accordion-flush' id='accordionFlushExample'>";
                $html .="<table class='table table-striped'>";
                    $html .="<thead>";
                        $html .="<tr style='position: sticky;z-index: 1;'>";
                            $html .="<th>SN</th>";
                            // $html .="<th>Category</th>";
                            $html .="<th>Product</th>";
                            $html .="<th>Brand</th>";
                            $html .="<th>Style No</th>";
                            $html .="<th>Color</th>";
                            $html .="<th>Action</th>";
                        $html .="</tr>";
                    $html .="</thead>";
                    $html .="<tbody >";
                        
                    foreach ($purchase_entry as $key => $list) {
                               
                        $html .="<tr class='accordion-button collapsed' data-bs-toggle='collapse' data-bs-target='#collapse_".$list->id."' aria-expanded='false' aria-controls='flush-collapseOne'>";
                            $html .="<td>".++$key."</td>";
                            // $html .="<td>".ucwords($list->category)."</td>";
                            $html .="<td>".ucwords($list->sub_category)."</td>";
                            $html .="<td>".ucwords($list->brand_name)."</td>";
                            $html .="<td>".ucwords($list->style_no)."</td>";
                            $html .="<td>".ucwords($list->color)."</td>";
                            $html .="<td>";
                                $html .="<button type='button' class='btn btn-info btn-flat btn-sm mx-1 barcodeBtn' value='".$list->id."' > <i class='fas fa-barcode'></i></button>";
                                $html .="<button type='button' class='btn btn-secondary btn-flat btn-sm mx-1 editPurchaseEntryBtn' value='".$list->id."' ><i class='far fa-edit'></i></button>";
                                $html .="<button type='button' class='btn btn-danger btn-flat btn-sm' purchase-id='".$purchase_id."'  id='deletePurchaseEntryStyleBtn' value='".$list->id."' ><i class='fa fa-trash'></i></button>";
                            $html .="</td>";
                        $html .="</tr> ";

                        $html .="<tr>";
                            $html .="<td colspan='6'>";
                                $html .="<div id='collapse_".$list->id."' class='accordion-collapse collapse' aria-labelledby='flush-headingOne' data-bs-parent='#accordionFlushExample'>";
                                    $html .="<div class='accordion-body table-responsive' >";
                                        $html .="<table class='table  '>";
                                            $html .="<thead>";
                                                $html .="<tr>";
                                                    $html .="<th> SN</th>";
                                                    $html .="<th> Size</th>";
                                                    $html .="<th> Qty</th>";
                                                    $html .="<th> Price</th>";
                                                    $html .="<th> Action</th>";
                                                $html .="</tr>";
                                            $html .="</thead>";
                                            $html .="<tbody>";
                                            $purchase_entry_items = $this->getPurchaseEntryItems($list->id);

                                            foreach ($purchase_entry_items['items'] as $key1 => $item) {
                                                $html .="<tr>";
                                                    $html .="<td>".++$key1."</td>";
                                                    $html .="<td>".$item->size."</td>";
                                                    $html .="<td>".$item->qty."</td>";
                                                    $html .="<td>".$item->price."</td>";
                                                    $html .="<td><button type='button' class='btn btn-danger btn-sm' purchase-id='".$purchase_id."' purchase-entry-id='".$item->purchase_entry_id."' id='deletePurchaseEntryItemBtn' value='".$item->id."'>Delete</button></td>";
                                                $html .="</tr>";
                                            }

                                            $html .="</tbody>";
                                        $html .="</table>";
                                    $html .="</div>";
                                $html .="</div>";
                            $html .="</td>";
                        $html .="</tr>";
                    }                                               
                    $html .="</tbody>";
                $html .="</table>";  
                $html .="</div>"; 
                $html .= "</div>";

            $html .= "</div>";


        return $result = [
            'status'=>200,
            'html'=>$html,
        ] ;
    }

    public function getPurchaseEntryItems($purchase_entry_id)
    {
        $items = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id])->get();

        return $result = [
            'status'=>200,
            'items'=>$items
        ] ;
    }

    public function generatePurchaseInvoice($purchase_id)
    {

        $business_detail = BusinessDetails::first();
        $purchase = Purchase::find($purchase_id);
        // $supplier = Supplier::find($purchase->supplier_id);

        $supplier = Supplier::join('cities','suppliers.city_id','=','cities.id')
            ->where('suppliers.id', $purchase->supplier_id)
            ->select('suppliers.*','cities.city')
            ->first();

        $purchase_entry = PurchaseEntry::join('sub_categories','purchase_entries.sub_category_id','=','sub_categories.id')
            // ->join('brands','purchase_entries.brand_id','=','brands.id')
            ->join('style_nos','purchase_entries.style_no_id','=','style_nos.id')
            ->where(['purchase_id'=> $purchase_id])
            ->get(['purchase_entries.*','sub_categories.sub_category','style_nos.style_no' ]);

        // $purchase_entry_items = $this->getPurchaseEntryItems()
        // $purchase_entry_items = array();
        // foreach ($purchase_entry as $key1 => $list) {
        //     $purchase_entry_items[] = PurchaseEntryItem::where(['purchase_entry_id'=>$list->id])->get();
        // }


        $html = "";

        $html .="<div class='modal-dialog modal-lg'>";
            $html .="<div class='modal-content'>";

                $html .="<div class='modal-header'>";
                    $html .="<h5 class='modal-title' id='staticBackdropLabel'>Purchase Invoice</h5>";
                    $html .="<button type='button' class='btn-close' id='reload_invoice_print' data-bs-dismiss='modal' aria-label='Close'></button>";
                $html .="</div>";

                $html .="<div class='modal-body' id='print_invoice' style='border:1px solid black'>";

                    $html .= "<div class='row mb-1' >";
                        $html .= "<div class='col-sm-12'>";
                            $html .= "<h5 class='modal-title text-center'><b> ".strtoupper($supplier->supplier_name)." </b></h5>";
                        $html .= "</div>";

                        $html .= "<div class='col-sm-12 text-center'>";
                            $html .= "<small class='modal-title'>";
                                $html .= "<b>".$supplier->address."</b><br>";
                                $html .= "<b>".ucwords($supplier->city)."</b><br>";
                            $html .= "</small>";
                        $html .= "</div>";
                        $html .= "<div class='col-sm-6 '>";
                            $html .= "<small class='modal-title'>";
                                $html .= "<b>GSTNO -  </b> ".$supplier->gst_no."<br>";
                            $html .= "</small>";
                        $html .= "</div>";
                        $html .= "<div class='col-sm-6 text-right'>";
                            $html .= "<small class='modal-title'>";
                                $html .= "<b>Mobile -  </b> ".$supplier->mobile_no."<br>";
                            $html .= "</small>";
                        $html .= "</div>";
                        
                    $html .= "</div>";

                    $html .= "<div class='row'>";
                            
                        $html .= "<div class='card text-dark bg-light mt-2' >";
                            $html .= "<div class='card-header text-center'><b>TAX INVOICE</b></div>";
                            $html .= "<div class='card-body'>";
                                $html .= "<div class='row'>";
                                    $html .= "<div class='col-md-6'>";
                                    $html .= "<small class='modal-title'>";
                                    
                                        $html .= "<b>".$business_detail->business_name." </b>";
                                        $html .= "<p style='inline-size:180px'>".$business_detail->company_address." </p>";
                                        $html .= "<b>GSTNO - ".$business_detail->gst." </b><br>";
                                    $html .= "</small>";
                                    $html .= "</div>";
                                    $html .= "<div class='col-md-6'>";
                                    $html .= "<small class='modal-title'>";
                                        $html .= "<b>Bill No -  </b> ".$purchase->bill_no." <br>";
                                        $html .= "<b>Bill Date -  </b> ". date('d-m-Y', strtotime($purchase->bill_date)) ."<br>";
                                    $html .= "</small>";
                                    $html .= "</div>";
                                $html .= "</div>";
                            $html .= "</div>";
                        $html .= "</div>";
                        
                    $html .= "</div>";

                    $html .= "<div class='row'>";
                        $html .= "<table class='table table-bordered border-dark'>";
                            $html .= "<thead>";
                                $html .= "<tr>";
                                    $html .= "<th style='width: 5%;'>SN</th>";
                                    $html .= "<th style='width: 25%;'>Description</th>";
                                    $html .= "<th style='width: 20%;'>Style No</th>";
                                    $html .= "<th style='width: 10%;'>Color</th>";

                                    $html .= "<th style='width: 5%;'>Size</th>";
                                    $html .= "<th style='width: 5%;'>Qty</th>";
                                    $html .= "<th >Price</th>";
                                    $html .= "<th >Dis.</th>";
                                    $html .= "<th >Taxable</th>";
                                    $html .= "<th >SGST</th>";
                                    $html .= "<th >CGST</th>";
                                    $html .= "<th >IGST</th>";
                                    $html .= "<th >Amount</th>";
                                $html .= "</tr>";
                            $html .= "</thead>";
                
                            $html .= "<tbody>";

                                $total_sgst = 0;
                                $total_cgst = 0;
                                $total_igst = 0;
                                $discount_amount = 0;
                                $total_taxable = 0;
                                $grand_total = 0;

                                foreach ($purchase_entry as $key => $list) {
                                    
                                    $data = $this->getPurchaseEntryItems($list->id);
                                    $row_count = count($data['items']);

                                    $html .= "<tr >";
                                        
                                        $html .= "<td rowspan='". ($row_count + 1) ."'>".++$key."</td>";
                                        $html .= "<td rowspan='". ($row_count + 1) ."'>".ucwords($list->sub_category)."</td>";
                                        $html .= "<td rowspan='". ($row_count + 1) ."'>".$list->style_no."</td>";
                                        $html .= "<td rowspan='". ($row_count + 1) ."'>".ucwords($list->color)."</td>";
                                        $html .= "<td >";

                                        $sgst = 0;
                                        $cgst = 0;
                                        $igst = 0;
                                        $discount = 0;
                                        $taxable = 0;
                                        $amount = 0;
                                        
                                        foreach ($data['items'] as $item) {
                                            
                                            $html .= "<tr>"; 
                                                $html .= "<td >".$item->size."</td>";
                                                $html .= "<td >".$item->qty."</td>";
                                                $html .= "<td >".$item->price."</td>";
                                                $html .= "<td >".$item->discount."</td>";
                                                $html .= "<td >".$item->taxable."</td>";
                                                $html .= "<td >".$item->sgst."</td>";
                                                $html .= "<td >".$item->cgst."</td>";
                                                $html .= "<td >".$item->igst."</td>";
                                                $html .= "<td >".$item->amount."</td>";
                                            $html .= "</tr>";

                                            $sgst = $sgst + $item->sgst;
                                            $cgst = $cgst + $item->cgst;
                                            $igst = $igst + $item->igst;
                                            $discount = $discount + $item->discount_amount;
                                            $taxable = $taxable + $item->taxable;
                                            $amount = $amount + $item->amount;
                                        }

                                        $total_sgst = $total_sgst + $sgst ;
                                        $total_cgst = $total_cgst + $cgst ;
                                        $total_igst = $total_igst + $igst ;
                                        $discount_amount = $discount_amount + $discount ;
                                        $grand_total = $grand_total + $amount ;
                                        $total_taxable = $total_taxable + $taxable ;                        
                                        $html .= "</td>";
                                        
                                    $html .= "</tr>";
                                }

                            $html .= "</tbody>";
                
                            $html .= "<tfoot>";

                                // $html .= "<tr>";
                                //     $html .= "<td colspan='5' class='align-top'></td>";
                                //     $html .= "<td  >Total Qty</td>";
                                //     $html .= "<td  >Total Price</td>";
                                // $html .= "</tr> ";

                                $html .= "<tr>";
                                    $html .= "<td colspan='8' rowspan='6'  class='align-top'> Amount in Words : ";
                                        $html .= "<textarea class='form-control' name='amount_in_words' id='amount_in_words'>".convertNumberToWords($grand_total)."</textarea>";
                                    $html .= "</td>  ";
                                    $html .= "<td colspan='3' ><b>Total Amount :</b></td>";
                                    $html .= "<td colspan='2'><input type='text' class='form-control form-control-sm' value='".$total_taxable."' readonly></td>";
                                $html .= "</tr> ";
                                $html .= "<tr>";
                                    $html .= "<td colspan='3'><b>Discount :</b></td>";
                                    $html .= "<td colspan='2'><input class='form-control form-control-sm' type='text' value='".$discount_amount."' readonly></td>";
                                $html .= "</tr>";
                                $html .= "<tr>";
                                    $html .= "<td colspan='3'><b>SGST : </b></td>";
                                    $html .= "<td colspan='2'><input class='form-control form-control-sm' type='text' value='".$total_sgst."' readonly></td>";
                                $html .= "</tr>";
                                $html .= "<tr> ";
                                    $html .= "<td colspan='3'><b>CGST : </b></td>";
                                    $html .= "<td colspan='2'><input class='form-control form-control-sm' type='text' value='".$total_cgst."' readonly></td>";
                                $html .= "</tr>";
                                $html .= "<tr>";
                                    $html .= "<td colspan='3'><b>IGST : </b></td>";
                                    $html .= "<td colspan='2'><input class='form-control form-control-sm' type='text' value='".$total_igst."' readonly></td>";
                                $html .= "</tr>";
                                $html .= "<tr>";
                                    $html .= "<td colspan='3'><b>Grand Total : </b></td>";
                                    $html .= "<td colspan='2'><input class='form-control form-control-sm' type='text' value='".$grand_total."' readonly ></td>";
                                $html .= "</tr>";
                                
                            $html .= "</tfoot>";
                
                        $html .= "</table>";
                    $html .= "</div>";

                $html .="</div>";

                $html .="<div class='modal-footer'>";
                    $html .="<button type='button' class='btn btn-secondary btn-sm' data-bs-dismiss='modal' id='reload_invoice_print'>Close</button>";
                $html .="<button type='button' id='printPurchaseInvoice' class='btn btn-primary btn-sm' print-section='print_invoice'>Print</button>";

            $html .="</div>";
        $html .="</div>";

        return response()->json([
            'status'=>200,
            'html'=>$html,
            'purchase'=>$purchase,
            'supplier'=>$supplier,
            // 'grand_total'=>round($grand_total ,0 , PHP_ROUND_HALF_EVEN)

        ]);
    }
    
    public function editPurchaseEntry($purchase_entry_id)
    {
        $purchase_entry = PurchaseEntry::find($purchase_entry_id);

        $purchase = Purchase::join('suppliers','purchases.supplier_id','=','suppliers.id')
            ->where('purchases.id', $purchase_entry->purchase_id)
            ->first(['purchases.*','suppliers.supplier_name','suppliers.state_type','suppliers.supplier_code','suppliers.gst_no','suppliers.address']);

        $purchase_entry_items = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id])->get();

        $size_type = Category::where(['id'=>$purchase_entry->category_id])->value('size_type');
        $sub_category_data = SubCategory::where(['category_id'=>$purchase_entry->category_id])->get(['id' , 'category_id', 'sub_category']);

        $sub_category_html = "";
        $sub_category_html .= "<option disabled>Choose...</option>";
        foreach ($sub_category_data as $key => $list) {
            if ($list->id == $purchase_entry->sub_category_id) {
                $sub_category_html .= "<option selected value='".$list->id."'>".ucwords($list->sub_category)."</option>";
            } else {
                $sub_category_html .= "<option value='".$list->id."'>".ucwords($list->sub_category)."</option>";
            }
        }

        $styles_no = StyleNo::where(['supplier_id'=>$purchase->supplier_id])->get();

        $style_no_html = "";
        $style_no_html .= "<option selected disabled value='0'>Category</option>";
        foreach ($styles_no as $key => $list) {
            if ($list->id == $purchase_entry->style_no_id) {
                $style_no_html .= "<option value='".$list->id."' selected>" . ucwords($list->style_no)  . "</option>" ;
            }else{
                $style_no_html .= "<option value='".$list->id."' >" . ucwords($list->style_no)  . "</option>" ;
            }
        }

        return response()->json([
            'status'=>200,
            'purchase'=>$purchase,
            'purchase_entry'=>$purchase_entry,
            'purchase_entry_items'=>$purchase_entry_items,
            'sub_category_html'=>$sub_category_html,
            'style_no_html'=>$style_no_html,
            'size_type'=>$size_type,
        ]);
    }

    public function updatePurchaseEntry(Request $req, $purchase_id, $purchase_entry_id)
    {

        if ($req->size_type_id == MyApp::NORMAL_SIZE) {
            $result = $this->purchaseEntryNormalSizeValidation($req);
        }elseif ($req->size_type_id == MyApp::KIDS_SIZE) {
            $result = $this->purchaseEntryKidsSizeValidation($req);
        }elseif ($req->size_type_id == MyApp::WITHOUT_SIZE) {
            $result = $this->purchaseEntryWithoutSizeValidation($req);
        }

        if ($result['status'] == 400) {
            return response()->json([   
                'status'=>$result['status'],
                'errors'=>$result['errors'],
            ]);
        }

        $supplier_id = $req->input('supplier_id');
        $bill_no = $req->input('bill_no');
        $model = Purchase::find($purchase_id);
            
        $model->bill_no = $bill_no;
        $model->bill_date = $req->input('bill_date');
        $model->payment_days = $req->input('payment_days');
        $model->time = date('g:i A');

        if ($model->save()) {

            $category_id = $req->input('category_id');
            $sub_category_id = $req->input('sub_category_id');
            $brand_id = $req->input('brand_id');
            $style_no_id = $req->input('style_no_id');
            $color = $req->input('color');
            $product_image = $req->input('product_image');
            
            $purchase_entry = PurchaseEntry::find($purchase_entry_id);
            
            $purchase_entry->category_id = $category_id;
            $purchase_entry->sub_category_id = $sub_category_id;
            $purchase_entry->brand_id = $brand_id;
            $purchase_entry->style_no_id = $style_no_id;
            $purchase_entry->color = $color;

            if ($product_image != '') {
                $purchase_entry->img = $product_image;
            }

            if ($purchase_entry->save()) {
                //delete purchase entry items
                // $purchase_entry_items = PurchaseEntryItem::where('purchase_entry_id', $purchase_entry_id)->get(['id']);
                // $items_deleted = PurchaseEntryItem::destroy($purchase_entry_items->toArray());
    
                $purchase_entry_items = PurchaseEntryItem::where('purchase_entry_id', $purchase_entry_id)->get(['id','size','qty']);
                foreach ($purchase_entry_items as $key => $list) {
                    $items = PurchaseEntryItem::find($list->id);
                    $items->delete();
    
                    $stock_type = MyApp::MINUS_MANAGE_STOCK;
                    manageStock($stock_type, $purchase_entry_id, $list->size, $list->qty);
                }
            }

            
            if ($req->size_type_id == MyApp::NORMAL_SIZE) {
                $result = $this->saveNormalSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id);
            }elseif ($req->size_type_id == MyApp::KIDS_SIZE) {
                $result = $this->saveKidsSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id);
            }elseif ($req->size_type_id == MyApp::WITHOUT_SIZE) {
                $result = $this->saveWithoutSizePurchaseEntry($req, $bill_no, $supplier_id, $purchase_entry_id);
            }

            if ($result['status'] == 400) {
                return response()->json([   
                    'status'=>$result['status'],
                    'errors'=>$result['errors'],
                ]);
            }else{
                return response()->json([   
                    'status'=>$result['status'],
                    // 'html'=>$result['html'],
                ]);
            }        
     
        }
        
    }

    public function deletePurchaseEntryStyleWise($purchase_entry_id)
    {
       
        $purchase_entry_items = PurchaseEntryItem::where('purchase_entry_id', $purchase_entry_id)->get(['id']);
        $items_deleted = PurchaseEntryItem::destroy($purchase_entry_items->toArray());
        if($items_deleted)
        {
            $purchase_entry = PurchaseEntry::find($purchase_entry_id);
            $purchase_entry->delete();
        }
        return response()->json([
            'status'=>200,
        ]);
    }

    public function deletePurchaseEntryItemWise($purchase_entry_item_id)
    {

        $purchase_item = PurchaseEntryItem::where(['id'=>$purchase_entry_item_id])->first(['id','purchase_entry_id']);
        $purchase_entry_items_count = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_item->purchase_entry_id])->count();
        $purchase_item->delete();

        if ($purchase_entry_items_count == 1) {
            $purchase_entry = PurchaseEntry::where(['id'=>$purchase_item->purchase_entry_id]);
            $purchase_entry->delete();
        }

        return response()->json([
            'status'=>200,
            
        ]);
    }


    public function getProductDetail($product_code)
    {
        $purchase_entry_item = PurchaseEntryItem::where(['barcode'=> $product_code])->first(['purchase_entry_id','size','mrp','qty','barcode','price','id']);
        if($purchase_entry_item){

            $employee = User::all(['id' , 'code']);

            $emp_html = "";
            $emp_html .= "<option selected disabled value='0'>Emp</option>";
            foreach($employee as $list)
            {
                $emp_html.= "<option value='" . $list->id . "'>" . strtoupper($list->code) . "</option>";
            }

            $purchase_entry = PurchaseEntry::join('sub_categories','purchase_entries.sub_category_id','=','sub_categories.id')
                    ->where(['purchase_entries.id'=> $purchase_entry_item->purchase_entry_id])
                    ->first(['purchase_entries.id','purchase_entries.style_no_id','purchase_entries.brand_id','purchase_entries.sub_category_id','sub_categories.sub_category']);

            
            $offers="";             
            $offer = '';
            // dd($purchase_entry_item->barcode);
            // $barcode_offer = ApplyOffer::where(['status'=>MyApp::ACTIVE])->whereRaw("FIND_IN_SET($purchase_entry_item->barcode, barcode)")->first();
            $barcode_offer = ApplyOffer::whereRaw("find_in_set('".$purchase_entry_item->barcode."',barcode)")->where(['status'=>MyApp::ACTIVE])->first();

            if ( $barcode_offer != null ) {
                $offer = $barcode_offer;
            }else{
                if ($purchase_entry->brand_id > 0) {
                    $brand_offer = ApplyOffer::where(['status'=>MyApp::ACTIVE, 'brand_id'=>$purchase_entry->brand_id ])->first();
                    if ($brand_offer == null) {
                        $sub_category_offer = ApplyOffer::where(['status'=>MyApp::ACTIVE, 'sub_category_id'=>$purchase_entry->sub_category_id ])->first();
                        if($sub_category_offer == null){
                            $store_offer = ApplyOffer::where(['status'=>MyApp::ACTIVE, 'brand_id'=>0,'sub_category_id'=>0 ])->first();
                            $offer = $store_offer;
                        }else{
                            $offer = $sub_category_offer;
                        }
                    }else{
                        $offer = $brand_offer;
                    }
                }else{
                    //store offers
                    $store_offer = ApplyOffer::where(['status'=>MyApp::ACTIVE, 'brand_id'=>0,'sub_category_id'=>0 ])->first();
                    $offer = $store_offer;
                }
            }

            // if($offer_section == MyApp::PRODUCT){
            //     if($offer_type == MyApp::PERCENTAGE){

            //     }
            // }else {
                
            // }




            // if (count($offers_data) > 0) {


            //     foreach ($offers_data as $key => $list) {

            //         if ($list->offer_section == MyApp::PRODUCT) {

            //             if ($list->offer_type == MyApp::PERCENTAGE) {

            //                 if($purchase_entry->brand_id){

            //                     $data = $list->where(['apply_offers.status'=>MyApp::ACTIVE])
            //                             ->where(['offer_section'=>MyApp::PRODUCT])
            //                             ->where("apply_offers.brand_id",$purchase_entry->brand_id)
                                        
            //                             ->join('offers','offers.id','=','apply_offers.offer_type_id')
            //                             ->select('apply_offers.*','offers.discount_offer')->get();  
            //                 }

            //                 if (count($data) > 0) {
            //                     $offers =   $list->where(['apply_offers.status'=>MyApp::ACTIVE])
            //                         ->where(['offer_section'=>MyApp::PRODUCT])
            //                         ->join('offers','offers.id','=','apply_offers.offer_type_id')
            //                         ->select('apply_offers.*','offers.discount_offer')->get();  
            //                         break;
            //                 }else{
            //                     $offers= $list->where(['apply_offers.status'=>MyApp::ACTIVE])
            //                     ->where(['offer_section'=>MyApp::PRODUCT])
            //                     ->get('apply_offers.id');
            //                 }

            //             }
                        
            //         } else{
            //             //store offer
            //             if ($list->offer_type == MyApp::PERCENTAGE) {
            //                 $offers = $list->where(['apply_offers.status'=>MyApp::ACTIVE])
            //                         ->where(['offer_section'=>MyApp::STORE])
            //                         ->join('offers','offers.id','=','apply_offers.offer_type_id')
            //                         ->select('apply_offers.*','offers.discount_offer')->get();
            //                         break;
                            
            //             }

            //             if ($list->offer_type == MyApp::VALUES) {
                            
            //                 $offers = $list->where(['apply_offers.status'=>MyApp::ACTIVE])
            //                         ->where(['offer_section'=>MyApp::STORE])
            //                         ->join('offers','offers.id','=','apply_offers.offer_type_id')
            //                         ->select('apply_offers.*','offers.discount_offer')->get();
            //                         break;
                                
            //                 }
                            
            //                 else{
            //                     $offers = $list->where(['apply_offers.status'=>MyApp::ACTIVE])
            //                         ->where(['offer_section'=>MyApp::STORE])
            //                         ->get('apply_offers.id');
            //                 }
                        
            //             }

            //         }
                  
                
            // }  
           
                    
            $result = collect([
                'product_id' => $purchase_entry->sub_category_id,
                'brand_id' => $purchase_entry->brand_id,
                'style_no_id' => $purchase_entry->style_no_id,
                'product' => $purchase_entry->sub_category,
                'size' => $purchase_entry_item->size,
                'mrp' => $purchase_entry_item->mrp,
                'qty' => $purchase_entry_item->qty,
                'barcode' => $purchase_entry_item->barcode,
                'price' => $purchase_entry_item->price,
                'purchase_entry_item_id' => $purchase_entry_item->id,
            ]);

            return response()->json([
                'status'=>200,
                // 'emp_html'=>$emp_html,
                'product_detail'=>$result,
                // 'offers_data'=>$offers_data,
                // 'offers'=>$offers,

                // 'offer_section'=>$offer->offer_section,
                // 'offer_type'=>$offer->offer_type,
                'offer'=>$offer,
                'barcode_offer'=>$barcode_offer,
            
            ]);
        }else{
            return response()->json([
                'status'=>400,
                'product_detail'=>"barcode not found"
            ]);
        } 
    }

    public function getBarcode()
    {
        $products = PurchaseEntry::Join('categories','categories.id','=','purchase_entries.category_id')
                ->join('sub_categories','sub_categories.id','=','purchase_entries.sub_category_id')
                ->join('sizes','sizes.id','=','purchase_entries.size_id')
                ->join('colors','colors.id','=','purchase_entries.color_id')
                ->get(['purchase_entries.*','categories.category',
                    'sub_categories.sub_category',
                    'sizes.size',
                    'colors.color'
                ]);


                $html = "";
                $html .="<div class='modal-dialog modal-sm'>";
                    $html .="<div class='modal-content'>";
                        $html .="<div class='modal-header'>";
                            $html .="<h5 class='modal-title' id='staticBackdropLabel'>Invoice</h5>";
                            $html .="<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                        $html .="</div>";
        
                            $html .="<div class='modal-body-wrapper'>";
        
        
                                $html .="<div class='modal-body' id='show_barcode_body'>";
            
                                    $html .="<div class='row text-center'>";
                                        $html .="<h5><b>Shivhare Hotel</b></h5>";
                                        $html .="<small>Dhuma</small>";
                                    $html .="</div>";
                                    $html .="<hr>";
                                    foreach ($products as $key => $list) {
                                        $html .="<div class='row' style='page-break-after: always;'>";
                                            $html .="<div class='col-md-6'>";
                                                $html .="<span>Bill No : <small>1</small></span><br>";
                                                $html .="<span>Payment : <small>2</small></span> ";
                                                $html .="</div>";
                                                $html .="<div class='col-md-6 '>";
                                                // $html .="<span class='float-end'>Date : <small>3</small></span><br>";
                                                // $html .="<span class='float-end'>Time : <small>4</small></span> ";
                                                $html .="<img src='".asset('public/assets/barcodes/barcode.gif')."' > ";
                                            $html .="</div>";
                                        $html .="</div>";
                                        $html .="<hr>";
                                    }
                                    // $html .="<div class='row'>";
                                    //     $html .="<table class='table table-striped'>";
                                    //         $html .="<thead>";
                                    //             $html .="<tr>";
                                    //                 $html .="<th>#</th>";
                                    //                 $html .="<th>Item Name</th>";
                                    //                 $html .="<th>Rate</th>";
                                                    
                                    //             $html .="</tr>";
                                    //         $html .="</thead>";
                                    //         $html .="<tbody>";
                                    //         foreach ($products as $key => $list) {
                                    //             $html .="<tr>";
                                    //                 $html .="<td>".++$key."</td>";
                                    //                 // $html .="<td>".ucwords($list->item_name)."</td>";
                                    //                 // $html .="<td>".$list->price."</td>";
                                    //                 // $html .="<td>".$list->qty."</td>";
                                    //                 // $html .="<td>".$list->amount."</td>";
                                    //             $html .="</tr>";
                                    //         }
                                                
                                    //         $html .="</tbody>";
                                    //         $html .="<tfoot>";
                                    //             $html .="<tr>";
                                    //                 $html .="<td colspan='2'></td>";
                                    //                 $html .="<td><b>Total :</b></td>";
                                    //                 // $html .="<td>".$key."</td>";
                                    //                 // $html .="<td>".$order->total_amount."</td>";
                                    //             $html .="</tr>";
                                    //         $html .="</tfoot>";
                                    //     $html .="</table>";
                                    // $html .="</div>";

                                    // $html .="<hr>";

                                    // $html .="<div class='row text-center'>";
                                    //     $html .="<h6><b>Thank You Have a Nice Day </b></h6>";
                                    //     $html .="<small>Visit Again !</small>";
                                    // $html .="</div>";
            
                                $html .="</div>";
            
        
                            $html .="</div>";
        
                            $html .="<div class='modal-footer'>";
                                $html .="<button type='button' class='btn btn-secondary btn-sm' data-bs-dismiss='modal'>Close</button>";
                                $html .="<button type='button' id='printBtn' class='btn btn-primary btn-sm' order-id=''>Print</button>";
                            $html .="</div>";
        
                    $html .="</div>";
                $html .="</div>";

            return response()->json([
                'status'=>200,
                'html'=>$html
            ]);

        // return view('barcode',[
        //     'products' => $products
        // ]);
    }

    public function getcolorcode($color_code)
    {
        // $color = Color::where(['color_code'=>$color_code])->first('color');
        // print_r($product);
        $color = Color::find($color_code);
                        
        return response()->json([
            'color'=>$color
        ]);

    }

    public function importProduct(){
        return Excel::download(new ExportProduct,'Purchase_entry.xlsx');
    }

    public function exportProduct(){
        Excel::import(new ImportProduct,request()->file('file'));
        return back();
    }

    // save subcategory of purchase entry
    function saveSubCategory(Request $req)
    {
        $validator = Validator::make($req->all(),[
            "category_id" => 'required|max:191',
            "sub_category" => 'required|unique:sub_categories,sub_category,'.$req->input('sub_category'),
            'sub_category_img' => 'required|max:191'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages("plz fill all field required"),
            ]);
        }else{
            $model = new SubCategory;
            $model->category_id = $req->input('category_id');
            $model->sub_category = $req->input('sub_category');
                    $subCategoryImage = public_path('storage/').$model->subcategory_img;
                    if(file_exists($subCategoryImage)){
                        @unlink($subCategoryImage); 
                    }
                $model->sub_category_img = $req->file('sub_category_img')->store('image/subcategory'. $req->input('sub_category_img'),'public'); 
            if($model->save()){
                return response()->json([   
                    'status'=>200,
                ]);
            }
        }
    }
// save style no of purchase entry
    public function manageStyleNo(Request $req)
    {
        if($req->input('style_id') > 0)
        {
            $supplier_id = 'required|max:191';
            $style_no = 'required|unique:style_nos,style_no,'.$req->input('style_id');
            $model = StyleNo::find($req->input('style_id'));
        }else{
            $supplier_id = 'required|max:191';
            $style_no = 'required|unique:style_nos,style_no,'.$req->input('style_no');
            $model = new  StyleNo;
        }

        $validator = Validator::make($req->all(),[
            'supplier_id' => $supplier_id,
            'style_no' => $style_no
        ]);
        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages(),
            ]);
        }else{
            $model->supplier_id = $req->input('supplier_id');
            $model->style_no = strtoupper($req->input('style_no'));

            if($model->save()){
                return response()->json([
                    'status'=>200,
                ]);
            }
        }
    }

    function saveBrand(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'brand_name' => 'required|max:191',
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages("plz fill all field required"),
            ]);
        }else{
            $model = new Brand;
            $model->brand_name = $req->input('brand_name');         
            if($model->save()){
                return response()->json([   
                    'status'=>200,
                ]);
            }
        }
    }

    public function editBrand($brand_id)
    {
        $brand = Brand::find($brand_id);
        return response()->json([
            'status'=>200,
            'brand'=>$brand
        ]);
    }

    public function updateBrand(Request $req, $brand_id)
    {
       
        $validator = Validator::make($req->all(),[
            'brand_name' => 'required|max:191',
        ]);
        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages(),
            ]);
        }else{
            $model = Brand::find($brand_id);
            $model->brand_name = $req->input('brand_name'); 
           
            
            if($model->save()){
                return response()->json([
                    'status'=>200,
                ]);
            }
        }
    }

    public function deleteBrand($brand_id)
    {
        $delete_brand = Brand::find($brand_id);
        $delete_brand->delete();
        return response()->json([
            'status'=>200
        ]);
    }

    public function loadPtFileData(Request $req)
    {
        if($req->hasfile('pt_file')){

            $replace_dash = str_replace('-', ' ', $req->file('pt_file')->getClientOriginalName());
            $file_name =  $req->supplier_id . '_' . $req->bill_no . '_' . str_replace(' ', '_', $replace_dash) ; 
            
            $file_exist = public_path('storage/files').$file_name;
            if(file_exists($file_exist)){
                @unlink($file_exist ); 
            }
            $filePath = $req->file('pt_file')->storeAs('files', $file_name, 'public');
        }

        $storage_path = storage_path('app/public/files/'.$file_name);
        // $storage_path = storage_path('app/public/files/demo.csv');

        $column_header = array();
        $final_data = array();
        $file_data = file_get_contents($storage_path);
        $data_array = array_map("str_getcsv", explode("\n", $file_data));
        $labels = array_shift($data_array);
        foreach($labels as $label)
        {
            $column_header[] = strtolower(str_replace(' ', '_', $label));
        }
        $count = count($data_array) - 1;
        for($i = 0; $i < $count; $i++)
        {
            $data = array_combine($column_header, $data_array[$i]);
            $final_data[$i] = $data;
        }

        // Laravel using Storage
        Storage::disk('public')->put('files/'.explode('.', $file_name)[0].'.json', json_encode($final_data)); 

        $html = '';
        $html .="<div class='card'>";
            $html .="<div class='card-header'>";
                $html .= "<div class='row'>";
                $html .= "<div class='col-md-6'>";
                    $html .= "<b>PT File Data</b>";
                $html .= "</div>";
                $html .= "<div class='col-md-6'>";
                    $html .= "<div class='d-grid gap-2 d-md-flex justify-content-md-end '>";
                        $html .= "<button type='button' id='savePtFileBtn' value='" . explode('.', $file_name)[0] . '.json' . "' class='btn btn-primary btn-flat btn-sm'> Save File Data </button>";
                    $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
            $html .="</div>";
            $html .=" <div class='card-body table-responsive p-0' style='height: 350px;'>";
                $html .="<table class='table table-striped table-head-fixed text-nowrap'>";
                    $html .="<thead>";
                        $html .="<tr style='position: sticky;z-index: 1;'>";
                            $html .="<th>SN</th>";
                            $html .="<th>Category</th>";
                            $html .="<th>Sub Category</th>";
                            $html .="<th>Brand</th>";
                            $html .="<th>Style No</th>";
                            $html .="<th>Color</th>";
                            $html .="<th>Size</th>";
                            $html .="<th>Qty</th>";
                            $html .="<th>Sale Rate</th>";
                            $html .="<th>MRP</th>";
                            $html .="<th>Tax(%)</th>";
                            $html .="<th>Tax(rs)</th>";
                            $html .="<th>Barcode</th>";
                        $html .="</tr>";
                    $html .="</thead>";
                    $html .="<tbody>";
                    foreach ($final_data as $key => $list) {
                        $html .="<tr>";
                            $html .="<td>". ++$key ."</td>";
                            $html .="<td>". $list['category'] ."</td>";
                            $html .="<td>". $list['sub_category'] ."</td>";
                            $html .="<td>". $list['brand'] ."</td>";
                            $html .="<td>". $list['style_no'] ."</td>";
                            $html .="<td>". $list['color'] ."</td>";
                            $html .="<td>". $list['size'] ."</td>";
                            $html .="<td>". $list['qty'] ."</td>";
                            $html .="<td>". $list['sale_rate'] ."</td>";
                            $html .="<td>". $list['mrp'] ."</td>";
                            $html .="<td>". $list['tax(%)'] ."</td>";
                            $html .="<td>". $list['tax(rs)'] ."</td>";
                            $html .="<td>". $list['barcode'] ."</td>";
                        $html .="</tr>";
                    }
                    $html .="</tbody >";
                $html .="</table >";
            $html .="</div>";
        $html .="</div>";

        return response()->json([
            'status'=>200,
            'final_data'=>$final_data,
            'html'=>$html,
        ]);
    }

    public function savePtFile(Request $req)
    {
        
        $validator = Validator::make($req->all(),[
            'supplier_id' => 'required|max:191',
            'bill_no' => 'required|max:191',
            'bill_date' => 'required|max:191',
            'payment_days' => 'required|max:191',
            'file_name' => 'required|max:191',
        ]);
        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages(),
            ]);
        }else{

            $supplier_id = $req->supplier_id;
            $bill_no = $req->bill_no;
            $bill_date = $req->bill_date;
            $payment_days = $req->payment_days;

            $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

            $supplier = Supplier::where(['id'=>$supplier_id])->first('state_type');
        
            // $gst = calculateGst($supplier->state_type, $taxable);
            // $total_gst = $gst['sgst'] + $gst['cgst'] + $gst['igst'];
            // $amount = $taxable + $total_gst ;

            $file = storage_path('app/public/files/'.$req->file_name);
            if(file_exists($file)){
                $data = json_decode(file_get_contents($file), true);

                //purchase
                $purchase = Purchase::where(['supplier_id'=>$supplier_id,'bill_no'=>$bill_no])->first('id');
                if ($purchase == null) {
                    $model = new Purchase;
                    
                    $model->supplier_id = $supplier_id;
                    $model->bill_no = $bill_no;
                    $model->bill_date = $bill_date;
                    $model->payment_days = $payment_days;
                    $model->time = date('g:i A');
                    $model->save();
                    
                    $purchase_id = $model->id;
                }else{
                    $purchase_id = $purchase->id;
                }

                foreach ($data as $key => $list) {
                    $category_data = Category::where(['category'=>$list['category'] ])->first('id');
                    if (!$category_data) {
                        $categoryModel = new Category;
                        $categoryModel->category = strtolower($list['category']);
                        $categoryModel->save();
                        $category_id = $categoryModel->id;
                    }else{
                        $category_id = $category_data->id;
                    }

                    $sub_category_data = SubCategory::where(['category_id'=>$category_id, 'sub_category'=>$list['sub_category'] ])->first('id');
                    if (!$sub_category_data) {
                        $subCategoryModel = new SubCategory;
                        $subCategoryModel->category_id = $category_id;
                        $subCategoryModel->sub_category = strtolower($list['sub_category']);
                        $subCategoryModel->save();
                        $sub_category_id = $subCategoryModel->id;
                    }else{
                        $sub_category_id = $sub_category_data->id;
                    }

                    $brand_data = Brand::where(['brand_name'=>$list['brand'] ])->first('id');
                    if (!$brand_data) {
                        $brandModel = new Brand;
                        $brandModel->brand_name = strtolower($list['brand']);
                        $brandModel->save();
                        $brand_id = $brandModel->id;
                    }else{
                        $brand_id = $brand_data->id;
                    }

                    $style_no_data = StyleNo::where(['supplier_id'=>$supplier_id, 'style_no'=>$list['style_no'] ])->first('id');
                    if (!$style_no_data) {
                        $styleNoModel = new StyleNo;
                        $styleNoModel->supplier_id = $supplier_id;
                        $styleNoModel->style_no = strtoupper($list['style_no']); 
                        $styleNoModel->save();
                        $style_no_id = $styleNoModel->id;
                    }else{
                        $style_no_id = $style_no_data->id;
                    }


                    $purchase_entry_data = PurchaseEntry::where(['purchase_id'=>$purchase_id,'style_no_id'=>$style_no_id, 'color'=>$list['color']])->first('id');
                    $purchase_entry_id = 0;
                    if ($purchase_entry_data == null) {
                        $purchase_entry = new PurchaseEntry;

                        $purchase_entry->purchase_id = $purchase_id;
                        $purchase_entry->category_id = $category_id;
                        $purchase_entry->sub_category_id = $sub_category_id;
                        $purchase_entry->brand_id = $brand_id;
                        $purchase_entry->style_no_id = $style_no_id;
                        $purchase_entry->color = strtolower($list['color']);
                        $purchase_entry->save();

                        $purchase_entry_id = $purchase_entry->id;
                    }else{
                        $purchase_entry_id = $purchase_entry_data->id;
                    }

                    $item_exist = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id, 'size'=>$list['size']])->exists();
                    if ($item_exist != true) {
                        
                        $purchase_item = new PurchaseEntryItem;

                        $taxable = 0;
                        $sgst = 0;
                        $cgst = 0;
                        $igst = 0;

                        $taxable = $list['qty'] * $list['sale_rate'];
                        if ($supplier->state_type == MyApp::WITH_IN_STATE) {
                            if ($list['sale_rate'] < 1000) {
                                $sgst = $list['tax(rs)'] / 2;
                                $cgst = $sgst;
                            }else{
                                $igst = $list['tax(rs)'];
                            }
                        }else {
                            $igst = $list['tax(rs)'];
                        }

                        $total_gst = $sgst + $cgst + $igst;
                        $amount = $taxable + $total_gst ;
                        $barcode_img = 'data:image/png;base64,' . base64_encode($generator->getBarcode($list['barcode'], $generator::TYPE_CODE_128, 3, 50)) ;


                        $purchase_item->purchase_entry_id = $purchase_entry_id;
                        $purchase_item->size = $list['size'];
                        $purchase_item->qty = $list['qty'];
                        $purchase_item->price = $list['sale_rate'];
                        $purchase_item->mrp = $list['mrp'];
                        $purchase_item->taxable = $taxable;
                        $purchase_item->sgst = $sgst;
                        $purchase_item->cgst = $cgst;
                        $purchase_item->igst = $igst;
                        $purchase_item->amount = $amount;
                        
                        $purchase_item->barcode = $list['barcode'];
                        $purchase_item->barcode_img = $barcode_img;

                        // $purchase_item->save();
                        if ($purchase_item->save()) {
                            
                            $stock_type = MyApp::PLUS_MANAGE_STOCK;
                            $res = manageStock($stock_type, $purchase_entry_id, $list['size'], $list['qty']);
                        }

                    }
                    
                    // return response()->json([
                    //     'status'=>200,
                    //     'data'=>$data,
                    //     'category_data'=>$category_data,
                    //     'category_id'=>$category_id,
                    // ]);
                }

                return response()->json([
                    'status'=>200,
                    'data'=>$data,
                    
                ]);
            }else{
                return response()->json([
                    'status'=>400,
                    'file'=>$file,
                    'data'=>$data,
                    'supplier_id'=>$req->supplier_id,
                    'bill_no'=>$req->bill_no,
                ]);
            }

        }

    }

    public function oldsavePurchaseEntryExcel(Request $req)
    {
        if($req->hasfile('pt_file')){
            $fileName = time() . '_' . $req->supplier_id . '_' . $req->file('pt_file')->getClientOriginalName();
            $filePath = $req->file('pt_file')->storeAs('files', $fileName, 'public');
        }

        $storage_path = storage_path('app/public/files/'.$fileName);
        // $storage_path = storage_path('app/public/files/demo.csv');

        $csv_data = [];
        $data = [];
        $start_row = 0;
        if (($csv_file = fopen($storage_path, "r")) !== FALSE) {
            while (($read_data = fgetcsv($csv_file, 1000, ",")) !== FALSE) {
                // $column_count = count($read_data);
                $start_row++;
                if($start_row == 1) continue;
                // for ($c=0; $c < $column_count; $c++) {
                //     $data = [
                //         'name'=> $read_data[0],
                //         'mobile'=> $read_data[1],
                //         'email'=> $read_data[2],
                //     ];
                // }
                $data = [
                    'name'=> $read_data[0],
                    'mobile'=> $read_data[1],
                    'email'=> $read_data[2],
                ];
                // $data = [
                //     'bill_date'=> $read_data[0],
                //     'bill_no'=> $read_data[1],
                //     'category'=> $read_data[2],
                //     'sub_category'=> $read_data[3],
                //     'brand'=> $read_data[4],
                //     'style_no'=> $read_data[5],
                //     'color'=> $read_data[6],
                //     'size'=> $read_data[7],
                //     'qty'=> $read_data[8],
                //     'price'=> $read_data[9],
                //     'mrp'=> $read_data[10],
                // ];

                $csv_data[] = $data ;
                
            }
            fclose($csv_file);
        }

        $count = 0;
        $detail = array();
        foreach ($csv_data as $key => $list) {
            $count = $count+1;
            // $dataa = [
            //     'cate'=>$list->category,
            // ];

            // $detail[] = $dataa;
        }

        return response()->json([
            'status'=>200,
            'csv_data'=>$csv_data,
            'column_header'=>$column_header,
            'labels'=>$labels,
            'data_array'=>$data_array,
            'final_data'=>$final_data,
            'data'=>$data,
        ]);

    }

  
}
