<?php

require_once('formatAnswer.php'); // Function to format the answer
require_once('conveyerBelt.php'); // Function to move conveyer belt



echo '<h2>Test #1</h2>';
echo 'Steps: 6<br>';
echo 'Expected Outcome: 1 Item Produced';

echo formatAnswer(
    init(
        [
            'items' => ['a', 'b', '0', '0', '0', '0'], // Items can either be a number, or a specified group of items
            'workerCount' => 2,
            'beltLengthCount' => 1,
            'define_blank_space' => '0',
            'settings' => (object) [
                'names' => ['Bob', 'Joe', 'Murph', 'Jason', 'Mike', 'Larry'],
                'collectable_items' => ['a', 'b'], // What the worker wants to pick up
                'items_to_process' => ['a', 'b'], // What the worker needs to process
                'do_not_collect' => ['c'], // What a worker doesnt want to collect
                'processed_item' => 'c', // What gets produced by a worker
                'increment_time' => 4, // How long it takes to process an item
                'define_blank_space' => '0'
            ]
        ]
    )
);


echo '<h2>Test #2</h2>';
echo 'Steps: 9<br>';
echo 'Expected Outcome: 2 Items Produced';

echo formatAnswer(
    init(
        [
            'items' => ['a', 'b', 'a', 'b', '0', '0', '0', '0', '0'], // Items can either be a number, or a specified group of items
            'workerCount' => 2,
            'beltLengthCount' => 1,
            'settings' => (object) [
                'names' => ['Bob', 'Joe', 'Murph', 'Jason', 'Mike', 'Larry'],
                'collectable_items' => ['a', 'b'], // What the worker wants to pick up
                'items_to_process' => ['a', 'b'], // What the worker needs to process
                'do_not_collect' => ['c'], // What a worker doesnt want to collect
                'processed_item' => 'c', // What gets produced by a worker
                'define_blank_space' => '0',
                'increment_time' => 4 // How long it takes to process an item
            ]
        ]
    )
);


echo '<h2>Test #3</h2>';

echo formatAnswer(
    init(
        [
            'items' => ['a', 'b', 'a', 'b', 'a', 'a', 'a', '0', '0'], // Items can either be a number, or a specified group of items
            'workerCount' => 2,
            'beltLengthCount' => 1,
            'settings' => (object) [
                'names' => ['Bob', 'Joe', 'Murph', 'Jason', 'Mike', 'Larry'],
                'collectable_items' => ['a', 'b'], // What the worker wants to pick up
                'items_to_process' => ['a', 'b'], // What the worker needs to process
                'do_not_collect' => ['c'], // What a worker doesnt want to collect
                'define_blank_space' => '0',
                'processed_item' => 'c', // What gets produced by a worker
                'increment_time' => 4 // How long it takes to process an item
            ]
        ]
    )
);
