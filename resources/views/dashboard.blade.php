@extends('layouts.app')
@section('page_title', 'Dashboard')


@section('style')
  <style>
      .small-box>.small-box-footer{
        background-color: #adb5bd
      }
  </style>

@endsection

@section('content')

<div class="row ">

  {{-- <h3>Mac Address {{$macAddr}}</h3> --}}

  @php
      // $string=exec('getmac');
      // $mac=substr($string, 0, 17);
      // echo $mac; 

      // $MAC = exec('getmac');
      // $MAC = strtok($MAC, ' ');
      // echo " MAC address of Server is: $MAC";
      
      // $ipAddress=$_SERVER['REMOTE_ADDR'];
      // $macAddr=false;

      // #run the external command, break output into lines
      // $arp=`arp -a $ipAddress`;
      // $lines=explode("\n", $arp);

      // #look for the output line describing our IP address
      // foreach($lines as $line)
      // {
      //   $cols=preg_split('/\s+/', trim($line));
      //   if ($cols[0]==$ipAddress)
      //   {
      //       $macAddr=$cols[1];
      //   }
      // }

      // echo " dsadjajs - ". $macAddr;

      // $ipAddress=$_SERVER['REMOTE_ADDR']; 

      // echo "sssssssssssssssssss". $mac = shell_exec('arp -a ');
      
      // $ipAddress=$_SERVER['REMOTE_ADDR'];
      // $macAddr=false;

      // $arp=`arp -a $ipAddress`;
      // $lines=explode("\n", $arp);

      // foreach($lines as $line)
      // {
      //   $cols=preg_split('/\s+/', trim($line));
      //   if ($cols[0]==$ipAddress)
      //   {
      //       $macAddr=$cols[1];
      //   }
      // }

      // echo "mac---------------". $macAddr;

      // $handle = fopen("E:\\demo\\resource.txt", "r");
      // print($handle);

      

  @endphp

    {{-- <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h4>Congratulations John!</h4>
                <p class="card-text">Best seller of the month.</p>
                <a href="#" class="btn btn-info">Go somewhere</a>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">
                <h4 >Statistics</h4>
                <p class="card-text">Total 48.5% growth this month</p>

                <div class="row">
                    <div class="col-md-3">Sales</div>
                    <div class="col-md-3">Customers</div>
                    <div class="col-md-3">Products</div>
                    <div class="col-md-3">Revenue</div>
                </div>
                <a href="#" class="btn btn-info">Go somewhere</a>
            </div>
        </div>
    </div> --}}

    {{-- <div class="col-lg-3 col-6">
      <div class="small-box ">
        <div class="inner">
          <h3>250496</h3> 
          <p>Sales</p> 
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#ghfsds" class="small-box-footer bg-gray color-palette ">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box ">
        <div class="inner">
          <h3>678</h3>
          <p>Stock</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer bg-gray color-palette">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box ">
        <div class="inner">
          <h3>1600</h3> 
          <p>Customers</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer bg-gray color-palette">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>

    <div class="col-lg-3 col-6">
      <div class="small-box ">
        <div class="inner">
          <h3>15</h3> 
          <p>Employee</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer bg-gray color-palette">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div> --}}

</div>


  {{-- <div class="row">
    <div class="col-md-8">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title">Today Sales</h4>

                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body table-responsive p-0" style="height: 350px;">
                <table class="table table-head-fixed text-nowrap">
                  <thead>
                    <tr>
                      <th>SN</th>
                      <th>Product</th>
                      <th>Sales By</th>
                      <th>Qty</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($sales as $key => $list)
                      <tr>
                        <td>{{++$key}}</td>
                        <td>{{ucwords($list->product)}}</td>
                        <td>S1</td>
                        <td><span class="badge bg-success">{{$list->qty}}</span></td>
                        <td>19-11-2022</td>
                      </tr>
                    @endforeach
                    
                  </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="col-md-4">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title">Stock</h4>

                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body table-responsive p-0" style="height: 350px;">
                <table class="table table-head-fixed text-nowrap">
                  <thead>
                    <tr>
                      <th>SN</th>
                      <th>Category</th>
                      <th>Qty</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($sales as $key => $list)
                      <tr>
                        <td>{{++$key}}</td>
                        <td>{{ucwords($list->product)}}</td>
                        <td><span class="badge bg-success">{{$list->qty}}</span></td>
                      </tr>
                    @endforeach
                    
                  </tbody>
                </table>
            </div>

        </div>
    </div>


  </div> --}}

  

  <div class="row">
    <section class="col-lg-7 connectedSortable">
      {{-- <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Sales
          </h3>
          <div class="card-tools">
            <ul class="nav nav-pills ml-auto">
              <li class="nav-item">
                <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
              </li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <div class="tab-content p-0">
            <div class="chart tab-pane active" id="revenue-chart"
                 style="position: relative; height: 300px;">
                <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
             </div>
            <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
              <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
            </div>
          </div>
        </div><!-- /.card-body -->
      </div> --}}
      <!-- /.card -->

      <!-- DIRECT CHAT -->
      {{-- <div class="card direct-chat direct-chat-primary">
        <div class="card-header">
          <h3 class="card-title">Direct Chat</h3>

          <div class="card-tools">
            <span title="3 New Messages" class="badge badge-primary">3</span>
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" title="Contacts" data-widget="chat-pane-toggle">
              <i class="fas fa-comments"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="direct-chat-messages">
            <div class="direct-chat-msg">
              <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-left">Alexander Pierce</span>
                <span class="direct-chat-timestamp float-right">23 Jan 2:00 pm</span>
              </div>
              <img class="direct-chat-img" src="dist/img/user1-128x128.jpg" alt="message user image">
              <div class="direct-chat-text">
                Is this template really for free? That's unbelievable!
              </div>
            </div>

            <div class="direct-chat-msg right">
              <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-right">Sarah Bullock</span>
                <span class="direct-chat-timestamp float-left">23 Jan 2:05 pm</span>
              </div>
              <img class="direct-chat-img" src="dist/img/user3-128x128.jpg" alt="message user image">
              <div class="direct-chat-text">
                You better believe it!
              </div>
            </div>

            <div class="direct-chat-msg">
              <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-left">Alexander Pierce</span>
                <span class="direct-chat-timestamp float-right">23 Jan 5:37 pm</span>
              </div>
              <img class="direct-chat-img" src="dist/img/user1-128x128.jpg" alt="message user image">
              <div class="direct-chat-text">
                Working with AdminLTE on a great new app! Wanna join?
              </div>
            </div>

            <div class="direct-chat-msg right">
              <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-right">Sarah Bullock</span>
                <span class="direct-chat-timestamp float-left">23 Jan 6:10 pm</span>
              </div>
              <img class="direct-chat-img" src="dist/img/user3-128x128.jpg" alt="message user image">
              <div class="direct-chat-text">
                I would love to.
              </div>
            </div>

          </div>

          <div class="direct-chat-contacts">
            <ul class="contacts-list">
              <li>
                <a href="#">
                  <img class="contacts-list-img" src="dist/img/user1-128x128.jpg" alt="User Avatar">

                  <div class="contacts-list-info">
                    <span class="contacts-list-name">
                      Count Dracula
                      <small class="contacts-list-date float-right">2/28/2015</small>
                    </span>
                    <span class="contacts-list-msg">How have you been? I was...</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#">
                  <img class="contacts-list-img" src="dist/img/user7-128x128.jpg" alt="User Avatar">

                  <div class="contacts-list-info">
                    <span class="contacts-list-name">
                      Sarah Doe
                      <small class="contacts-list-date float-right">2/23/2015</small>
                    </span>
                    <span class="contacts-list-msg">I will be waiting for...</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#">
                  <img class="contacts-list-img" src="dist/img/user3-128x128.jpg" alt="User Avatar">

                  <div class="contacts-list-info">
                    <span class="contacts-list-name">
                      Nadia Jolie
                      <small class="contacts-list-date float-right">2/20/2015</small>
                    </span>
                    <span class="contacts-list-msg">I'll call you back at...</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#">
                  <img class="contacts-list-img" src="dist/img/user5-128x128.jpg" alt="User Avatar">

                  <div class="contacts-list-info">
                    <span class="contacts-list-name">
                      Nora S. Vans
                      <small class="contacts-list-date float-right">2/10/2015</small>
                    </span>
                    <span class="contacts-list-msg">Where is your new...</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#">
                  <img class="contacts-list-img" src="dist/img/user6-128x128.jpg" alt="User Avatar">

                  <div class="contacts-list-info">
                    <span class="contacts-list-name">
                      John K.
                      <small class="contacts-list-date float-right">1/27/2015</small>
                    </span>
                    <span class="contacts-list-msg">Can I take a look at...</span>
                  </div>
                </a>
              </li>
              <li>
                <a href="#">
                  <img class="contacts-list-img" src="dist/img/user8-128x128.jpg" alt="User Avatar">

                  <div class="contacts-list-info">
                    <span class="contacts-list-name">
                      Kenneth M.
                      <small class="contacts-list-date float-right">1/4/2015</small>
                    </span>
                    <span class="contacts-list-msg">Never mind I found...</span>
                  </div>
                </a>
              </li>
            </ul>
          </div>
        </div>
        <div class="card-footer">
          <form action="#" method="post">
            <div class="input-group">
              <input type="text" name="message" placeholder="Type Message ..." class="form-control">
              <span class="input-group-append">
                <button type="button" class="btn btn-primary">Send</button>
              </span>
            </div>
          </form>
        </div>
      </div> --}}

      <!-- TO DO List -->
      {{-- <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="ion ion-clipboard mr-1"></i>
            To Do List
          </h3>

          <div class="card-tools">
            <ul class="pagination pagination-sm">
              <li class="page-item"><a href="#" class="page-link">&laquo;</a></li>
              <li class="page-item"><a href="#" class="page-link">1</a></li>
              <li class="page-item"><a href="#" class="page-link">2</a></li>
              <li class="page-item"><a href="#" class="page-link">3</a></li>
              <li class="page-item"><a href="#" class="page-link">&raquo;</a></li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <ul class="todo-list" data-widget="todo-list">
            <li>
              <span class="handle">
                <i class="fas fa-ellipsis-v"></i>
                <i class="fas fa-ellipsis-v"></i>
              </span>
              <div  class="icheck-primary d-inline ml-2">
                <input type="checkbox" value="" name="todo1" id="todoCheck1">
                <label for="todoCheck1"></label>
              </div>
              <span class="text">Design a nice theme</span>
              <small class="badge badge-danger"><i class="far fa-clock"></i> 2 mins</small>
              <div class="tools">
                <i class="fas fa-edit"></i>
                <i class="fas fa-trash-o"></i>
              </div>
            </li>
            <li>
              <span class="handle">
                <i class="fas fa-ellipsis-v"></i>
                <i class="fas fa-ellipsis-v"></i>
              </span>
              <div  class="icheck-primary d-inline ml-2">
                <input type="checkbox" value="" name="todo2" id="todoCheck2" checked>
                <label for="todoCheck2"></label>
              </div>
              <span class="text">Make the theme responsive</span>
              <small class="badge badge-info"><i class="far fa-clock"></i> 4 hours</small>
              <div class="tools">
                <i class="fas fa-edit"></i>
                <i class="fas fa-trash-o"></i>
              </div>
            </li>
            <li>
              <span class="handle">
                <i class="fas fa-ellipsis-v"></i>
                <i class="fas fa-ellipsis-v"></i>
              </span>
              <div  class="icheck-primary d-inline ml-2">
                <input type="checkbox" value="" name="todo3" id="todoCheck3">
                <label for="todoCheck3"></label>
              </div>
              <span class="text">Let theme shine like a star</span>
              <small class="badge badge-warning"><i class="far fa-clock"></i> 1 day</small>
              <div class="tools">
                <i class="fas fa-edit"></i>
                <i class="fas fa-trash-o"></i>
              </div>
            </li>
            <li>
              <span class="handle">
                <i class="fas fa-ellipsis-v"></i>
                <i class="fas fa-ellipsis-v"></i>
              </span>
              <div  class="icheck-primary d-inline ml-2">
                <input type="checkbox" value="" name="todo4" id="todoCheck4">
                <label for="todoCheck4"></label>
              </div>
              <span class="text">Let theme shine like a star</span>
              <small class="badge badge-success"><i class="far fa-clock"></i> 3 days</small>
              <div class="tools">
                <i class="fas fa-edit"></i>
                <i class="fas fa-trash-o"></i>
              </div>
            </li>
            <li>
              <span class="handle">
                <i class="fas fa-ellipsis-v"></i>
                <i class="fas fa-ellipsis-v"></i>
              </span>
              <div  class="icheck-primary d-inline ml-2">
                <input type="checkbox" value="" name="todo5" id="todoCheck5">
                <label for="todoCheck5"></label>
              </div>
              <span class="text">Check your messages and notifications</span>
              <small class="badge badge-primary"><i class="far fa-clock"></i> 1 week</small>
              <div class="tools">
                <i class="fas fa-edit"></i>
                <i class="fas fa-trash-o"></i>
              </div>
            </li>
            <li>
              <span class="handle">
                <i class="fas fa-ellipsis-v"></i>
                <i class="fas fa-ellipsis-v"></i>
              </span>
              <div  class="icheck-primary d-inline ml-2">
                <input type="checkbox" value="" name="todo6" id="todoCheck6">
                <label for="todoCheck6"></label>
              </div>
              <span class="text">Let theme shine like a star</span>
              <small class="badge badge-secondary"><i class="far fa-clock"></i> 1 month</small>
              <div class="tools">
                <i class="fas fa-edit"></i>
                <i class="fas fa-trash-o"></i>
              </div>
            </li>
          </ul>
        </div>
        <div class="card-footer clearfix">
          <button type="button" class="btn btn-primary float-right"><i class="fas fa-plus"></i> Add item</button>
        </div>
      </div> --}}

    </section>
    <section class="col-lg-5 connectedSortable">

      {{-- <div class="card bg-gradient-primary">
        <div class="card-header border-0">
          <h3 class="card-title">
            <i class="fas fa-map-marker-alt mr-1"></i>
            Visitors
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm daterange" title="Date range">
              <i class="far fa-calendar-alt"></i>
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div id="world-map" style="height: 250px; width: 100%;"></div>
        </div>
        <div class="card-footer bg-transparent">
          <div class="row">
            <div class="col-4 text-center">
              <div id="sparkline-1"></div>
              <div class="text-white">Visitors</div>
            </div>
            <div class="col-4 text-center">
              <div id="sparkline-2"></div>
              <div class="text-white">Online</div>
            </div>
            <div class="col-4 text-center">
              <div id="sparkline-3"></div>
              <div class="text-white">Sales</div>
            </div>
          </div>
        </div>
      </div> --}}

      {{-- <div class="card bg-gradient-info">
        <div class="card-header border-0">
          <h3 class="card-title">
            <i class="fas fa-th mr-1"></i>
            Sales Graph
          </h3>

          <div class="card-tools">
            <button type="button" class="btn bg-info btn-sm" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn bg-info btn-sm" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <canvas class="chart" id="line-chart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
        <div class="card-footer bg-transparent">
          <div class="row">
            <div class="col-4 text-center">
              <input type="text" class="knob" data-readonly="true" value="20" data-width="60" data-height="60"
                     data-fgColor="#39CCCC">

              <div class="text-white">Mail-Orders</div>
            </div>
            <div class="col-4 text-center">
              <input type="text" class="knob" data-readonly="true" value="50" data-width="60" data-height="60"
                     data-fgColor="#39CCCC">

              <div class="text-white">Online</div>
            </div>
            <div class="col-4 text-center">
              <input type="text" class="knob" data-readonly="true" value="30" data-width="60" data-height="60"
                     data-fgColor="#39CCCC">

              <div class="text-white">In-Store</div>
            </div>
          </div>
        </div>
      </div> --}}

      {{-- <div class="card bg-gradient-success">
        <div class="card-header border-0">

          <h3 class="card-title">
            <i class="far fa-calendar-alt"></i>
            Calendar
          </h3>
          <div class="card-tools">
            <div class="btn-group">
              <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" data-offset="-52">
                <i class="fas fa-bars"></i>
              </button>
              <div class="dropdown-menu" role="menu">
                <a href="#" class="dropdown-item">Add new event</a>
                <a href="#" class="dropdown-item">Clear events</a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">View calendar</a>
              </div>
            </div>
            <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body pt-0">
          <div id="calendar" style="width: 100%"></div>
        </div>
      </div> --}}

    </section>
  </div>



@endsection


@section('script')
{{-- <script src="{{asset('public/sdpl-assets/user/js/slider.js')}}"></script> --}}
  <script>



// navigator.usb.getDevices()
// .then((devices) => {
//   console.log(`Total devices: ${devices.length}`);
//   devices.forEach((device) => {
//     console.log(`Product name: ${device.productName}, serial number ${device.serialNumber}`);
//   });
// });
    // $(document).ready(function () {
    //   alert("hiii");
    // });

    // var macAddress = "";
    // var ipAddress = "";
    // var computerName = "";
    // var wmi = GetObject("winmgmts:{impersonationLevel=impersonate}");
    // e = new Enumerator(wmi.ExecQuery("SELECT * FROM Win32_NetworkAdapterConfiguration WHERE IPEnabled = True"));
    // for(; !e.atEnd(); e.moveNext()) {
    //     var s = e.item();
    //     macAddress = s.MACAddress;
    //     ipAddress = s.IPAddress(0);
    //     computerName = s.DNSHostName;
    // }
    // console.log(macAddress);
    // console.log(ipAddress);
    // console.log(computerName);
    // document.getElementById("txtMACAdress").value = unescape(macAddress);
    // document.getElementById("txtIPAdress").value = unescape(ipAddress);
    // document.getElementById("txtComputerName").value = unescape(computerName);
</script>

  </script>

@endsection