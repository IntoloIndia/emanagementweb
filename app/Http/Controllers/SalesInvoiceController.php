<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseEntry;
use App\Models\Size;
use App\Models\SalesInvoice;
use App\Models\Customer;
use App\Models\Month;
use App\Models\City;
use App\Models\User;
use Validator;

class SalesInvoiceController extends Controller
{
    public function index(){
        $products = PurchaseEntry::all();
        $sizes = Size::all();
        $customers_billing = Customer::all();
        $months = Month::all();
        $cities = City::all();
        $users = User::all();
        // $allSales = SalesInvoice::all();
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
            'mobile_no'=>'required|unique:customers,mobile_no,'.$req->input('mobile_no'),
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
            $model = new Customer;
            $model->customer_name = $req->input('customer_name');
            $model->mobile_no = $req->input('mobile_no');
            $model->birthday_date = $req->input('birthday_date');
            $model->month_id = $req->input('month_id');
            $model->state_type = $req->input('state_type');
            $model->employee_id = $req->input('employee_id');
            $model->city_id = $req->input('city_id');
            $model->gst_no = $req->input('gst_no');
            $model->date = date('Y-m-d');
            $model->time = date('g:i A');



            $product_id = $req->input('product_id');
            $product_code = $req->input('product_code');
            $price = $req->input('price');
            $qty = $req->input('qty');
            $size = $req->input('size');
            $amount = $req->input('amount');
            $sgst = $req->input('sgst');
            $cgst = $req->input('cgst');
            $igst = $req->input('igst');
            $alteration_voucher = $req->input('alteration_voucher');
            
            

            if($model->save()){

                foreach ($product_id as $key => $list) {
                    
                    // $categories = Customer::find($product_code[$key]);

                    $item = new SalesInvoice;

                    $item->bill_id = $model->id;
                    $item->product_id = $product_id[$key];
                    $item->product_code = $product_code[$key];
                    $item->price = $price[$key];
                    $item->qty = $qty[$key];
                    $item->size = $size[$key];
                    $item->amount = $amount[$key];
                    $item->sgst = $sgst[$key];
                    $item->cgst = $cgst[$key];

                    $item->igst= $igst[$key];
                    $item->alteration_voucher = $alteration_voucher[$key];
                    $item->date = date('Y-m-d');
                    $item->time = date('g:i A');
                    $item->save();
                    if ($item->save()) {
                        // updateProductStatus($product_id[$key]);
                    }

                } 

                return response()->json([   
                    'status'=>200
                    

                ]);
            }
        }
    }
            

    public function getItemPrice($product_code)
    {
        $product = PurchaseEntry::where(['product_code'=>$product_code])->first();
        // print_r($product);
        // $product = Product::find($product_code);
                        
        return response()->json([
            'product'=>$product
        ]);

    }
    public function getCumosterData($mobile_no)
    {
        $customersData = Customer::where(['mobile_no'=>$mobile_no])->first();
        // $customersData = Customer::find($mobile_no);
     
        return response()->json([
            'status'=>200,
            'customersData'=>$customersData
        ]);

    }

    public function generateInvoice($customer_id)
    {
        

                 $get_cutomer_data =Customer::find($customer_id);
        
                $order_items =SalesInvoice::join('customers','customers.id','=','customer_bill_invoices.customer_id')->
                                    join('purchase_entries','purchase_entries.id','=','customer_bill_invoices.product_id')
                                    // join('sizes','sizes.id','=','customer_bill_invoices.size_id')
                                    // join('colors','colors.id','=','customer_bill_invoices.color_id')

                                    ->where('customer_bill_invoices.customer_id',$get_cutomer_data->id)
                            ->select(['customer_bill_invoices.*','customers.total_amount','purchase_entries.product'])->get(); 

                            
                    //  print_r($order_items);
                    // dd($order_items);  
                    // print_r($order_items);    
        //         ->get(['order_items.*','items.item_name','items.price' ]);

        //         if($order->payment_mode == MyApp::ONLINE){
        //             $payment_mode = "Online";
        //         }else{
        //             $payment_mode = "Cash";
        //         }


        $html = "";
        $html .="<div class='modal-dialog modal-lg'>";
            $html .="<div class='modal-content'>";
                $html .="<div class='modal-header'>";
                    $html .="<h5 class='modal-title' id='staticBackdropLabel'><b>$get_cutomer_data->customer_name</b></h5>";
                    $html .="<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                $html .="</div>";

                //  $html .="<div class='modal-body-wrapper'>";


                    $html .="<div class='modal-body' id='invoiceModalPrint' style='border:1px solid black'>";

                        // $html .="<div class='row text-center'>";
                        //     $html .="<h5><b>Mangaldeep </b></h5>";
                        //     $html .="<small>Jabalpur</small>";
                        // $html .="</div>";

                        $html .="<div class='row mb-1'>";
                                $html .="<div class='col-md-3 '>";
                                    $html .="<span></span><br>";
                                    $html .="<span>GST NO: <small>4125666</small></span><br>";
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
                        // $html .="<hr>";

                        $html .="<div class='row '>";
                            $html .="<div class='col-md-6' style='border:1px solid black'>";
                            $html .="<span>Customer name: <small>".$get_cutomer_data->customer_name."</small></span><br>";
                            $html .="<span>Location : <small>Jabalpur</small></span><br>";
                            $html .="<span>Mobile no : <small>".$get_cutomer_data->mobile_no."</small></span><br>";
                            $html .="<span>State code  : <small>0761</small></span><br>";
                            // $html .="<span>Payment : <small>".$payment_mode."</small></span> ";
                            $html .="</div>";
                            $html .="<div class='col-md-2' style='border:1px solid black'>";
                            $html .="<span class=''>CASH :<br/> <small><b>10000</b></small></span> ";
                            $html .="</div>";
                            $html .="<div class='col-md-4' style='border:1px solid black'>";
                            $html .="<span>Invoicen No : <small class='float-end'>".$get_cutomer_data->invoice_no."</small></span><br>";
                                $html .="<span class=''>Date : <small class='float-end'>".date('d/M/Y', strtotime($get_cutomer_data->date))."</small></span><br>";
                                $html .="<span class=''>Attent By : <small class='float-end'></small></span> ";
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
                                        $html .="<th>Color</th>";
                                        $html .="<th>MRP</th>";
                                        $html .="<th>Rate</th>";
                                        $html .="<th>Disc</th>";
                                        $html .="<th>Total</th>";
                                        $html .="<th>Taxable</th>";
                                        $html .="<th>CGST%</th>";
                                        $html .="<th>SGST%</th>";
                                    $html .="</tr>";
                                $html .="</thead>";
                                $html .="<tbody>";
                                foreach ($order_items as $key => $list) {
                                    // dd($list);
                                    $html .="<tr>";
                                        $html .="<td>".++$key."</td>";
                                        $html .="<td>".ucwords($list->product)."</td>";
                                        $html .="<td>".$list->qty."</td>";
                                        $html .="<td>".$list->size."</td>";
                                        $html .="<td>".$list->color."</td>";
                                        $html .="<td>".$list->price."</td>";
                                        $html .="<td>".$list->price."</td>";
                                        $html .="<td>".$list->price."</td>";
                                        $html .="<td>".$list->amount."</td>";
                                        $html .="<td>".$list->amount."</td>";
                                        $html .="<td>".$list->amount."</td>";
                                        $html .="<td>".$list->amount."</td>";
                                    $html .="</tr>";
                                }
                                    
                                $html .="</tbody>";
                                $html .="<tfoot>";
                                    $html .="<tr>";
                                    $html .="<td colspan='2'></td>";
                                    $html .="<td>".$key."</td>";
                                        $html .="<td colspan='4'></td>";
                                        $html .="<td><b>Total :</b></td>";
                                        $html .="<td>".$get_cutomer_data->total_amount."</td>";
                                        $html .="<td>".$get_cutomer_data->total_amount."</td>";
                                        $html .="<td>".$get_cutomer_data->total_amount."</td>";
                                        $html .="<td>".$get_cutomer_data->total_amount."</td>";
                                    $html .="</tr>";
                                $html .="</tfoot>";
                            $html .="</table>";
                            $html .="</div>";
                        $html .="</div>";

                        $html .="<div class='row'>";
                        $html .="<div class='col-md-8'>";
                        $html .="<span class='float-start'>Amount of Tax Subject to Recvers Change :</span><br>";
                           
                        $html .="</div>";
                        $html .="<div class='col-md-2'>";

                                $html .="<span class='float-end'>GROSS AMOUNT:</span><br>";
                                $html .="<span class='float-end'>LESS DISCOUNT:</span><br>";
                                $html .="<span class='float-end'>ADD CGST :</span> <br>";
                                $html .="<span class='float-end'>ADD SGST : </span><br>";
                                $html .="<span class='float-end'>OTHER ADJ :</span> <br>";
                                $html .="<span class='float-end'>R/OFF AMT :</span> <br>";
                                $html .="<span class='float-end'>G.TOTAL : </span><br>";

                        $html .="</div>";
                        $html .="<div class='col-md-2'>";

                            $html .="<small class='text-center'>".$get_cutomer_data->total_amount."</small><br>";
                            $html .="<small class='text-center'>".$get_cutomer_data->total_amount."</small><br>";
                            $html .="<small class='text-center'>".$get_cutomer_data->total_amount."</small><br>";
                            $html .="<small class='text-center'>".$get_cutomer_data->total_amount."</small><br>";
                            $html .="<small class='text-center'>".$get_cutomer_data->total_amount."</small><br>";
                            $html .="<small class='text-center'>".$get_cutomer_data->total_amount."</small><br>";
                            $html .="<small class='text-center'>".$get_cutomer_data->total_amount."</small><br>";

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
                    $html .="<button type='button' class='btn btn-secondary btn-sm' data-bs-dismiss='modal'>Close</button>";
                    $html .="<button type='button' id='printBtn' class='btn btn-primary btn-sm' order-id='".$get_cutomer_data->id."'>Print</button>";
                $html .="</div>";

            $html .="</div>";
        $html .="</div>";

        return response()->json([
            'status'=>200,
            'html'=>$html
        ]);
  }   

}


