<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\Size;
use App\Models\CustomerBillInvoice;
use App\Models\CustomerBill;
use App\Models\Customer;
use App\Models\Month;
use App\Models\City;
use App\Models\User;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\CustomerPoint;
use App\Models\Offer;
use App\Models\ApplyOffer;
use App\Models\Brand;
use App\Models\BusinessDetails;
use App\MyApp;
use Validator;

class CustomerBillInvoiceController extends Controller
{
    public function index(){
        $products = PurchaseEntry::all();
        $customer_offer_report_data = CustomerBillInvoice::all();
        $product_barcode = PurchaseEntryItem::all();
        $sizes = Size::all();
        // $allOffers = Offer::all();
        $brands = Brand::all(); 
        // $allOffers = ApplyOffer::join('brands','brands.id','=','apply_offers.brand_id')
        //             ->select(['apply_offers.*','brands.brand_name'])->get();

        $allOffers = ApplyOffer::where(['status'=>MyApp::ACTIVE])
                ->leftjoin('brands', 'brands.id', '=', 'apply_offers.brand_id')
                ->leftjoin('sub_categories', 'apply_offers.sub_category_id', '=', 'sub_categories.id')
                ->orderBy('offer_from', 'DESC')
                ->orderBy('offer_to', 'DESC')
                ->select(['apply_offers.*','brands.brand_name','sub_categories.sub_category'])
                ->get();


        // $customers_billing = CustomerBill::all();
        $customers_billing = CustomerBill::join('customers','customers.id','=','customer_bills.customer_id')->get([
            'customers.*','customer_bills.*'
        ]);
        
        $months = Month::all();
        $cities = City::all();
        $users = User::all();
        $sales_return_data = SalesReturn::join('customers','customers.id','=','sales_returns.customer_id')
                                        ->join('sales_return_items','sales_return_items.sales_return_id','=','sales_returns.id')
                             ->select(['sales_returns.*','customers.customer_name','sales_return_items.amount'])->get();

        // dd($customer_offer_report_data);
        return view('customer_bill_invoices',[ 
            'products'=> $products,
            'product_barcode'=> $product_barcode,
            'sizes' => $sizes,
            'customers_billing' => $customers_billing,
            'months' => $months,
            'cities' => $cities,
            'users' => $users,
            'sales_return_data' => $sales_return_data,
            'allOffers' => $allOffers,
            'customer_offer_report_data' => $customer_offer_report_data,
        ]);
        
    }

