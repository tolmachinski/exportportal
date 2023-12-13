<?php

class Combination implements Iterator {
    private $array;
    private $position;
    private $max_position;
    private $count;
    private $combination_size = null;

    public function __construct($array, $combination_size) {
        $this->combination_size = $combination_size;
        $this->array = $array;
        $this->count = count($this->array);
        $this->max_position = (1 << $this->count) - 1;
        $this->position = 0;
    }

    public function current() {
        $el = array();
        $position = $this->position;
        $position_index = 0;

        while($position) {
            if($position & 0b1) {
                $el[] = $this->array[$position_index];
            }

            $position = $position >> 1;
            ++$position_index;
        }

        return $el;
    }

    public function next() {
        ++$this->position;
        if($this->combination_size == null) return ++$this->position;

        while($this->bitcount($this->position) > $this->combination_size && $this->position <= $this->max_position ) ++$this->position;
    }

    private function bitcount($number) {
        $bit_counts = 0;
        while($number) {
            ($number & 0b1) && ++$bit_counts;
            $number >>= 1;
        }

        return $bit_counts;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function valid() {
        return $this->position <= $this->max_position && $this->bitcount($this->position) <= $this->combination_size;
    }

    public function key() {
        return $this->position;
    }
}
