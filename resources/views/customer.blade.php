@extends('layouts.app')
@section('page_title', '    ')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">Customers</h3>
            </div>
            <div class="card-body " >
                <div class="row">
                    <div class="col-md-12 table-responsive p-0" style="height: 550px;">
                        <table class="table table-striped table-head-fixed" id="customer_list">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Customer</th>
                                    <th>Mobile</th>
                                    <th>City</th>
                                    <th>Member</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customers as $key => $list)
                                @php
                                    $member = getMemberShip($list->id)
                                @endphp
                                    <tr >
                                        <td>{{++$key}}</td>
                                        <td>{{ucwords($list->customer_name)}}</td>
                                        <td>{{$list->mobile_no}}</td>
                                        <td>{{ucwords($list->city)}}</td>
                                        @if($member == MyApp::SILVER)
                                            <td style='color:#454545';><b>{{MyApp::SILVER}}</b></td>
                                        @elseif($member == MyApp::GOLDEN)
                                            <td style='color:#D35400';><b>{{MyApp::GOLDEN}}</b></td>
                                        @else
                                            <td style='color:#5D6D7E';><b>{{MyApp::PLATINUM}}</b></td>
                                        @endif 
                                        <td id="customer_row_id" customer-id="{{$list->id}}"><i class="fas fa-lg fa-file" ></i></td>
                                    </tr>
                                @endforeach
                            </tbody> 
                        </table>    
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customer Detail</h3>
            </div>
            <div class="card-body" style="height:550px;">
                <div class="col-md-12 mt-2">
                    <div id="customer_detail_list"></div>

                    
                </div>
            </div>
        </div>
    </div>
</div>

<section>
    <div id="newcontent">
        <div class="modal fade" id="generateInvoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                
        </div>
    </div>
</section>

@endsection
@section('script')
<script>
    $(document).ready(function(){
        $(document).on('change','#customer_id', function(e){
            e.preventDefault();
            var customer_id = $(this).val();
            getProjects(customer_id);
        });

        
        $(document).on('click','#customer_row_id', function(e){
            e.preventDefault();
            var customer_id = $(this).attr('customer-id');
            // alert(customer_id);
            CustomerDetail(customer_id);
        });
        $(document).on('click','#showGenerateInvoiceModal', function (e) {
                e.preventDefault();
                var bill_id = $(this).attr('bill-id');
                generateInvoice(bill_id);              
            });


        // $(document).on('click','#client_project_row', function(e){
        //     e.preventDefault();
        //     var customer_id = $(this).val();
        //     // alert(customer_id);
        //      getCustomerPoints(customer_id);
        // });


    });

    function getProjects(customer_id) {
        $.ajax({
            type: "get",
            url: `get-customer/${customer_id}`,
            dataType: "json",
            success: function (response) {
               
                if(response.status == 200){
                    $('#customer_list').html("");
                    $('#customer_list').append(response.html);
                }
            }
        });
    } 

    function CustomerDetail(customer_id) {
        $.ajax({
            type: "get",
            url: `customer-detail/${customer_id}`,
            dataType: "json",
            success: function (response) {
               console.log(response);
                if(response.status == 200){
                    $('#customer_detail_list').html("");
                    $('#customer_detail_list').append(response.html);
                }
            }
        });
    } 

    function generateInvoice(customer_id) {
         $.ajax({
        type: "get",
        url: "generate-invoice/"+customer_id,
        dataType: "json",
        success: function (response) {
            //console.log(response);
            if (response.status == 200) {
                $('#generateInvoiceModal').html(response.html);
                $('#generateInvoiceModal').modal('show');
                
            }
        }
    });
    
}


</script>

@endsection