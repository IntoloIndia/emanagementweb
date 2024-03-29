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
    use App\Models\ManageStock;
    use App\Models\StyleNo;
    use App\Models\Offer;
    use App\Models\ApplyOffer;
    use App\Models\SystemKey;
   

    // use App\MyApp;

    function generateRandomString($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function checkIsActive($user_id, $user_role_id)
    {
        $data = SystemKey::where(['user_id'=>$user_id, 'user_role_id'=>$user_role_id])->get(['is_active','key','created_at']);
        // $data = SystemKey::where(['user_id'=>$user_id, 'user_role_id'=>$user_role_id])->value('is_active');
        return $data;
    }

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
    // $qty =0;
    function calculateDiscount($price, $discount,$qty)
    {
        $total_discount_amount = 0;
        if ($discount > 0) {
            $discount_amount = ($price * $discount) / 100;
            $taxable = ($price - $discount_amount) * $qty;
            $total_discount_amount = ($discount_amount * $qty);
            
        }else{
            $taxable = $price * $qty;
        }
        return $result = [
            'taxable'=>$taxable,
            'total_discount_amount'=>$total_discount_amount,
        ]; 

       
        // return $discount_amount;    
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

    function getStockItems($purchase_entry_id)
    {
        $items = ManageStock::where('purchase_entry_id',$purchase_entry_id)->get();
        $total_quantity = ManageStock::where('purchase_entry_id',$purchase_entry_id)->get()->sum('total_qty');
        return $result = [
            'status'=>200,
            'items'=>$items,
            'total_quantity'=>$total_quantity
        ] ;
    }

    function getItemsDetail($purchase_entry_id, $size)
    {
        $item_detail = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id, 'size'=>$size])->first('price');
        return $result = [
            'price'=>$item_detail->price,
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

    function stockItemQtyByCategory($category_id)
    {
        $purchase_entry = PurchaseEntry::where(['category_id'=>$category_id])->get('id');
        $stock_qty = array();
        $stock_amount = array();
        foreach ($purchase_entry as $key => $list) {
            $stock_qty[] = ManageStock::where(['purchase_entry_id'=>$list->id])->get()->sum('total_qty');
            $stock_amount[] = PurchaseEntryItem::where(['purchase_entry_id'=>$list->id])->get()->sum('taxable');
        }
        
        return $result = [
            'total_qty'=>array_sum($stock_qty),
            'total_amount'=>array_sum($stock_amount)
        ] ;
        // $total_qty = array_sum($stock_qty);
        // return $total_qty;
      
    }
   
    function stockItemQtyBySubCategory($sub_category_id)
    {
        $purchase_entry = PurchaseEntry::where(['sub_category_id'=>$sub_category_id])->get('id');
        $stock_qty = array();
        $stock_amount = array();

        foreach ($purchase_entry as $key => $list) {
            $stock_qty[] = ManageStock::where(['purchase_entry_id'=>$list->id])->get()->sum('total_qty');
            $stock_amount[] = PurchaseEntryItem::where(['purchase_entry_id'=>$list->id])->get()->sum('taxable');
        }
        return $result = [
            'total_qty'=>array_sum($stock_qty),
            'total_amount'=>array_sum($stock_amount)
        ];
    }

    function getSumOfPurchaseEntryItems($purchase_entry_id)
    {
        $qty = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id])->get()->sum('qty');
        $amount = PurchaseEntryItem::where(['purchase_entry_id'=>$purchase_entry_id])->get()->sum('taxable');
        return $result = [
            'qty'=>$qty,
            'amount'=>$amount,
        ];
    }

    function manageStock($stock_type, $purchase_entry_id, $size, $qty){

        $data = ManageStock::where(['purchase_entry_id'=>$purchase_entry_id, 'size'=>$size])->first();

        if ($data != null) {

            $manageStock = ManageStock::find($data->id);
            if ($stock_type == MyApp::PLUS_MANAGE_STOCK) {
                $total_qty = ($data->total_qty + $qty);
            }elseif ($stock_type == MyApp::MINUS_MANAGE_STOCK) {
                $total_qty = ($data->total_qty - $qty);
            }
            
            $manageStock->total_qty = $total_qty;
            $manageStock->save();
            
        }else{
            
            $model = new ManageStock;
            $model->purchase_entry_id = $purchase_entry_id;
            $model->size = $size;
            $model->total_qty = $qty;
            $model->save();
        }
        
        return 'ok';

    }

    function getStyleNO($style_no_id)
    {
        $data = StyleNo::where(['id'=>$style_no_id])->pluck('style_no')->first();
        return $data;
    }

    function getOfferData($offer_id)
    {
        $data = Offer::where(['id'=>$offer_id])->first();
        return $data;
    }

    function getSalesPayment($customer_id)
    // function getSalesPayment($customer_id,$month)
    {
        $get_sales_payment = CustomerBill::join('customer_bill_invoices','customer_bills.id','=','customer_bill_invoices.bill_id')
            ->join('sub_categories','customer_bill_invoices.product_id','=','sub_categories.id')
            ->where('customer_id' ,$customer_id)           
            ->select('customer_bills.*','customer_bill_invoices.qty','customer_bill_invoices.size','customer_bill_invoices.price','customer_bill_invoices.amount','customer_bill_invoices.discount_amount','customer_bill_invoices.date','customer_bill_invoices.time','sub_categories.sub_category')
            ->get();
            return $get_sales_payment;
    }

    function getOffers($offer_type)
    {
        $data = Offer::where(['offer_type'=> $offer_type])->get();
        return $data;
    }

    function convertNumberToWords($amount){
        // $number = 190908100.25;
        $no = floor($amount);
        $point = round($amount - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array('0' => '', '1' => 'One', '2' => 'Two',
            '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
            '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
            '13' => 'Thirteen', '14' => 'Fourteen',
            '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
            '18' => 'Eighteen', '19' =>'Nineteen', '20' => 'Twenty',
            '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
            '60' => 'Sixty', '70' => 'Seventy',
            '80' => 'Sighty', '90' => 'Ninety');
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' And ' : null;
                $str [] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred
                    :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
            } else $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ? ". " . $words[$point / 10] . " " . $words[$point = $point % 10] : '' ; 

        if (floor( $amount ) != $amount) 
        {
            return $result . "Rupees" . $points . " Paise"; 
        }
        else{
            return $result . "Rupees";
        }
    }


    // function brandOfferItems($brand_id){
    //     $brand_offer_items = ApplyOffer::where(['brand_id'=>$brand_id])->get();
    //     return $brand_offer_items; 
    // }

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


    // 80000001010323
    // 80000001009771
    // 80000001006988
    // 80000001009772
    // 80000001009780

