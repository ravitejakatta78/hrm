 <!-- Page Header-->
          <header class="page-header">
            <div class="container-fluid">
              <h2 class="no-margin-bottom">Dashboard</h2>
            </div>
          </header>
          <!-- Dashboard Counts Section-->
          <section class="dashboard-counts no-padding-bottom">
            <div class="container-fluid">
              <div class="row bg-white has-shadow">
                <!-- Item -->
                <div class="col-xl-3 col-sm-6">
                  <div class="item d-flex align-items-center">
                    <div class="icon bg-violet"><i class="fa fa-plus"></i></div>
                    <div class="title"><span>New<br>Orders</span>
                      <div class="progress">
                        <div role="progressbar" style="width: 25%; height: 4px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" class="progress-bar bg-violet"></div>
                      </div>
                    </div>
                    <div class="number" ><strong class="counter">254</strong></div>
                  </div>
                </div>
                <!-- Item -->
                <div class="col-xl-3 col-sm-6">
                  <div class="item d-flex align-items-center">
                    <div class="icon bg-red"><i class="fa fa-clock-o"></i></div>
                    <div class="title"><span>Pending<br>Orders</span>
                      <div class="progress">
                        <div role="progressbar" style="width: 70%; height: 4px;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" class="progress-bar bg-red"></div>
                      </div>
                    </div>
                    <div class="number"><strong class="counter">70</strong></div>
                  </div>
                </div>

                <!-- Item -->
                <div class="col-xl-3 col-sm-6">
                  <div class="item d-flex align-items-center">
                    <div class="icon bg-green"><i class="fa fa-truck"></i></div>
                    <div class="title"><span>Delivered<br>Orders</span>
                      <div class="progress">
                        <div role="progressbar" style="width: 40%; height: 4px;" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" class="progress-bar bg-green"></div>
                      </div>
                    </div>
                    <div class="number"><strong class="counter">40</strong></div>
                  </div>
                </div>
                <!-- Item -->
                <div class="col-xl-3 col-sm-6">
                  <div class="item d-flex align-items-center">
                    <div class="icon bg-orange"><i class="fa fa-ban"></i></div>
                    <div class="title"><span>Cancelled<br>Orders</span>
                      <div class="progress">
                        <div role="progressbar" style="width: 50%; height: 4px;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" class="progress-bar bg-orange"></div>
                      </div>
                    </div>
                    <div class="number"><strong class="counter">50</strong></div>
                  </div>
                </div>
              </div>
            </div>
          </section>
          <section class="dashboard-header">
            <div class="container-fluid">
              <div class="row">
                <!-- Statistics -->
                <div class="statistics col-lg-3 col-12">
                  <div class="statistic d-flex align-items-center bg-white has-shadow">
                    <div class="icon bg-red"><i class="fa fa-money"></i></div>
                    <div class="text"><strong class="counter">234</strong><br><small>Paid By Cash</small></div>
                  </div>
                  <div class="statistic d-flex align-items-center bg-white has-shadow">
                    <div class="icon bg-green"><i class="fa fa-globe"></i></div>
                    <div class="text"><strong class="counter">152</strong><br><small>Online Payment</small></div>
                  </div>
                  <div class="statistic d-flex align-items-center bg-white has-shadow">
                    <div class="icon bg-orange"><i class="fa fa-times"></i></div>
                    <div class="text"><strong class="counter">147</strong><br><small>Not Paid Orders</small></div>
                  </div>
                  <div class="statistic d-flex align-items-center bg-white has-shadow">
                    <div class="icon bg-violet"><i class="fa fa-check"></i></div>
                    <div class="text"><strong class="counter">147</strong><br><small>Paid Orders</small></div>
                  </div>
                </div>
                <!-- Line Chart            -->
                <div class="chart col-lg-6 col-12">
                  <div id="chart-container"></div>
                </div>
                <div class="chart col-lg-3 col-12">
                  <!-- Bar Chart   -->
                  
                  <!-- Numbers-->
                  <div class="statistic d-flex align-items-center bg-white has-shadow">
                    <div class="icon bg-green"><i class="fa fa-user-secret"></i></div>
                    <div class="text"><strong>Pilot Data</strong></div>
                  </div>
                  <div class="statistic d-flex align-items-center bg-white has-shadow">
                    <div class="icon bg-orange"><i class="fa fa-users"></i></div>
                    <div class="text"><strong>Users Data</strong></div>
                  </div>
                  <div class="statistic d-flex align-items-center bg-white has-shadow">
                    <div class="icon bg-violet"><i class="fa fa-commenting-o"></i></div>
                    <div class="text"><strong>Feedback</strong></div>
                  </div>
                </div>
              </div>
            </div>
          </section>
		  <script src='https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js'></script>
<script src='https://cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js'></script>
<script src='https://unpkg.com/jquery-fusioncharts@1.1.0/dist/fusioncharts.jqueryplugin.js'></script>
			<script src='http://cdnjs.cloudflare.com/ajax/libs/waypoints/2.0.3/waypoints.min.js'></script>
			<script src='https://cdn.jsdelivr.net/jquery.counterup/1.0/jquery.counterup.min.js'></script>	
			<script>
				$(document).ready(function(){
					$('.counter').counterUp({
  delay: 10,
  time: 2000
});

				})
			
			</script>
		  <script>
	$(document).ready(function(){
	
	$('select').select2();
  $("#chart-container").insertFusionCharts({
  type: "spline",
  width: "100%",
  height: "100%",
  dataFormat: "json",
  dataSource: {
    chart: {
      caption: "Monthly Sale Report",
      yaxisname: "Average Monthly Sales",
      anchorradius: "5",
      plottooltext: "Average Sale in $label is <b>$dataValue</b>",
      showhovereffect: "1",
      showvalues: "0",
      numbersuffix: "K",
      theme: "fusion",
      anchorbgcolor: "#72D7B2",
      palettecolors: "#72D7B2"
    },
    data: [
      {
        label: "Jan",
        value: "1"
      },
      {
        label: "Feb",
        value: "5"
      },
      {
        label: "Mar",
        value: "10"
      },
      {
        label: "Apr",
        value: "12"
      },
      {
        label: "May",
        value: "14"
      },
      {
        label: "Jun",
        value: "16"
      },
      {
        label: "Jul",
        value: "20"
      },
      {
        label: "Aug",
        value: "22"
      },
      {
        label: "Sep",
        value: "20"
      },
      {
        label: "Oct",
        value: "16"
      },
      {
        label: "Nov",
        value: "7"
      },
      {
        label: "Dec",
        value: "2"
      }
    ]
  }
});
});
	</script>