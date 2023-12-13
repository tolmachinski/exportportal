<?php
/*
 * $set1 = [
 *  [ ['a'], ['b'], ['a', 'b']]
 *  [ ['c'], ['d'], ['c', 'd']]
 * ]
 *
 * $set2 = [
 *  [ ['1'], ['2'], ['1', '2' ],
 *  [ ['3'], ['4'], ['3', '4' ],
 * ]
 *
 * foreach(new CombineNestedArrays($set1, $set2, 2) as $combination) {
 *  var_dump($combination);
 * }
 *  will give
 *  ['a', '1']
 *  ['a', '2']
 *  ['a', '3']
 *  ['a', '4']
 *  ['b', '1']
 *  ['b', '2']
 *  ['b', '3']
 *  ['b', '4']
 *  ['c', '1']
 *  ['c', '2']
 *  ['c', '3']
 *  ['c', '4']
 *  ['d', '1']
 *  ['d', '2']
 *  ['d', '3']
 *  ['d', '4']
 *
 *new CombineNestedArrays($set1, $set2) will give all combinations as arrays
 */
class CombineNestedArrays implements Iterator {
    private $set1 = null;
    private $set2 = null;
    private $max_size = null;

    private $set_cursor_1 = 0;
    private $set_cursor_inner_1 = 0;

    private $set_cursor_2 = 0;
    private $set_cursor_inner_2 = 0;

    public function __construct($set1, $set2, $max_size = null) {
        $this->set1 = $set1;
        $this->set2 = $set2;
        $this->max_size = $max_size;

        $this->rewind();
    }

    public function rewind() {
        $this->set_cursor_1 = 0;
        $this->set_cursor_inner_1 = 0;
        $this->set_cursor_2 = 0;
        $this->set_cursor_inner_2 = 0;

        if(!$this->valid_size()) $this->next();
    }

    public function key() {
        return array($this->set_cursor_1, $this->set_cursor_inner_1, $this->set_cursor_2, $this->set_cursor_inner_2);
    }

    public function current() {
        return array($this->set1[$this->set_cursor_1][$this->set_cursor_inner_1], $this->set2[$this->set_cursor_2][$this->set_cursor_inner_2]);
    }

    private function _next() {
        ++$this->set_cursor_inner_1;
        if(isset($this->set1[$this->set_cursor_1][$this->set_cursor_inner_1])) return;

        $this->set_cursor_inner_1 = 0;
        ++$this->set_cursor_1;
        if(isset($this->set1[$this->set_cursor_1][$this->set_cursor_inner_1])) return;

        $this->set_cursor_1 = 0;
        ++$this->set_cursor_inner_2;
        if(isset($this->set2[$this->set_cursor_2][$this->set_cursor_inner_2])) return;

        $this->set_cursor_inner_2 = 0;
        ++$this->set_cursor_2;
        if(isset($this->set2[$this->set_cursor_2][$this->set_cursor_inner_2])) return;
    }

    public function next() {
        for(;;) {
            $this->_next();
            if(!$this->valid_index() || $this->valid_size())  return;
        }
    }

    private function valid_index() {
        return isset($this->set1[$this->set_cursor_1][$this->set_cursor_inner_1]) && isset($this->set2[$this->set_cursor_2][$this->set_cursor_inner_2]);
    }

    private function valid_size() {
        if(empty($this->max_size)) return true;

        return count($this->set1[$this->set_cursor_1][$this->set_cursor_inner_1]) + count($this->set2[$this->set_cursor_2][$this->set_cursor_inner_2]) <= $this->max_size;
    }

    public function valid() {
        return $this->valid_index() && $this->valid_size();
    }
}

/*
$set1 = [
   [ ['a'], ['b'], ['a', 'b']],
   [ ['c'], ['d'], ['c', 'd']]
];

$set2 = [
 [ ['1'], ['2'], ['1', '2' ]],
 [ ['3'], ['4'], ['3', '4' ]],
];


$combineNestedArrays = new CombineNestedArrays($set1, $set2, 2);
foreach($combineNestedArrays as $combine) {
    echo implode(",", $combine).PHP_EOL;
}
 */
