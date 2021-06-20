<?php

/* Please note that this is the 5 hour mark of the challenge */

/*
  1. Components A and B enter the start of the belt. These are at completely random intervals with 0 representing an empty space.
  2. The belt must have x slots. x slots will have 2 workers. The workers will be aware of the slots, but the slots only focus on the items.
  3.  When a worker interacts with the belt, it signals the belt to lock up
  3a. The worker will seek to pick up an item if its arms can contain it, or place an item once it has finished constructing the item
  3b. The worker can observe the conveyer belt when both arms are full, but can not interact with it. It will not be able to lock up the belt.
  4.  When a worker picks up an item, it locks the belt. When a worker places an item it locks the belt.
  5. After both workers have observed the belt, the second worker signals the belt to unlock
*/

/* 
  The items will always derive at 97 whilst calculating Fully Manufactured + Not Touched by Worker + Empty Objects
*/

// Functional Model

// Input = A, B and NULL
// -> These are in a random order

// 6 workers (array with object of current operation)
// -> arm A
// -> arm B
// -> Processing Time

// Conveyer Belt (Array with object of object status)
// -> Status (Is busy)
// -> Current Step
// -> Current Object


// Create a time-function to process moves
// -> Move items along with array
// -> Handle all objects and prep for the return view


echo '<h2>The Conveyer Belt Test Answer</h2>';

echo formatAnswer(
    init(
        [
            'items' => 100, // Items can either be a number, or a specified group of items
            'workerCount' => 6,
            'beltLengthCount' => 3
        ]
    )
);


echo '<h2>Test #1</h2>';
echo 'Steps: 6<br>';
echo 'Expected Outcome: 1 Item Produced';

echo formatAnswer(
    init(
        [
            'items' => ['a', 'b', '0', '0', '0', '0'], // Items can either be a number, or a specified group of items
            'workerCount' => 2,
            'beltLengthCount' => 1
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
            'beltLengthCount' => 1
        ]
    )
);


echo '<h2>Test #3</h2>';

echo formatAnswer(
    init(
        [
            'items' => ['a', 'b', 'a', 'b', 'a', 'a', 'a', '0', '0'], // Items can either be a number, or a specified group of items
            'workerCount' => 2,
            'beltLengthCount' => 1
        ]
    )
);

// Format the answer;
function formatAnswer($getAnswers)
{

    $notPickedA = 0;
    $notPickedB = 0;

    $content = '';
    $content .= '<pre>';
    $content .= '<b>Products off the production line</b>: ' . $getAnswers->sum_manufactured . '<br>';
    $content .= 'Objects Not Touched by Worker: ' . $getAnswers->sum_no_worker_interaction . '<br>';
    $content .= 'Times conveyer belt produced nothing: ' . $getAnswers->empty_objects_processed . '<br>';


    for ($i = 0; $i < count($getAnswers->return_all); $i++) {
        if (!isset($getAnswers->return_all[$i]->inventory)) {
            continue;
        }
        // Test for returned items
        if ($getAnswers->return_all[$i]->inventory == 'a') {
            $notPickedA++;
            //
        }

        if ($getAnswers->return_all[$i]->inventory == 'b') {
            $notPickedB++;
        }
    }

    $content .= '<b>A products</b> not picked up: ' . $notPickedA  . '<br>';
    $content .= '<b>B products</b> not picked up: ' .  $notPickedB . '<br>';


    $content .= '</pre>';

    $content .= '<small>';

    $getLog = $getAnswers->log;
    for ($i = 0; $i < count($getLog); $i++) {
        $content .= 'The Conveyer Belt Moves....' . '<br>';

        for ($x = 0; $x < count($getLog[$i]); $x++) {
            // $content .= $getLog[$i][$x][0];

            for ($z = 0; $z < count($getLog[$i][$x]); $z++) {
                $content .= $getLog[$i][$x][$z] . '<br>';
            }
        }
    }

    $content .= '</small>';

    return $content;
}


