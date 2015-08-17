@extends('app')


<?php

$stocks = array("AAPL", "GOOGL", "XOM", "MSFT", "JNJ", "WMT", "GE", "JPM", "FB", "T");
$stocksrealname = array("Apple Inc", "Google Inc", "Exxon Mobil Corp.", "Microsoft Corporation", "Johnson & Johnson", "Wal-Mart Stores, Inc.", "General Electric Co", "JPMorgan Chase & Co", "Facebook, Inc. ", "AT&T Inc");
$date = strtotime(date('Y-m-d') . ' -1 month');
$date2 = strtotime(date('Y-m-d') . ' -24 months');

$a = (date('m', $date));
$b = (date('d', $date));
$c =(date('Y', $date));
$d = (date('m', $date2));
$e = (date('d', $date2));
$f =(date('Y', $date2));

$rsi = array();
foreach($stocks as $stock) {
  $s = str_getcsv(file_get_contents("http://ichart.yahoo.com/table.csv?s=$stock&a=$d&b=$e&c=$f&d=$a&e=$b&f=$c&g=$d"));

  $stockcloses = array();
  for ($x = 1600; $x > 9; $x -= 6) {
      //echo $s[$x];
      $stockcloses[] = $s[$x];
  }

  $amount = count($stockcloses);
  $stockdiff = array();
  for($x = 1; $x < $amount; $x++) {
      $stockdiff[] = $stockcloses[$x] - $stockcloses[$x - 1];
  }

  $avggain = array();
  $avgloss = array();
  $totalgain = 0;
  $totalloss = 0;
  for($x = 0; $x < 14; $x++) {
      if ($stockdiff[$x] > 0) {
          $totalgain += $stockdiff[$x];
      }
      else {
          $totalloss += $stockdiff[$x];
      }
  }

  $avggain[] = $totalgain / 14;
  $avgloss[] = $totalloss / 14;

  for($x = 15; $x < $amount - 1; $x++) {
      if ($stockdiff[$x] > 0) {
          $avggain[] = (13 * $avggain[$x - 15] + $stockdiff[$x]) / 14;
          $avgloss[] =  (13 * $avgloss[$x - 15]) / 14;
      }
      else {
          $avgloss[] = (13 * $avgloss[$x - 15] + $stockdiff[$x]) / 14;
          $avggain[] = (13 * $avggain[$x - 15]) / 14;
      }
  }

  $rs = end($avggain) / (-1 * end($avgloss));
  $rsi[] = 100 - 100 / (1 + $rs);

}

$min = 101;
$max = -1;
$indexmin = 0;
$indexmax = 0;
for($x = 0; $x < count($rsi); $x++) {

    if ($rsi[$x] < $min) {
        $min = $rsi[$x];
        $indexmin = $x;
    }
    if ($rsi[$x] > $max) {
        $max = $rsi[$x];
        $indexmax = $x;
    }
}

/*
print_r($rsi[$indexmax]);
print_r($stocks[$indexmax]);

echo '<pre>';
for ($x = 0; $x < count($rsi); $x++) {
    echo  $stocks[$x] . ": " . $rsi[$x];
    echo '<br>';
}
echo '</pre>';
*/
?>





@section('content')


<!-- Navigation -->
    <nav class="navbar navbar-default navbar-fixed-top topnav" role="navigation">
        <div class="container topnav">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand topnav" href="#">Stock of the Day</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="#about">About</a>
                    </li>
                    <li>
                        <a href="#rsi">RSI</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>


    <!-- Header -->
    <a name="about"></a>
    <div class="intro-header">
        <div class="container">

            <div class="row">
                <div class="col-lg-12">
                    <div class="intro-message">
                        <h1>Stock of the Day</h1>
                        <h3>The Highest and Lowest RSI Stocks of the Day</h3>

                    </div>
                </div>
            </div>

        </div>
        <!-- /.container -->

    </div>
    <!-- /.intro-header -->

    <!-- Page Content -->

    <a  name="rsi"></a>
    <div class="content-section-a">

        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-sm-6">
                    <hr class="section-heading-spacer">
                    <div class="clearfix"></div>
                    <h2 class="section-heading">Highest RSI:
                    <?php echo $stocksrealname[$indexmax]; ?>
                    </h2>
                    <h2>
                        RSI: <?php echo round($rsi[$indexmax],2); ?>
                    </h2>
                </div>
                <div class="col-lg-5 col-lg-offset-2 col-sm-6">
                        <!-- TradingView Widget BEGIN -->
                        <script type="text/javascript" src="https://d33t3vvu2t2yu5.cloudfront.net/tv.js"></script>
                        <script type="text/javascript">
                        var sym = <?php echo json_encode($stocks[$indexmax]) ?>;
                        new TradingView.widget({
                          "width": 600,
                          "height": 400,
                          "symbol": "NASDAQ:" + sym,
                          "interval": "D",
                          "timezone": "Etc/UTC",
                          "theme": "White",
                          "style": "1",
                          "toolbar_bg": "#f1f3f6",
                          "allow_symbol_change": true,
                          "hideideas": true
                        });
                        </script>
                        <!-- TradingView Widget END -->
                </div>
            </div>

        </div>
        <!-- /.container -->

        <br>

        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-sm-6">
                    <hr class="section-heading-spacer">
                    <div class="clearfix"></div>
                    <h2 class="section-heading">Lowest RSI:
                    <?php echo $stocksrealname[$indexmin]; ?>
                    </h2>
                    <h2>
                        RSI: <?php echo round($rsi[$indexmin],2); ?>
                    </h2>
                </div>
                <div class="col-lg-5 col-lg-offset-2 col-sm-6">
                        <!-- TradingView Widget BEGIN -->
                        <script type="text/javascript" src="https://d33t3vvu2t2yu5.cloudfront.net/tv.js"></script>
                        <script type="text/javascript">
                        var sym = <?php echo json_encode($stocks[$indexmin]) ?>;
                        new TradingView.widget({
                          "width": 600,
                          "height": 400,
                          "symbol": "NASDAQ:" + sym,
                          "interval": "D",
                          "timezone": "Etc/UTC",
                          "theme": "White",
                          "style": "1",
                          "toolbar_bg": "#f1f3f6",
                          "allow_symbol_change": true,
                          "hideideas": true
                        });
                        </script>
                        <!-- TradingView Widget END -->
                </div>
            </div>

        </div>
        <!-- /.container -->


    </div>
    <!-- /.content-section-a -->







@stop
