<?php

// Get the answer formatter


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
    $settings = $data->settings; // Give the workers settings
    $random = $data->put_on_conveyer; // Create Elements to be processed

    $belt = createBelt($beltLength);
    $workers = createWorkers($workerCount, $settings);

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
function createWorker($id, $settings, $beltid)
{
    $object = (object) [
        'id' => $id,
        'name' => $settings->names[$id],
        'arms' => $settings->arms,
        'looksafter' => $beltid,
        'collectable_items' => $settings->collectable_items, // What the worker wants to pick up
        'items_to_process' => $settings->items_to_process, // What the worker needs to process
        'do_not_collect' => $settings->do_not_collect, // What a worker doesnt want to collect
        'processed_item' => $settings->processed_item, // What gets produced by a worker
        'increment_time' => $settings->increment_time,
        'define_blank_space' => $settings->define_blank_space,
        'increment' => 0,

    ];

    return $object;
}

// Create all Workers
function createWorkers($workerCount, $settings)
{
    $beltid = 0;
    $workers = [];
    for ($x = 0; $x < $workerCount; $x++) {
        array_push($workers, createWorker($x, $settings, $beltid));

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

    return (object) [
        'return_all' => $returnView->collection,
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
                // if the belt is busy then continue
                continue;
            }

            // Process Arm Collection
            for ($x = 0; $x < count($workers[$i]->arms); $x++) {

                // If arm has an item OR the belt has no items
                if ($workers[$i]->arms[$x] == true || $workerToBelt->inventory == $workers[$i]->define_blank_space) {
                    continue;
                }

                // If collectable items is already in the arms, then don't add it to the arms
                if (in_array($belt[$workers[$i]->looksafter]->inventory, $workers[$i]->arms)) {
                    continue;
                }

                // If item not to collect is on the conveyer
                if (in_array($belt[$workers[$i]->looksafter]->inventory, $workers[$i]->do_not_collect)) {
                    array_push($worker_talk, [$workers[$i]->name . ' tries to pick up an item, its a finished product and he doesnt want it']);
                    continue 2;
                }

                // Check if item can be collected

                if (!in_array($belt[$workers[$i]->looksafter]->inventory, $workers[$i]->collectable_items)) {
                    continue;
                }

                $workers[$i]->arms[$x] = $belt[$workers[$i]->looksafter]->inventory; // Pick up item from belt
                $belt[$workers[$i]->looksafter]->inventory = $workers[$i]->define_blank_space; // Remove item from belt
                $belt[$workers[$i]->looksafter]->status = 1; // Signal belt as busy
                array_push($worker_talk, [$workers[$i]->name . ' has picked up ' . $workers[$i]->arms[$x]]);
            };


            // If has items needed to process
            $itemsToProcess = array_intersect($workers[$i]->items_to_process, $workers[$i]->arms);
            if ($itemsToProcess == $workers[$i]->items_to_process) {
                $workers[$i]->increment++;

                if ($workers[$i]->increment >= $workers[$i]->increment_time) {

                    // Reset Arms
                    for ($x = 0; $x < count($workers[$i]->arms); $x++) {
                        $workers[$i]->arms[$x] = false;
                    };

                    // Flag belt as busy 
                    $belt[$workers[$i]->looksafter]->status = 1;

                    // Place processed item on belt
                    $belt[$workers[$i]->looksafter]->inventory = $workers[$i]->processed_item;

                    // Reset Increment
                    $workers[$i]->increment = 0;

                    array_push($worker_talk, [$workers[$i]->name . ' has finished processing and placed a completed product (' . $workers[$i]->processed_item . ') back on the conveyer']);
                }
            };
        }
    }

    return (object) ['workers' => $workers, 'belt' => $belt, 'worker_talk' => $worker_talk];
}