// function to process steps
function init($data)
{
    // Convert to object
    $data = (object) $data;

    $workerCount = $data->workerCount; // How many workers are created
    $process_order = null;

    if (is_array($data->items)) {
        $stepCount = count($data->items);
        $process_order = $data->items;
    } else {
        $stepCount = $data->items; // How many steps to process
    }

    $beltLength = $data->beltLengthCount; // Length of Belt
    $names = ['Bob', 'Joe', 'Murph', 'Jason', 'Mike', 'Larry']; // Give the workers names
    $random = ['a', 'b', '0']; // Create Elements to be processed

    $belt = createBelt($beltLength);
    $workers = createWorkers($workerCount, $names);

    // Update the view with our Ticker
    $ticker = ticker($belt, $workers, $stepCount, $random, $process_order);
    return updateView((object) [
        'collection' => $ticker->collection,
        'worker_talk' => $ticker->worker_talk
    ]);
}


// Function to create object existing on conveyer beltlk
function createObject($id, $newinventory)
{

    $object = (object) [
        'id' => $id,
        'inventory' => $newinventory, // Random Generation
        'status' => 0,
        'step' => 0,
    ];

    return $object;
}


// Function to create the new belt
function createBelt($beltLength)
{
    $belt = [];
    for ($x = 0; $x < $beltLength; $x++) {
        array_push($belt, null);
    }

    return $belt;
}


// function to create a new worker
function createWorker($id, $names, $beltid)
{
    $object = (object) [
        'id' => $id,
        'name' => $names[$id],
        'arm_a' => false,
        'arm_b' => false,
        'looksafter' => $beltid,
        'increment' => 0,

    ];

    return $object;
}

// Create all Workers
function createWorkers($workerCount, $names)
{
    $beltid = 0;
    $workers = [];
    for ($x = 0; $x < $workerCount; $x++) {
        array_push($workers, createWorker($x, $names, $beltid));

        if ($x % 2 != 0) {
            $beltid++;
        }
    }

    return $workers;
}

// Process the ongoing moving of the belt
function moveBelt($belt, $random, $i, $process_order = null)
{
    // Move Belt - Belt will be limited to a maximum of 3 items

    // Increment Element
    for ($x = 0; $x < count($belt); $x++) {
        if (!empty($belt[$x])) {
            $belt[$x]->step = $belt[$x]->step + 1;
        }
    }

    // Check if we are handling unique, items, or if the user has pre-defined an order (for testing)
    $newinventory = null;
    if (!empty($process_order)) {
        $newinventory = $process_order[$i];
    } else {
        shuffle($random);
        $newinventory = $random[0];
    }

    // Handle creation of new object (Conveyer belt item)
    $newObject = createObject($i, $newinventory);

    array_unshift($belt, $newObject);

    // Store the old item (Last conveyer belt item before deletion)
    $getDeletedItem = $belt[array_key_last($belt)];

    // Handle removal of old conveyer belt item
    array_pop($belt);

    // Return the new state of belt + the deleted item
    return (object) [
        'belt' => $belt,
        'created' => $newObject->inventory,
        'removed' => $getDeletedItem,
    ];
}

// Function to process time
function ticker($belt, $workers, $stepCount, $random, $process_order = null)
{

    $created_log = [];
    $collection = [];
    $worker_talk = [];

    for ($i = 0; $i < $stepCount; $i++) {

        // Move belt, and update values
        $moveBelt = moveBelt($belt, $random, $i, $process_order);
        $belt = $moveBelt->belt;

        // Push Collection + What objects have been created
        array_push($created_log, $moveBelt->created);
        array_push($collection, $moveBelt->removed);

        if (isset($moveBelt->removed->inventory)) {
            if ($moveBelt->removed->inventory == 'c') {
                array_push($worker_talk, [['A finished item has dropped of the conveyer']]);
            }
        };

        // Process the worker, Update Belt + Workers
        $processWorkers = processWorkers($workers, $belt);
        $belt = $processWorkers->belt;
        $workers = $processWorkers->workers;
        array_push($worker_talk, $processWorkers->worker_talk);
    }


    return (object)['collection' => $collection, 'worker_talk' => $worker_talk];
}

