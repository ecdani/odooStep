<?php

class SecondTest extends PHPUnit_Framework_TestCase {
    
    public function testAsort() {
    $vegetablesArray = array('carrot' => 1, 'broccoli' => 2.99,
    'garlic' => 3.98, 'swede' => 1.75);
    $sortedArray = array('carrot' => 1, 'swede' => 1.75,
    'broccoli' => 2.99, 'garlic' => 3.98);
    asort($vegetablesArray, SORT_NUMERIC);
    $this->assertSame($sortedArray, $vegetablesArray);
    }

    public function testKsort() {
    $fruitsArray = array('oranges' => 1.75, 'apples' => 2.05,
    'bananas' => 0.68, 'pear' => 2.75);
    $sortedArray = array('apples' => 2.05, 'bananas' => 0.68,
    'oranges' => 1.75, 'pear' => 2.75);
    ksort($fruitsArray, SORT_STRING);
    $this->assertSame($sortedArray, $fruitsArray);
    }
}