<?php
$config_dir = '/opt/packetloss/conf.ini';
$default = 1;
$results_ok = array ();
$results_404 = array ();
$times = array ();

function write_into_ini($config , $section, $a , $value)
{
  $command = "python /var/www/html/paketloss/writeini.py " . $config . " " . $section . " " . $a . " " . $value;
  exec($command);
}

if(isset($_POST["ping_server"]))
{
  write_into_ini($config_dir,"script","ping_server",$_POST["ping_server"]);
}

if(isset($_POST["days"]))
{
  $POST = $_POST["days"];
  if(preg_match('/[a-zA-Z]+/', $POST))
  {
    $days = $default;
  }
    if($_POST["days"] == "" or $_POST["days"] == null or $_POST["days"] == false)
  {
    $days = $default;
  }
  else
  {
    $days = $_POST["days"];
    write_into_ini($config_dir,"script","display_days",$_POST["days"]);
  }

}
else{
  $days = $default;
}

//Loading from ini
$ini = parse_ini_file($config_dir);
$scala = $ini['scala'];

?>
<html>
<head>
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.js"></script>
  <script src="https://code.jquery.com/jquery-3.2.1.js"></script>
</head>
<style>
@media screen and (max-width: 1000px){
  .graph{
    width: 90%;
    padding-left: 0;
    padding-right: 0;
    margin-left: auto;
    margin-right: auto;
    display: block;
  }
}
@media screen and (min-width: 1001px){
.graph{
    width: 60%;
    padding-left: 0;
    padding-right: 0;
    margin-left: auto;
    margin-right: auto;
    display: block;
  }

}
</style>
<body style="background: url(https://i.imgur.com/ZOcLM7h.jpg)">
  <div class="w3-container">
    <div class="w3-row w3-card-4 graph" style="background-color:#f4f4f4;">
      <div>
        <canvas id="myChart" style="display: block; height:60%;"></canvas>
      </div>
    </div>
    <div class="w3-row w3-card-4 graph">
      <div class="w3-col w3-container m6 l6" style="height:41%; background-color:#eeeeee;">
        <p style="text-align: center;"><?php echo($days * 24) ?> Hour summary</p>
        <canvas id="pie-chart" style="display: block;">
      </canvas>
      </div>
      <div class="w3-col w3-container m6 l6" style="height:41%; background-color:#eeeeee">
        <p>Config:</p>
        <form method="post" class="w3-container">
          <div class="w3-row">
            <div class="w3-col w3-container m6 l6">
              <label>Ping Server</label>
              <input class="w3-input" name="ping_server" type="text" value="<?php echo($ini['ping_server']) ?>">
            </div>
            <div class="w3-col w3-container m6 l6">
              <label>Show days</label>
              <input class="w3-input" name="days" type="text" value="<?php echo($days) ?>">
            </div>
          </div>
          <button class="w3-button w3-block w3-section w3-blue w3-ripple w3-padding">Save</button>
        </form>
      </div>
    </div>
  </div>
  <?php
        //MYSQL connection
        $conn = mysqli_connect($ini['db_host'],$ini['db_user'],$ini['db_password']);
        if (!$conn) {
            die('Could not connect: ' . mysqli_error($conn));
        }
        mysqli_select_db($conn,"packetloss");
        date_default_timezone_set("Europe/Berlin");


        for ($i = 0; $i <= $days * 1440; $i=$i + $scala)
        {
          array_unshift($times,date('H:i', strtotime("-" . $i . " minutes")));
          $a = $i + $scala;
          //Get all the lost pings

          $sql = "select sum(packet_count) as status from tblPacket where status = 1 and timestamp between DATE_SUB(now(), INTERVAL " . $a . " MINUTE) and DATE_SUB(NOW(),INTERVAL " . $i . " MINUTE);";
          $result = mysqli_query($conn,$sql);
          if (mysqli_num_rows($result) > 0) {
                // output data of each row
                while($row = mysqli_fetch_assoc($result)) {
                    array_unshift($results_404, $row["status"]);

                }
            }
          else {
                echo "0";
          }

          $sql = "select sum(packet_count) as status from tblPacket where status = 0 and timestamp between DATE_SUB(now(), INTERVAL " . $a . " MINUTE) and DATE_SUB(NOW(),INTERVAL " . $i . " MINUTE);";
          $result = mysqli_query($conn,$sql);
          $final_result = 0;
          if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
                array_unshift($results_ok, $row["status"]);


              }
            }

          else {
                echo "0";
            }
        }
        $final_result_ok = array_sum($results_ok);
        $final_result_404 = array_sum($results_404);
          mysqli_close($conn);
      ?>

    <script>
      var ctx = document.getElementById("myChart").getContext('2d');
      var pie_chart = document.getElementById("pie-chart").getContext("2d");

      var myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?php echo json_encode($times);?>,
          datasets: [{
              label: 'Total pings',
              data: <?php echo json_encode($results_ok);?>,
              backgroundColor: 'rgba(140, 212, 168, 0.2)',
              borderColor: 'rgba(140, 212, 168, 1)',
              borderWidth: 2
            },
            {
              label: 'Packets lost',
              data: <?php echo json_encode($results_404);?>,
              backgroundColor:'rgba(244, 57, 57, 0.2)',
              borderColor: 'rgba(244, 57, 57, 1)',
              borderWidth: 2
            }
          ]
        },
        options: {

          title: {
            display: true,
            text: 'Packetloss'
          },
          hover: {
            mode: 'nearest',
            intersect: true
          },
          responsive: true,
          tooltips: {
            mode: 'x-axis',
            intersect: false,
            callbacks: {
              //Percentage of packetloss
              label: function(tooltipItems, data) {
                if (tooltipItems.datasetIndex === 1) {
                  var percentage = 0.0;
                  var ok = parseInt(data.datasets[0].data[tooltipItems.index]);
                  var notok = parseInt(data.datasets[1].data[tooltipItems.index]);
                  percentage = 100.0 * notok / ok;
                  return data.datasets[tooltipItems.datasetIndex].label + ": " + tooltipItems.yLabel + " (" + percentage.toFixed(1) + "%)";
                } else {
                  return data.datasets[tooltipItems.datasetIndex].label + ": " + tooltipItems.yLabel;
                }
              },
              //Determine last hour for the time period

              title: function(tooltipItem, data) {
                            var label = tooltipItem[0].xLabel;
                            var time = label.match(/(\d?\d):?(\d?\d?)/);
                            var h = parseInt(time[1], 10);
                            var m = parseInt(time[2], 10) || 0;
                            var p;
                            var extrah = 0;
                            timeshift = <?php echo($scala) ?> - 1;
                            shiftedtime = (m + timeshift);
                            if(shiftedtime >= 60 )
                            {
                              extrah = 1;
                              p = (m + timeshift) - 60;
                            }
                            else {
                              p = (m + timeshift);
                            }

                            m = "00" + m;
                            p = "00" + p;

                            var from = h+":"+ String(m).substr(-2,2);
                            var to = (h + extrah)+":"+ String(p).substr(-2,2) ;


                            return "from "+from+" - to "+to;
                        },
              labelTextColor: function(tooltipItem, chart) {
                return '#ffffff';
              }
            }
          },
          scales: {
            yAxes: [{
              display: true,
              ticks: {
                beginAtZero: true,
                suggestedMax: 1400,
              }
            }],
            xAxes: [{
              display: true,
              autoSkip: false,
            }],
          }
        },
      });

    var myDoughnutChart = new Chart(pie_chart, {
    type: 'doughnut',
    data: {
      labels: ['Total pings:', 'Packets lost:'],
      datasets: [{
          data: [<?php echo($final_result_ok);?>,<?php echo($final_result_404);?>],
          backgroundColor: ['rgba(140, 212, 168, 1)','rgba(244, 57, 57, 1)'],
          borderWidth: 0
        },
      ]
    },

    });




    </script>

</body>

</html>
