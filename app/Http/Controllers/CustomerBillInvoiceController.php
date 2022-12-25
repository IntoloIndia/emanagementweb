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
use App\Models\CustomerPoint;
use Validator;

class CustomerBillInvoiceController extends Controller
{
    public function index(){
        $products = PurchaseEntry::all();
        $product_barcode = PurchaseEntryItem::all();
        $sizes = Size::all();
        // $customers_billing = CustomerBill::all();
        $customers_billing = CustomerBill::join('customers','customers.id','=','customer_bills.customer_id')->get([
            'customers.*','customer_bills.*'
        ]);
        
        $months = Month::all();
        $cities = City::all();
        $users = User::all();
        // $allSales = CustomerBillInvoice::all();
        // $customers_billing = Customer::join('billings','billings.customer_id','=','customers.id')
        // $customers_billing = Billing::join('customers','customers.id','=','billings.customer_id')
        // $customers_billing = Billing::join('products','products.id','=','billings.product_id')
                        // ->groupBy('customers.customer_name')
                    // ->get(['customers.*','billings.amount','billings.qty']);
                    // ->get(['customers.*','billings.amount','billings.qty']);
                    // print_r($customers_billing);

            // print_r($allSales); 
            // print_r("<per>");   
                    
        return view('customer_bill_invoices',[ 
            'products'=> $products,
            'product_barcode'=> $product_barcode,
            'sizes' => $sizes,
            'customers_billing' => $customers_billing,
            'months' => $months,
            'cities' => $cities,
            'users' => $users
            // 'allSales' => $allSales,
        ]);
        
    }