     function saveOrder(Request $req)
    {
        
        $validator = Validator::make($req->all(),[
            // 'customer_name'=>'required',
            // 'mobile_no'=>'required|unique:customers,mobile_no,'.$req->input('mobile_no'),
            // 'birthday_date'=>'required|max:191',
            // 'month_id'=>'required|max:191',
            // 'state_type'=>'required|max:191',
            'employee_id'=>'required',
          
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages("plz all field required"),
            ]);

        }else{
            
            $customer_id = 0;
            
            $data = Customer::where(['mobile_no'=>$req->input('mobile_no')])->first(['id', 'advance_amount']);
            
            if ($data == null) {

                $model = new Customer;
                $model->customer_name = $req->input('customer_name');
                $model->mobile_no = $req->input('mobile_no');
                $model->birthday_date = $req->input('birthday_date');
                $model->month_id = $req->input('month_id');
                $model->anniversary_date = $req->input('anniversary_date');
                $model->state_type = $req->input('state_type');
                $model->city_id = $req->input('city_id');
                $model->gst_no = $req->input('gst_no');
                $model->date = date('Y-m-d');
                $model->time = date('g:i A');
                $model->save();
                
                $customer_id = $model->id;
                
            }else{
                //only for advance amount
                if ($data->advance_amount > 0) {
                    $data->birthday_date = $req->input('birthday_date');
                    $data->month_id = $req->input('month_id');
                    $data->anniversary_date = $req->input('anniversary_date');
                    $data->state_type = $req->input('state_type');
                    $data->gst_no = $req->input('gst_no');
                    $data->date = date('Y-m-d');
                    $data->time = date('g:i A');
                    $data->save();
                } 
                $customer_id = $data->id;
            }

            $earn_point =0;
            $total_amount = $req->input('total_amount');
            $pay_online = $req->input('pay_online');
            $pay_cash = $req->input('pay_cash');
            $pay_card = $req->input('pay_card');
            $balance_amount = $req->input('balance_amount');
            $redeem_points = $req->input('redeem_points');
            $credit_note_amount = $req->input('credit_note_amount');
            
            
            $customer = CustomerPoint::where(['customer_id'=>$customer_id])->first(['id','total_points']);
            
            if ($customer) {
                if ( $redeem_points > 0) {
                    $new_total = ($total_amount - $redeem_points) ; 
                    $earn_point = ($new_total * 10) / 100 ;
                    
                    $remain_point = $customer->total_points - $redeem_points ;
                    $total_new_point = $remain_point + $earn_point ;
                }else{
                    $earn_point = ($total_amount * 10) / 100 ;
                    
                    $total_new_point = $customer->total_points + $earn_point ;
                }
                
                $cutomerModal = CustomerPoint::find($customer->id);
                $cutomerModal->total_points = $total_new_point;
                $cutomerModal->save();
                
            }else{
                
                $earn_point = ($total_amount * 10) / 100 ;
                $cutomerModal = new CustomerPoint;
                $cutomerModal->customer_id = $customer_id;
                $cutomerModal->total_points = $earn_point;
                $cutomerModal->save();
            }
            
              // customer bills tables insert
            
              $billmodel = new CustomerBill;
              $billmodel->user_id = session('LOGIN_ID');   
              $billmodel->customer_id = $customer_id;   
              $billmodel->total_amount = $total_amount;
              $billmodel->credit_note_amount = $credit_note_amount;
              $billmodel->pay_online = $pay_online;
              $billmodel->pay_cash = $pay_cash;
              $billmodel->pay_card = $pay_card;
              $billmodel->balance_amount = $balance_amount;
              $billmodel->earned_point = $earn_point;
              $billmodel->redeem_point = $redeem_points;
              $billmodel->bill_date = date('Y-m-d');
              $billmodel->bill_time = date('g:i A');
              $billmodel->save();
              
              $product_id = $req->input('product_id');
              $purchase_entry_item_id = $req->input('purchase_entry_item_id');
              $product_code = $req->input('product_code');
              $price = $req->input('price');
              $qty = $req->input('qty');
              $size = $req->input('size');
              $amount = $req->input('amount');
              $employee_id = $req->input('employee_id');
            //   $offer_id = $req->input('offer_id');
            //   $discount_percentage = $req->input('discount_percentage');
              $discount_amount = $req->input('discount_amount');
              $taxfree_amount = $req->input('taxfree_amount');
              $sgst = $req->input('sgst');
              $cgst = $req->input('cgst');
              $igst = $req->input('igst');
           
              
              // $result = manageCustomerPoint($customer_id, $redeem_point,$total_amount);
              
            if($billmodel->save()){
                
                foreach ($product_id as $key => $list) {
                    // $categories = Customer::find($product_code[$key]);
                    $item = new CustomerBillInvoice;
                    
                    $item->bill_id = $billmodel->id;
                    $item->product_id = $product_id[$key];
                    $item->purchase_entry_item_id = $purchase_entry_item_id[$key];
                    $item->product_code = $product_code[$key];
                    $item->price = $price[$key];
                    $item->qty = $qty[$key];
                    $item->size = $size[$key];
                    $item->amount = $amount[$key];
                    $item->discount_amount = $discount_amount[$key];
                    // $item->discount_percentage = $discount_percentage[$key];
                    $item->taxfree_amount = $taxfree_amount[$key];
                    $item->sgst = $sgst[$key];
                    $item->cgst = $cgst[$key];
                    $item->igst= $igst[$key];
                    $item->employee_id = $employee_id[$key];
                    // $item->offer_id = $offer_id[$key];
                    $item->date = date('Y-m-d');
                    $item->time = date('g:i A');
                    $item->save();

                    //get purchase_entry_id for decrease stock - manage stock
                    $purchase_item = PurchaseEntryItem::where(['barcode'=>$product_code[$key]])->first('purchase_entry_id');
                    $stock_type = MyApp::MINUS_MANAGE_STOCK;
                    manageStock($stock_type, $purchase_item->purchase_entry_id, $size[$key], $qty[$key]);
                } 

                    $credit_note_id = $req->input('credit_note_id');
                    if ( $credit_note_id) {
                        
                        foreach($credit_note_id as $key => $list) {
                            $sales_return_model = SalesReturn::find($list);
                            $sales_return_model->apply_bill_id = $billmodel->id;
                            $sales_return_model->status = MyApp::USED;
                            $sales_return_model->save();
                        } 
                    }

                    if($credit_note_amount > $total_amount){

                        $model_item = new SalesReturn;
                        $model_item->bill_id = $billmodel->id;
                        $model_item->customer_id = $customer_id;
                        $model_item->credit_note_total_amount = $total_amount;
                        $model_item->create_date = date('Y-m-d');
                        $model_item->create_time = date('g:i A');
                        $model_item->save();
                    }

                         
                return response()->json([   
                    'bill_id'=>$billmodel->id,
                    'status'=>200,
                ]);
            }
        }
    }
            

    
    public function getCustomerData($mobile_no)
    {
        $customersData = Customer::where(['mobile_no'=>$mobile_no])->first();
        if($customersData){

            $customer_id =  $customersData->id;
            $points =  CustomerPoint::where(['customer_id'=>$customer_id])->first('total_points');
            $total_points = 0;
            if($points != "")
            {
                $total_points = $points->total_points; 
            }

            $credit_note_data =  SalesReturn::where(['customer_id'=>$customer_id])
                                ->where(['status'=>MyApp::ACTIVE])
                                ->select(['sales_returns.*'])
                                ->get();
     
        //    dd($credit_note_data);
        $html = "";
         $html .= "<div class='row'>";
         $html .= "<div class='row'>";
            $html .= "<table class='table'>";
                // $html .= "<thead>";
                //     $html .= "<tr>";
                //         $html .= "<th></th>";
                //         $html .= "<th>SN</th>";
                //         $html .= "<th>Check</th>";
                //         $html .= "<th>Date</th>";
                //         // $html .= "<th>Time</th>";
                //         $html .= "<th>Amount</th>";
                //     $html .= "</tr>";
                // $html .= "</thead>";
                $html .= "<tbody>";
                    // $credit_note_total_amount =0;
                    foreach ($credit_note_data as $key => $list) {
                    // $credit_note_amount = SalesReturnItem::where(['sales_return_id'=>$list->id])->get()->sum('amount');
                        $html .= "<tr>";
                            $html .= "<td></td>";
                            $html .= "<td>" . ++$key . "</td>";
                            $html .= "<td> <div class='form-check '>
                             <input class='form-check-input credit_note' id='notedata' type='checkbox' name='credit_note_id[]' value='".$list->id."'credit-note-amount='". $list->credit_note_total_amount ."' >
                            </div></td>";
                            $html .= "<td>" .$list->create_date. "</td>";
                            $html .= "<td>" . $list->credit_note_total_amount ."</td>";           
                        $html .= "</tr>";
                        // $credit_note_total_amount = $credit_note_total_amount + $credit_note_amount;
                    }
                $html .= "<tbody>";
                //     $html .="<tfoot>";
                //     $html .="<tr>";
                //         $html .="<td colspan='4'><b>Total :</b></td>";
                //         $html .="<td><b>".$credit_note_total_amount."</b></td>";
                //     $html .="</tr>";
                // $html .="</tfoot>";
            $html .= "</table>";
        $html .= "</div>"; 

            return response()->json([
                'status'=>200,
                'customersData'=>$customersData,
                'total_points'=> $total_points,
                'html'=> $html
            ]);
        }else{
            return response()->json([
                'status'=>404,
                'msg'=>"Data not found",
                // 'html'=> "dat"
                // 'total_points'=>$total_points
            ]);
        }
        
     

    }

    public function generateInvoice($bill_id)
    {
        $business_detail = BusinessDetails::first();
        $bills = CustomerBill::join('customers','customer_bills.customer_id','=','customers.id')
            ->join('cities','customers.city_id','=','cities.id')
            ->where('customer_bills.id',$bill_id)
            ->select(['customer_bills.*','customers.customer_name','customers.mobile_no','cities.city'])
            ->first(); 

        $bill_invoise = CustomerBillInvoice::join('sub_categories','customer_bill_invoices.product_id','=','sub_categories.id')
            ->where('bill_id' ,$bill_id)
            ->select('customer_bill_invoices.*','sub_categories.sub_category')
            ->get(); 

        $html = "";
        $html .="<div class='modal-dialog modal-lg'>";
            $html .="<div class='modal-content'>";
                $html .="<div class='modal-header'>";
                    $html .="<button type='button' class='btn-close' id='reload_invoice_print' data-bs-dismiss='modal' aria-label='Close'></button>";
                $html .="</div>";
                $html .="<div class='modal-body' id='print_invoice' style='border:1px solid black'>";

                    $html .="<div class='row mb-1' >";
                            $html .="<div class='col-md-3 '>";
                                $html .="<span></span><br>";
                                $html .="<span><b>GST NO : </b><small>".$business_detail->gst."</small></span><br>";
                                $html .="<span></span><br>";
                            $html .="</div>";
                        $html .="<div class='col-md-6 text-center'>";
                                $html .="<span><b>".$business_detail->business_name."</b></span><br>";
                                $html .="<span>".$business_detail->company_address."</span><br>";
                                
                        $html .="</div>";
                        $html .="<div class='col-md-3' >";
                                $html .="<p><b>Phone no : </b>0761-4047699</p><br>";                                  
                                $html .="<p><b>Mobile no : </b>".$business_detail->mobile_no."</p>";                                  
                        $html .="</div>";
                    $html .="</div>";
                    
                    $html .="<div class='row'>";
                        $html .="<div class='col-md-7' style='border:1px solid black'>";
                        $html .="<span>Customer name: <small>".ucwords($bills->customer_name)."</small></span><br>";
                        $html .="<span>Location : <small>". ucwords($bills->city)."</small></span><br>";
                        $html .="<span>Mobile no : <small>".$bills->mobile_no."</small></span><br>";
                        // $html .="<span>State code  : <small>0761</small></span><br>";
                        // $html .="<span>Payment : <small>".$payment_mode."</small></span> ";
                        $html .="</div>";
                        // $html .="<div class='col-md-2' style='border:1px solid black'>";
                        // // $html .="<span class=''>Payment :<br/>";
                        // $html .="<span class=''>online :<br/>";
                        // $html .="<span class=''>cash :<br/>";
                        // $html .="<span class=''>card :<br/>";
                        // $html .="<span class=''>credit :<br/>";
                        // $html .="</div>";
                        $html .="<div class='col-md-5' style='border:1px solid black'>";
                        $html .="<span class=''>Date : <small class='float-end'>".date('d/M/Y', strtotime($bills->bill_date))."</small></span><br>";
                        $html .="<span>Invoice No : <h5 class='float-end'>".$bills->id."</h5></span><br>";
                            // $html .="<span class=''>Attent By : <small class='float-end'></small></span> ";
                        $html .="</div>";
                    $html .="</div>";
                    // $html .="<hr>";

                    $html .="<div class='row mt-2'>";
                        $html .="<div class='table-responsive'>";
                        $html .="<table class='table table-bordered'>";
                    
                            $html .="<thead>";
                                $html .="<tr>";
                                    $html .="<th>#</th>";
                                    $html .="<th>Item Name</th>";
                                    $html .="<th>Size</th>";
                                    $html .="<th>Qty</th>";
                                    // $html .="<th>Color</th>";
                                    $html .="<th>MRP</th>";
                                    // $html .="<th>Rate</th>";
                                    $html .="<th>Disc</th>";
                                    $html .="<th>Taxable</th>";
                                    $html .="<th>SGST</th>";
                                    $html .="<th>CGST</th>";
                                    $html .="<th>IGST</th>";
                                    $html .="<th>Total</th>";
                                $html .="</tr>";
                            $html .="</thead>";
                            $html .="<tbody>";
                            // $total_amount = 0;
                            $total_cgst = 0;
                            $total_sgst = 0;
                            $total_igst = 0;
                            $taxfree_amount =0;
                            $total_qty = 0;
                            $total_discount_amount = 0;

                            foreach ($bill_invoise as $key => $list) {
                                // dd($list);
                                $html .="<tr>";
                                    $html .="<td>".++$key."</td>";
                                    $html .="<td>".ucwords($list->sub_category)."</td>";
                                    $html .="<td>".$list->size."</td>";
                                    $html .="<td>".$list->qty."</td>";
                                    // $html .="<td>".$list->color."</td>";
                                    $html .="<td>".$list->price."</td>";
                                    // $html .="<td>".$list->price."</td>";
                                    $html .="<td>".$list->discount_amount."</td>";
                                    $html .="<td>".$list->taxfree_amount."</td>";
                                    $html .="<td>".$list->cgst."</td>";
                                    $html .="<td>".$list->sgst."</td>";
                                    $html .="<td>".$list->igst."</td>";
                                    $html .="<td>".$list->amount."</td>";
                                $html .="</tr>";
                            // $total_amount =  $list->total_amount;
                            $total_qty = $total_qty + $list->qty;
                            $total_cgst =  $total_cgst + $list->cgst;
                            $total_sgst =  $total_sgst+ $list->sgst;
                            $total_igst =  $total_igst+ $list->igst;
                            $taxfree_amount =  $taxfree_amount+ $list->taxfree_amount;
                            $total_discount_amount =  $total_discount_amount+ $list->discount_amount;

                            }
                                
                            $html .="</tbody>";
                            $html .="<tfoot>";
                                $html .="<tr>";
                                $html .="<td colspan='3'></td>";
                                $html .="<td>".$total_qty."</td>";
                                    $html .="<td colspan='1'><b>Total :</b></td>";
                                    $html .="<td><b>".$total_discount_amount."</b></td>";
                                    $html .="<td><b>".$taxfree_amount."</b></td>";
                                    $html .="<td><b>".$total_sgst."</b></td>";
                                    $html .="<td><b>".$total_cgst."</b></td>";
                                    $html .="<td><b>".$total_igst."</b></td>";
                                    $html .="<td><b>".$bills->total_amount."</b></td>";
                                $html .="</tr>";
                            $html .="</tfoot>";
                        $html .="</table>";
                        $html .="</div>";
                    $html .="</div>";

                    $html .="<div class='row'>";
                    $html .="<div class='col-md-8'>";
                    $html .="<span class='float-start'><b>Amount of Tax Subject to Reverse Charge :</b></span><br>";
                    $html.="<div class='mt-3' style='width:300px;height:100px;border: 1px solid black;'>";
                    $html .="<span class='ml-2'>Online :</span> <span class='ml-4'><b>".$bills->pay_online."</b></span><br>";
                    $html .="<span class='ml-2'>Cash : </span><span class='ml-4'><b>".$bills->pay_cash."</b></span><br>";
                    $html .="<span class='ml-2'>Card : </span><span class='ml-4'><b>".$bills->pay_card."</b></span><br>";
                    $html .="<span class='ml-2'>Credit : </span><span class='ml-4'><b>".$bills->balance_amount."</b></span><br>";
                    $html.="</div>";
                        
                    $html .="</div>";
                    $html .="<div class='col-md-2'>";

                            $html .="<span class='float-end'>TOTAL AMOUNT:</span><br>";
                            $html .="<span class='float-end'>DISCOUNT:</span><br>";
                            $html .="<span class='float-end'>SGST : </span><br>";
                            $html .="<span class='float-end'>CGST :</span> <br>";
                            $html .="<span class='float-end'>IGST : </span><br>";
                            $html .="<span class='float-end'>Points :</span> <br>";
                            $html .="<span class='float-end'>Note amount :</span> <br>";
                            $html .="<span class='float-end'>GROSS.TOTAL : </span><br>";

                    $html .="</div>";
                    $html .="<div class='col-md-2'>";

                            $html .="<b class='text-center'>".$taxfree_amount."</b><br>";
                            $html .="<b class='text-center'>".$total_discount_amount."</b><br>";
                            $html .="<b class='text-center'>".$total_cgst."</b><br>";
                            $html .="<b class='text-center'>".$total_sgst."</b><br>";
                            $html .="<b class='text-center'>".$total_igst."</b><br>";
                            $html .="<b class='text-center'>".$bills->redeem_point."</b><br>";
                            // $html .="<b class='text-center'>0.00</b><br>";
                            $html .="<b class='text-center'>".$bills->credit_note_amount."</b><br>";
                            $html .="<b class='text-center'>".$bills->total_amount."</b><br>";

                    $html .="</div>";
                    $html .="</div>";

                    
                    $html .="<hr>";
                    $html .="<div class='row text-center'>";
                        $html .="<h6><b>Thank  Have a Nice Day </b></h6>";
                        $html .="<small>Visit Again !</small>";
                    $html .="</div>";

                    // $html .="</div>";
                
                $html .="</div>";

                $html .="<div class='modal-footer'>";
                    $html .="<button type='button' class='btn btn-secondary btn-sm' data-bs-dismiss='modal' id='reload_invoice_print'>Close</button>";
                    $html .="<button type='button' id='printBillInvoiceBtn' class='btn btn-primary btn-sm' order-id='".$bills->id."' print-section='print_invoice'>Print</button>";
                $html .="</div>";

            $html .="</div>";
        $html .="</div>";

        return response()->json([
            'status'=>200,
            'bills'=>$bills,
            'bill_invoise'=>$bill_invoise,
            'html'=>$html
        ]);
  }   

  

}