// Function to update the view
function updateView($returnView)
{
    $sum_manufactured = 0;
    $sum_no_worker_interaction = 0;
    $empty_objects_processed = 0;

    for ($i = 0; $i < count($returnView->collection); $i++) {

        if (!empty($returnView->collection[$i])) {

            $inventory = $returnView->collection[$i]->inventory; // Get Inventory

            // Count objects with C
            if ($inventory  == 'c') {
                $sum_manufactured++;
            }

            if ($inventory == 'a' || $inventory == 'b') {
                $sum_no_worker_interaction++;
            }
        }

        if (!empty($returnView->collection[$i])) {
            if ($inventory == '0') {
                $empty_objects_processed++;
            }
        }
    }

    return (object) [
        'return_all' => $returnView->collection,
        'sum_manufactured' => $sum_manufactured,
        'sum_no_worker_interaction' => $sum_no_worker_interaction,
        'empty_objects_processed' => $empty_objects_processed,
        'log' => $returnView->worker_talk
    ];
}

// The key logic for a worker picking up an item, and conveyer belt locking up
function processWorkers($workers, $belt)
{

    $worker_talk = [];

    // Reset belt to free it up to tasks
    for ($i = 0; $i < count($belt); $i++) {
        if ($belt[$workers[$i]->looksafter]) {
            $belt[$workers[$i]->looksafter]->status = 0;
        }
    }

    // Process all Workers at current Step
    for ($i = 0; $i < count($workers); $i++) {

        if ($belt[$workers[$i]->looksafter]) {

            // See whats on the belt
            $workerToBelt = $belt[$workers[$i]->looksafter];

            // Only check if both conditions are met
            if ($workerToBelt->status == 1 &&  $workers[$i]->increment > 1) {
                if ($belt[$workers[$i]->looksafter]->inventory == 'c') {
                    array_push($worker_talk, [$workers[$i]->name . ' tries to pick up an item, its a finished product and he doesnt want it']);
                } else {
                    array_push($worker_talk, [$workers[$i]->name . ' tries to pick up an item, its busy']);
                }
                // if the belt is busy then continue
                continue;
            }

            // if a is available && arm doesnt have item
            if ($workerToBelt->inventory == 'a' && $workers[$i]->arm_a == false) {
                $workers[$i]->arm_a = true; // Arm as containing Item
                $belt[$workers[$i]->looksafter]->inventory = '0'; // Remove item from belt
                $belt[$workers[$i]->looksafter]->status = 1; // Signal belt as busy
                array_push($worker_talk, [$workers[$i]->name . ' has picked up A']);
            }

            // if b is available & arm doesn't have item
            if ($workerToBelt->inventory == 'b' && $workers[$i]->arm_b == false) {
                $workers[$i]->arm_b = true; // Arm as containing Item
                $belt[$workers[$i]->looksafter]->inventory = '0'; // Remove item from belt
                $belt[$workers[$i]->looksafter]->status = 1; // Signal belt as busy
                array_push($worker_talk, [$workers[$i]->name . ' has picked up B']);
            }

            if ($workers[$i]->arm_a == true && $workers[$i]->arm_b == true) {

                // If both exist, start incrementing
                $workers[$i]->increment++;

                // Check if increment surpases 4;
                if ($workers[$i]->increment >= 4) {

                    // Place back on belt if belt is free
                    if ($belt[$workers[$i]->looksafter]->inventory == '0') {

                        // Reset Arms
                        $workers[$i]->arm_a = false;
                        $workers[$i]->arm_b = false;

                        // Flag belt as busy 
                        $belt[$workers[$i]->looksafter]->status = 1;

                        // Place C on belt
                        $belt[$workers[$i]->looksafter]->inventory = 'c';

                        // Reset Increment
                        $workers[$i]->increment = 0;

                        array_push($worker_talk, [$workers[$i]->name . ' has finished processing and placed a completed product back on the conveyer']);
                    };
                }
            }
        }
    }

    return (object) ['workers' => $workers, 'belt' => $belt, 'worker_talk' => $worker_talk];
}