     function saveOrder(Request $req)
    {
        // return $req;
        $validator = Validator::make($req->all(),[
            'customer_name'=>'required|max:191',
            // 'mobile_no'=>'required|unique:customers,mobile_no,'.$req->input('mobile_no'),
            'birthday_date'=>'required|max:191',
            'month_id'=>'required|max:191',
            'state_type'=>'required|max:191',
            // 'city_id'=>'required|max:191',
            // 'gst_no'=>'required|max:191',

        ]);

        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages("plz  all field required"),
            ]);

        }else{
            
            $customer_id = 0;
            
            $data = Customer::where(['mobile_no'=>$req->input('mobile_no')])->first('id');
            
            if ($data == null) {

                $model = new Customer;
                $model->customer_name = $req->input('customer_name');
                $model->mobile_no = $req->input('mobile_no');
                $model->birthday_date = $req->input('birthday_date');
                $model->month_id = $req->input('month_id');
                $model->anniversary_date = $req->input('anniversary_date');
                $model->state_type = $req->input('state_type');
                $model->employee_id = $req->input('employee_id');
                $model->city_id = $req->input('city_id');
                $model->gst_no = $req->input('gst_no');
                $model->date = date('Y-m-d');
                $model->time = date('g:i A');
                $model->save();
                
                $customer_id = $model->id;

            }else{
                $customer_id = $data->id;
            }
            $new_point =0;
            $total_amount = $req->input('total_amount');
            $redeem_point = $req->input('redeem_point');

            $customer_point = CustomerPoint::where(['customer_id'=>$customer_id])->first(['id','total_points']);
                
            if ($customer_point) {
                if ( $redeem_point > 0) {
                    $new_total = ($total_amount - $redeem_point) ; 
                    $new_point = ($new_total * 10) / 100 ;
                    $remain_point = $customer_point->total_points - $redeem_point ;
                    $total_new_point = $remain_point + $new_point ;
                }
             
                else{
                    $new_point = ($total_amount * 10) / 100 ;
                    $total_new_point = $customer_point->total_points + $new_point ;
                }
                $cutomerModal = CustomerPoint::find($customer_point->id);
                $cutomerModal->total_points = $total_new_point;
                $cutomerModal->save();
            }else{
                $total_new_point = ($total_amount * 10) / 100 ;
                $cutomerModal = new CustomerPoint;
                $cutomerModal->customer_id = $customer_id;
                $cutomerModal->total_points = $total_new_point;
                $cutomerModal->save();
            }

              // customer bills tables insert
              $invoice_no = rand(000001,999999);
              $billmodel = new CustomerBill;
              $billmodel->invoice_no = $invoice_no;
              $billmodel->customer_id = $customer_id;   
              $billmodel->total_amount = $total_amount;
              $billmodel->earned_point = $total_new_point;
              $billmodel->redeem_point = $redeem_point;
              $billmodel->bill_date = date('Y-m-d');
              $billmodel->bill_time = date('g:i A');
              $billmodel->save();
           
             
               
                $product_id = $req->input('product_id');
                $product_code = $req->input('product_code');
                $price = $req->input('price');
                $qty = $req->input('qty');
                $size = $req->input('size');
                $amount = $req->input('amount');
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
                    $item->product_code = $product_code[$key];
                    $item->price = $price[$key];
                    $item->qty = $qty[$key];
                    $item->size = $size[$key];
                    $item->amount = $amount[$key];
                    $item->discount_amount = $discount_amount[$key];
                    $item->taxfree_amount = $taxfree_amount[$key];
                    $item->sgst = $sgst[$key];
                    $item->cgst = $cgst[$key];
                    $item->igst= $igst[$key];
                    $item->date = date('Y-m-d');
                    $item->time = date('g:i A');
                    $item->save();
                } 

                return response()->json([   
                    'bill_id'=>$billmodel->id,
                    'status'=>200,
                    ' total_new_point'=>$total_new_point
                ]);
            }
        }
    }
            

    
    public function getCumosterData($mobile_no)
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
            return response()->json([
                'status'=>200,
                'customersData'=>$customersData,
                'total_points'=> $total_points
            ]);
        }else{
            return response()->json([
                'status'=>404,
                'msg'=>"Data not found",
                // 'total_points'=>$total_points
            ]);
        }
        
     

    }

    public function generateInvoice($bill_id)
    {
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
                    $html .="<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                $html .="</div>";
                    $html .="<div class='modal-body' id='invoiceModalPrint' style='border:1px solid black'>";

                        $html .="<div class='row mb-1'>";
                                $html .="<div class='col-md-3 '>";
                                    $html .="<span></span><br>";
                                    $html .="<span>GST NO: <small>41256668</small></span><br>";
                                    $html .="<span></span><br>";
                                $html .="</div>";
                            $html .="<div class='col-md-6 text-center'>";
                                    $html .="<span>SALES INVOICE</span><br>";
                                    $html .="<span>ERENOWN CLOTHING CO </span><br>";
                                    $html .="<span>Shop no.8-9,Ground Floor Samdariya Mall </span><br>";
                                    $html .="<span>Jabalpur -482002 </span><br>";
                            $html .="</div>";
                            $html .="<div class='col-md-3' >";
                                    $html .="<span>Phone no: 0761-4047699</span><br>";
                                    $html .="<span></span><br>";
                                    $html .="<span>Mobile no : 09826683399<small></small></span><br>";
                                    $html .="<span></span><br>";
                            $html .="</div>";
                        $html .="</div>";
                        $html .="<div class='row '>";
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
                            $html .="<span>Invoice No : <small class='float-end'>".$bills->invoice_no."</small></span><br>";
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
                                        $html .="<th>Qty</th>";
                                        $html .="<th>Size</th>";
                                        // $html .="<th>Color</th>";
                                        $html .="<th>MRP</th>";
                                        // $html .="<th>Rate</th>";
                                        $html .="<th>Disc</th>";
                                        $html .="<th>Taxable</th>";
                                        $html .="<th>CGST</th>";
                                        $html .="<th>SGST</th>";
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
                                foreach ($bill_invoise as $key => $list) {
                                    // dd($list);
                                    $html .="<tr>";
                                        $html .="<td>".++$key."</td>";
                                        $html .="<td>".ucwords($list->sub_category)."</td>";
                                        $html .="<td>".$list->qty."</td>";
                                        $html .="<td>".$list->size."</td>";
                                        // $html .="<td>".$list->color."</td>";
                                        $html .="<td>".$list->price."</td>";
                                        // $html .="<td>".$list->price."</td>";
                                        $html .="<td>0.00</td>";
                                        $html .="<td>".$list->taxfree_amount."</td>";
                                        $html .="<td>".$list->cgst."</td>";
                                        $html .="<td>".$list->sgst."</td>";
                                        $html .="<td>".$list->igst."</td>";
                                        $html .="<td>".$list->amount."</td>";
                                    $html .="</tr>";
                                // $total_amount =  $list->total_amount;
                              
                                $total_cgst =  $total_cgst + $list->cgst;
                                $total_sgst =  $total_sgst+ $list->sgst;
                                $total_igst =  $total_igst+ $list->igst;
                                $taxfree_amount =  $taxfree_amount+ $list->taxfree_amount;

                                }
                                    
                                $html .="</tbody>";
                                $html .="<tfoot>";
                                    $html .="<tr>";
                                    $html .="<td colspan='2'></td>";
                                    $html .="<td>".$key."</td>";
                                        $html .="<td colspan='2'></td>";
                                        $html .="<td><b>Total :</b></td>";
                                        $html .="<td>".$taxfree_amount."</td>";
                                        $html .="<td>".$total_sgst."</td>";
                                        $html .="<td>".$total_cgst."</td>";
                                        $html .="<td>".$total_igst."</td>";
                                        $html .="<td>".$bills->total_amount."</td>";
                                    $html .="</tr>";
                                $html .="</tfoot>";
                            $html .="</table>";
                            $html .="</div>";
                        $html .="</div>";

                        $html .="<div class='row'>";
                        $html .="<div class='col-md-8'>";
                        $html .="<span class='float-start'>Amount of Tax Subject to Reverse Change :</span><br>";
                        $html.="<div class='mt-3' style='width:300px;height:100px;border: 1px solid black;'>";
                        $html .="<span class='ml-2'>Online : 100</span><br>";
                        $html .="<span class='ml-2'>Cash :   100</span><br>";
                        $html .="<span class='ml-2'>Card :   100</span><br>";
                        $html .="<span class='ml-2'>Credit : 100</span><br>";
                        $html.="</div>";
                           
                        $html .="</div>";
                        $html .="<div class='col-md-2'>";

                                $html .="<span class='float-end'>TOTAL AMOUNT:</span><br>";
                                $html .="<span class='float-end'>DISCOUNT:</span><br>";
                                $html .="<span class='float-end'>CGST :</span> <br>";
                                $html .="<span class='float-end'>SGST : </span><br>";
                                $html .="<span class='float-end'>IGST : </span><br>";
                                $html .="<span class='float-end'>Point :</span> <br>";
                                // $html .="<span class='float-end'>R/OFF AMT :</span> <br>";
                                $html .="<span class='float-end'>GROSS.TOTAL : </span><br>";

                        $html .="</div>";
                        $html .="<div class='col-md-2'>";

                            $html .="<b class='text-center'>".$taxfree_amount."</b><br>";
                            $html .="<b class='text-center'>0.00</b><br>";
                            $html .="<b class='text-center'>".$total_cgst."</b><br>";
                            $html .="<b class='text-center'>".$total_sgst."</b><br>";
                            $html .="<b class='text-center'>".$total_igst."</b><br>";
                            $html .="<b class='text-center'>".$bills->redeem_point."</b><br>";
                            // $html .="<b class='text-center'>".$get_cutomer_data->total_amount."</b><br>";
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
                    $html .="<button type='button' id='printBtn' class='btn btn-primary btn-sm' order-id='".$bills->id."'>Print</button>";
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


