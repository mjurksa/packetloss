<html>

<head>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.js"></script>
  <script src="https://code.jquery.com/jquery-3.2.1.js"></script>
</head>

<body>
  <div style="width: 70% ; height: 40%;padding-left: 0; padding-right: 0; margin-left: auto; margin-right: auto; display: block;">
    <canvas id="myChart" style="display: block;"></canvas>
  </div>
  <?php
        $scala = 20; //in minuten
        $results_ok = array ();
        $results_404 = array ();
        $times = array ();
        $default = 1; //tage in tagen
        if(isset($_GET["days"]))
        {
          $yolo = $_GET["days"];
          if(preg_match('/[a-zA-Z]+/', $yolo))
          {
            $days = $default;
          }
          if($_GET["days"] == "" or $_GET["days"] == null or $_GET["days"] == false)
          {
            $days = $default;
          }
          else
          {
            $days = $_GET["days"];
          }

        }
        else{
          $days = $default;
        }


        //MYSQL connection
        $conn = mysqli_connect('localhost','php2','PhP13378908991');
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

          mysqli_close($conn);



      ?>

    <script>
      var ctx = document.getElementById("myChart").getContext('2d');

      var myChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?php echo json_encode($times);?>,
          datasets: [{
              label: 'Total Pings',
              data: <?php echo json_encode($results_ok);?>,
              backgroundColor: 'rgba(140, 212, 168, 0.2)',
              borderColor: 'rgba(140, 212, 168, 1)',
              borderWidth: 3
            },
            {
              label: 'Packets lost',
              data: <?php echo json_encode($results_404);?>,
              backgroundColor:'rgba(244, 57, 57, 0.2)',
              borderColor: 'rgba(244, 57, 57, 1)',
              borderWidth: 3
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
                            if (m < 60)
                            {
                              k = 60 - m;
                              p = ("00" + parseInt(60 - k))
                            }
                            else {
                              p = ("00" + parseInt(m - 60))
                            }
                            var k = ("00" + parseInt(m))
                            var from = h+":"+ p.substr(-2,2);
                            var to = h+":"+ k.substr(-2,2);
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
                suggestedMax: 3600
              }
            }],
            xAxes: [{
              display: true,
              autoSkip: false,
            }],
          }
        },
      });
    </script>
</body>

</html>
