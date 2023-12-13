<?php
class PrinterFixer {
    private $last_line_length = -1;
    private $delimiter_left;
    private $delimiter_right;
    private $last_printtime;

    public function __construct($delimiter_left = "[", $delimiter_right = "]") {
        $this->delimiter_left = $delimiter_left;
        $this->delimiter_right = $delimiter_right;

        $this->last_printtime = microtime(true) - 3600; //1 hour
    }

    public function __destruct() {
        $this->defill();
    }

    private function move_cursor() {
        echo "\033[{$this->last_line_length}D";
    }

    public function printt($str, $time_interval = null) {
        if(! is_null($time_interval)) {
            if(microtime(true) - $this->last_printtime < $time_interval) return;
            $this->last_printtime = microtime(true);
        }

        $this->defill();

        $str = $this->delimiter_left . $str . $this->delimiter_right;
        echo $str; //don't forget about strlen($str). You should not collapse 22 with 24 lines
        $this->last_line_length = strlen($str);
    }

    public function defill() {
        $this->move_cursor();
        echo str_pad("", $this->last_line_length);

        $this->move_cursor();
    }
}

/*

echo "your hello: ";

$printerFixer = new PrinterFixer();
$printerFixer->printt("salut");

sleep(1);
$printerFixer->printt("hello");

sleep(1);
$printerFixer->printt("shalom");

sleep(1);
$printerFixer->printt("good morning");

sleep(1);
$printerFixer->printt("privet");

sleep(1);
$printerFixer->printt("hi");
echo PHP_EOL;
*/

/*
$localPrinter = new PrinterFixer();

$microtime1 = microtime(true);
for($i = 0; $i < 1000000000000000; $i++) {
    $localPrinter->printt($i, 0.125);
}
 */

?>
