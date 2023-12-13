<?php
class SetCombination implements Iterator {
    public $sets;
    public $sizes = array();
    public $max_size;
    public $inner_cursors = array();
    public $cursor;
    public $max_cursor;
    public $cursor_count;

    public function __construct($sets, $max_size = null) {
        $this->max_size = $max_size;
        $this->sets = $sets;
        $this->cursor_count = count($sets);
        $this->max_cursor = 1 << $this->cursor_count;

        $this->rewind();
    }

    public function valid() {

        return $this->cursor < $this->max_cursor;
    }

    private function rewind_inner_cursors() {
        for($i = 0; $i < $this->cursor_count; $i++) {
            $this->inner_cursors[$i] = 0;
        }
    }

    public function rewind() {
        for($i = 0; $i < $this->cursor_count; $i++) {
            $this->sizes[$i] = count($this->sets[$i]);
        }
        $this->cursor = 0;

        $this->rewind_inner_cursors();
    }

    public function key() {
        return $this->inner_cursors;
    }

    public function current() {
        $values = array();
        for($i = 0; $i < $this->cursor_count; $i++) {
            if((1 << $i) & $this->cursor) {
                $values[] = $this->sets[$i][$this->inner_cursors[$i]];
            }
        }

        return $values;
    }

    public function next_() {
        for($i = 0; $i < $this->cursor_count; $i++) {
            if(($this->cursor & ( 1 << $i)) == 0) continue;
            if(++$this->inner_cursors[$i] < $this->sizes[$i]) return true;
            $this->inner_cursors[$i] = 0;
        }

        return false;
    }

    public function next() {
        //while(!$this->next_() && ++$this->cursor < $this->max_cursor);
        if($this->next_()) return;
        if(++$this->cursor >= $this->max_cursor) return;
        $this->rewind_inner_cursors();
        if(empty($this->max_size)) return;

        while(1) {
            for($count_ones = 0, $x = $this->cursor;$x; $x >>= 1) $count_ones += $x & 1;
            if($count_ones <= $this->max_size) return;
            if(++$this->cursor >= $this->max_cursor) return;
        }
    }
}

/*
$set = [
    ['a', 'b'],
    ['1', '2'],
    ['-', '*', '+']
];

$setCombination = new SetCombination($set, 3);

foreach($setCombination as $combination) {
    echo implode(" ", $combination).PHP_EOL;
}
 */
