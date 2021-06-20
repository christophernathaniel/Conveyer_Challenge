<?php

require_once('formatAnswer.php'); // Function to format the answer
require_once('conveyerBelt.php'); // Function to move conveyer belt


echo '<h2>The Conveyer Belt Test Answer</h2>';

echo formatAnswer(
    init(
        [
            'items' => 100, // Items can either be a number, or a specified group of items
            'workerCount' => 6, // Worker Count
            'beltLengthCount' => 3, // Conveyer Belt Length
            'put_on_conveyer' => ['A', 'B', 'C', 'D', '[ empty ]'], // The random items to be put on the conveyer
            'define_blank_space' => '0',
            'settings' => (object) [ // Worker Settings
                'arms' => [false, false, false, false],
                'names' => ['Bob', 'Joe', 'Murph', 'Jason', 'Mike', 'Larry'],
                'collectable_items' => ['A', 'B', 'C', 'D'], // What the worker wants to pick up
                'items_to_process' => ['A', 'B', 'C', 'D'], // What the worker needs to process an item
                'do_not_collect' => ['[ Processed ]'], // What a worker doesnt want to collect
                'processed_item' => '[ Processed ]', // What gets produced by a worker
                'increment_time' => 4, // How long it takes to process an item
                'define_blank_space' => '[ empty ]' // Define what a worker thinks is a blank space
            ]
        ]
    )
);
