
//common function
function getSubCategoryByCategory(category_id) {
    $.ajax({
        type: "get",
        url: "get-sub-category-by-category/"+category_id,
        dataType: "json",
        success: function (response) {
            $('#sub_category_id').html("");
            if (response.status == 200) {
                $('#sub_category_id').append(response.html); 
                $("#sub_category_id").trigger("chosen:updated");  
            }
        }
    });
}

function getStateByCountry(country_id) {
    $.ajax({
        type: "get",
        url: "get-state-by-country/"+country_id,
        dataType: "json",
        success: function (response) {
            $('#state_id').html("");
            $('#city_id').html("");
            if (response.status == 200) {
                $('#state_id').append(response.html);  
                $("#state_id").trigger("chosen:updated"); 
            }
        }
    });
}

function getCityByState(state_id) {
    $.ajax({
        type: "get",
        url: "get-city-by-state/"+state_id,
        dataType: "json",
        success: function (response) {
            $('#city_id').html("");
            if (response.status == 200) {
                $('#city_id').append(response.html); 
                $("#city_id").trigger("chosen:updated");  
                
            }
        }
    });
}

function generateInvoice(bill_id) {
    $.ajax({
       type: "get",
       url: "generate-invoice/"+bill_id,
       dataType: "json",
       success: function (response) {
           if (response.status == 200) {
               $('#generateInvoiceModal').html(response.html);
               $('#generateInvoiceModal').modal('show');
               // window.location.reload();
           }
       }
   });
}

function saveColor() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($("#colorForm")[0]);
    $.ajax({
        type: "post",
        url: "save-color",
        data: formData,
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            console.log(response);
            if (response.status === 400) {
                $('#color_err').html('');
                $('#color_err').addClass('alert alert-danger');
                var count = 1;
                $.each(response.errors, function (key, err_value) {
                    $('#color_err').append('<span>' + count++ + '. ' + err_value + '</span></br>');
                });

            } else {
                $('#color_err').html('');
                $('#colorModal').modal('hide');
                // window.location.reload();
                $('#color').html('');
                $('#color').append(response.color_html); 
                $("#color").trigger("chosen:updated");  
            }
        }
    });
}

function manageCity(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($("#cityForm")[0]);
    $.ajax({
        type: "post",
        url: "manage-city",
        data: formData,
        dataType: "json",
        cache: false,
        contentType: false, 
        processData: false, 
        success: function (response) {
            //console.log(response);
            if(response.status === 400)
            {
                $('#city_err').html('');
                $('#city_err').addClass('alert alert-danger');
                var count = 1;
                $.each(response.errors, function (key, err_value) { 
                    $('#city_err').append('<span>' + count++ +'. '+ err_value+'</span></br>');
                });
                

            }else{
                $('#city_err').html('');
                $('#cityModal').modal('hide');
                var state_id = $("#put_country_id").val();

                // alert(state_id);
                getCityByState(state_id);
                // window.location.reload();
            }
        }
    });
}

function convertNumberToWords(fees_amount) {
        
    var ones = ["", "One ", "Two ", "Three ", "Four ", "Five ", "Six ", "Seven ", "Eight ", "Nine ", "Ten ", "Eleven ", "Twelve ", "Thirteen ", "Fourteen ", "Fifteen ", "Sixteen ", "Seventeen ", "Eighteen ", "Nineteen "];
    var tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
    if ((fees_amount = fees_amount.toString()).length > 9) return "Overflow: Maximum 9 digits supported";
    n = ("000000000" + fees_amount).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
    if (!n) return;
    var str = "";
    str += n[1] != 0 ? (ones[Number(n[1])] || tens[n[1][0]] + " " + ones[n[1][1]]) + "Crore " : "";
    str += n[2] != 0 ? (ones[Number(n[2])] || tens[n[2][0]] + " " + ones[n[2][1]]) + "Lakh " : "";
    str += n[3] != 0 ? (ones[Number(n[3])] || tens[n[3][0]] + " " + ones[n[3][1]]) + "Thousand " : "";
    str += n[4] != 0 ? (ones[Number(n[4])] || tens[n[4][0]] + " " + ones[n[4][1]]) + "Hundred " : "";
    str += n[5] != 0 ? (str != "" ? "and " : "") + (ones[Number(n[5])] || tens[n[5][0]] + " " + ones[n[5][1]]) : " ";
    
    return str+"Rupees Only";
}

function printBarcode(params) {
    
    // alert(params)
    var backup = document.body.innerHTML;
    var div_content = document.getElementById(params).innerHTML;
    document.body.innerHTML = div_content;
    window.print();
    document.body.innerHTML = backup;
    window.location.reload();
}

function printInvoice(params) {
    // alert(params);
    var backup = document.body.innerHTML;
    var div_content = document.getElementById(params).innerHTML;
    document.body.innerHTML = div_content;
    window.print();
    document.body.innerHTML = backup;
    window.location.reload();
}
