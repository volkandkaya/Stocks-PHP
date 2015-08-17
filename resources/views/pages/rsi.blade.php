@extends('app')

@section('content')

<?php

$stocks = array("AAPL", "GOOGL", "XOM", "MSFT", "JNJ", "WMT", "GE", "JPM", "FB", "T");
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

print_r($rsi[$indexmax]);
print_r($stocks[$indexmax]);

echo '<pre>';
for ($x = 0; $x < count($rsi); $x++) {
    echo  $stocks[$x] . ": " . $rsi[$x];
    echo '<br>';
}
echo '</pre>';

?>

<!-- TradingView Widget BEGIN -->
<script type="text/javascript" src="https://d33t3vvu2t2yu5.cloudfront.net/tv.js"></script>
<script type="text/javascript">
var sym = <?php echo json_encode($stocks[$indexmax]) ?>;
new TradingView.widget({
  "autosize": true,
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

@stop
