<?php
    use Illuminate\Support\facades\DB;
    // use App\Models\Order;
    use App\Models\SubCategory;
    use App\Models\PurchaseEntry;
    use App\Models\PurchaseEntryItem;
    use App\Models\Supplier;
    use App\Models\AlterationItem;
    use App\Models\PurchaseReturnItem;
    use App\Models\CustomerPoint;
    use App\Models\CustomerBill;
    use App\Models\Purchase;

    // use App\MyApp;

    function subCategoryItems($category_id){
        $subCategory_item = SubCategory::where(['category_id'=>$category_id])->get();
        return $subCategory_item; 
    }

    // function purchaseReturnItemsdata($supplier_id){
    //     $purchase_return_item_data = PurchaseReturnItem::where(['supplier_id'=>$supplier_id])->get();
    //     return $purchase_return_item_data; 
    // }

    function updateProductStatus($product_id){
        $purchase = PurchaseEntry::find($product_id);
        $purchase->status = MyApp::SOLD;
        $purchase->save();
        return 200; 
    }

    function supplierCode(){
        $suppliers = Supplier::all();
        // $count = count($suppliers);
        if (count($suppliers) == 0) {
            $supplier_code = 1;
        }else{
            $supplier = Supplier::latest('id')->first();
            $count = $supplier->id ;
            $supplier_code = ($count + 1);
        }
        
        return $supplier_code;
    }

    function calculateGst($state_type, $taxable){

        $sgst = 0;
        $cgst = 0;
        $igst = 0;
        
        if ($state_type == 1) {
            if ($taxable < 1000) {
                $sgst = ($taxable * 2.5 / 100);
                $cgst = ($taxable * 2.5 / 100);
            }else{
                $sgst = ($taxable * 6 / 100) ;
                $cgst = ($taxable * 6 / 100) ;
            }
        }else{
            if ($taxable < 1000) {
                $igst = ($taxable * 5 / 100) ;
            }else{
                $igst = ($taxable * 12 / 100) ;
            }
        }

        return $result = [
            'sgst'=>round($sgst ,2 , PHP_ROUND_HALF_EVEN),
            'cgst'=>round($cgst ,2 , PHP_ROUND_HALF_EVEN),
            'igst'=>round($igst ,2 , PHP_ROUND_HALF_EVEN)
        ] ;
    }

    function getPurchaseEntryItems($purchase_entry_id)
    {
        $items = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id])->get();
        return $result = [
            'status'=>200,
            'items'=>$items
        ] ;
    }

    function getMemberShip($customer_id)
    {
        $total_amount = CustomerBill::where(['customer_id'=>$customer_id])->sum('total_amount');

        if($total_amount  <= MyApp::SILVER_AMOUNT){
            $membership = MyApp::SILVER;
        }elseif($total_amount > MyApp::SILVER_AMOUNT && $total_amount  <= MyApp::GOLDEN_AMOUNT){
            $membership = MyApp::GOLDEN;
        }else{
            $membership = MyApp::PLATINUM;
        } 
        return $membership;
    }

    function ManageStockItemQty($id)
    {
        $purchase_entry = PurchaseEntry::where(['category_id'=>$id])->get('id');
        $manage_stock_qty = array();
        foreach ($purchase_entry as $key => $list) {
            $manage_stock_qty[] = PurchaseEntryItem::where(['purchase_entry_id'=>$list->id])->get()->sum('qty');
        }
        $total_qty = array_sum($manage_stock_qty);
        return $total_qty;
    }

    function ManageSubCategoryQty($id)
    {
        $purchase_entry = PurchaseEntry::where(['sub_category_id'=>$id])->get('id');
        $manage_sub_category_qty = array();
        foreach ($purchase_entry as $key => $list) {
            $manage_sub_category_qty[] = PurchaseEntryItem::where(['purchase_entry_id'=>$list->id])->get()->sum('qty');
        }
        $total_qty = array_sum($manage_sub_category_qty);
        return $total_qty;
    }



    // show alter item
    // function getAlterationItem($alteration_voucher_id){
    //     $alteration_items = AlterationItem::join('sub_categories','alteration_items.product_id','=','sub_categories.id')
    //         ->where(['alteration_voucher_id'=>$alteration_voucher_id])->get(['sub_categories.sub_category','alteration_items.item_qty']);
    //     return $alteration_items;
    // }


    // function invoiceNo(){
    //     $orders_count = Order::count();
    //     // $orders_count = Order::latest()->first()->id;

    //     $count = $orders_count + 1 ;
    //     $invoice_no = "SH".$count ."D";
    //     return $invoice_no;
    // }

