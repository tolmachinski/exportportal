<?php
class CartesianProduct implements Iterator{
    private $position = null;
    private $sizes = array();
    private $arrays = null;
    private $cursor = null;

    private function increment_position() {
        $inner_cursor = ++ $this->position[$this->cursor];
        if($this->valid()) return;

        while($this->position[$this->cursor] + 1 >= $this->sizes[$this->cursor]) {
            ++$this->cursor;
            if($this->cursor >= count($this->sizes)) return;
        }

        ++$this->position[$this->cursor];
        for($i = 0; $i < $this->cursor; $i++) {
            $this->position[$i] = 0;
        }
        $this->cursor = 0;
    }

    private function reset_cursor() {
        $this->cursor = 0;
    }

    public function __construct($arrays) {
        $this->arrays = $arrays;
        foreach($arrays as $array) $this->sizes[] = count($array);
        $this->rewind();
    }

    public function rewind() {
        $this->position = array();
        foreach($this->sizes as $size) $this->position[] = 0;
        $this->cursor = 0;
    }

    public function current() {
        $el = array();
        foreach($this->position as $out_key => $in_key) {
            $el[$out_key] = $this->arrays[$out_key][$in_key];
        }

        return $el;
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->increment_position();
        return $this->position;
    }

    public function valid() {
        if(empty($this->sizes)) return false;

        foreach($this->position as $index => $inner_index) {
            if($inner_index >= $this->sizes[$index]) return false;
        }

        return true;
    }
}

/*
$arrs = array(
    array("a", "b"),
    array("-", "*", "&"),
    array("1", "2"),
    array("s", "a", "c", "u", "r", "a")

);

$cartesianProduct = new CartesianProduct($arrs);

foreach($cartesianProduct as $key => $val) {
    echo implode($key). " - " . implode($val) . PHP_EOL;
}
 */
