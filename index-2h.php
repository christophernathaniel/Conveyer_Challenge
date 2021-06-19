<?php

// Functional Model


// Input = A, B and NULL
// -> These are in a random order

// 6 workers (array with object of current operation)
// -> arm A
// -> arm B
// -> Processing Time

// Conveyer Belt (Array with object of object status)


// Ticker (Function to move forward time / steps)
// -> array_shift 1 object
// -> array_pop 1 object per tick to keep the belt moving
echo '<pre>';
$getAnswers = ticker(); // App Init

echo 'Objects Fully Manufactured: ' . $getAnswers->sum_manufactured . '<br>';
echo 'Objects Not Touched by Worker: ' . $getAnswers->sum_no_worker_interaction . '<br>';
echo 'Empty Objects Processed: ' . $getAnswers->empty_objects_processed;

echo '</pre>';

echo '<pre>';
var_dump($getAnswers);
echo '</pre>';


// Function to create conveyer belt item
function createObject($id, $to_create)
{
    shuffle($to_create);

    $object = (object) [
        'id' => $id,
        'inventory' => $to_create[0], // Random Generation
        'status' => 0,
        'step' => 0
    ];

    return $object;
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

function moveBelt($belt, $random, $i)
{



    // Move Belt - Belt will be limited to a maximum of 3 items

    // Increment Element
    for ($x = 0; $x < count($belt); $x++) {
        if (!empty($belt[$x])) {
            $belt[$x]->step = $belt[$x]->step + 1;
        }
    }
    // Handle Removal of Old

    array_unshift($belt, createObject($i, $random));

    // store the item to drop off the belt
    $getDeletedItem = $belt[array_key_last($belt)];
    array_pop($belt);

    // We have to tell what items are removed
    return (object) [
        'belt' => $belt,
        'removed' => $getDeletedItem
    ];
}


// function to process steps
function ticker()
{
    $workerCount = 6;
    $stepCount = 100;
    $beltLength = 4;
    $belt = [];
    $collection = [];
    $workers = [];
    $pushedObjects = [];
    $names = ['bob', 'joe', 'murph', 'json', 'mike', 'larry'];
    $random = ['a', 'b', 0];
    // Belt Creation
    for ($x = 0; $x < $beltLength; $x++) {
        array_push($belt, null);
    }

    // Our randomiser variables;



    // Create all Workers
    $beltid = 0;
    for ($x = 0; $x < $workerCount; $x++) {

        array_push($workers, createWorker($x, $names, $beltid));

        if ($x % 2 != 0) {
            $beltid++;
        }
    }

    // Basic loop to act as our ticker
    for ($i = 0; $i < $stepCount; $i++) {


        // Move belt, and update values
        $moveBelt = moveBelt($belt, $random, $i);
        $belt = $moveBelt->belt;
        array_push($collection, $moveBelt->removed);


        // Process the worker
        $processWorkers = processWorkers($workers, $belt);

        $belt = $processWorkers->belt;
        $workers = $processWorkers->workers;
    }


    // Sum Final Built Objects
    $sum_manufactured = 0;
    $sum_no_worker_interaction = 0;
    $empty_objects_processed = 0;
    for ($i = 0; $i < count($collection); $i++) {

        if (!empty($collection[$i]->inventory)) {
            // Count objects with C
            if ($collection[$i]->inventory == 'c') {
                $sum_manufactured++;
            }

            if ($collection[$i]->inventory == 'a' || $collection[$i]->inventory == 'b') {
                $sum_no_worker_interaction++;
            }
        }


        if (empty($collection[$i]->inventory)) {

            $empty_objects_processed++;
        }
    };


    return (object) [
        'collection' => $collection,
        'belt' => $belt,
        'workers' => $workers,
        'sum_manufactured' => $sum_manufactured,
        'sum_no_worker_interaction' => $sum_no_worker_interaction,
        'empty_objects_processed' => $empty_objects_processed,
    ];
};



function processWorkers($workers, $belt)
{

    // Reset belt to free it up to tasks
    for ($i = 0; $i < count($belt); $i++) {
        if (!empty($belt[$i])) {
            $belt[$i]->status = 0;
        }
    }


    // Process all Workers
    for ($i = 0; $i < count($workers); $i++) {
        if (!empty($belt[$i])) {
            // See whats on the belt
            $workerToBelt = $belt[$workers[$i]->looksafter];


            // Flag that the belt is busy
            if ($workerToBelt->status == 1) {
                continue;
            }

            //var_dump($workerToBelt->inventory);

            // if a is available && arm doesnt have item
            if ($workerToBelt->inventory == 'a' && $workers[$i]->arm_a == false) {
                $workers[$i]->arm_a = true;
                $belt[$workers[$i]->looksafter]->inventory = 0;
                $belt[$workers[$i]->looksafter]->status = 1;
            }


            // if b is available & arm doesn't have item
            if ($workerToBelt->inventory == 'b' && $workers[$i]->arm_b == false) {
                $workers[$i]->arm_b = true;
                $belt[$workers[$i]->looksafter]->inventory = 0;
                $belt[$workers[$i]->looksafter]->status = 1;
            }

            if ($workers[$i]->arm_a == true && $workers[$i]->arm_b == true) {

                $workers[$i]->increment++;

                if ($workers[$i]->increment > 4) {
                    // Place back on belt if belt is free
                    if ($belt[$workers[$i]->looksafter]->inventory == 0) {
                        $workers[$i]->arm_a = false;
                        $workers[$i]->arm_b = false;
                        $belt[$workers[$i]->looksafter]->inventory = 'c';
                        $workers[$i]->increment = 0;
                    };
                }
            }
        }
    }

    return (object) ['workers' => $workers, 'belt' => $belt];
}




// // function to process a workers involvement with an item
// function processWorker($belt, $beltLength, $random, $workers)
// {

//     // Needs to understand belt 1 belongs to 1 
//     // Does it take a step for the robot to pick the item up, or is it automatic?

//     // Reset the belts status after Turn
//     for ($i = 0; $i < $beltLength; $i++) {
//         if (isset($belt[$i])) {
//             $belt[$i]->status = 0;
//         }
//     }

//     for ($i = 0; $i < $beltLength; $i++) {
//         if (isset($belt[$i]->inventory)) {

//             if (!$belt[$i]->inventory == 0) {

//                 // Null Function
//                 feedWorker($belt[$i], [$i, $i + 1]);
//             } else {
//                 feedWorker($belt[$i]);

//                 $belt[$i]->status = 1;
//                 $belt[$i]->inventory = 0;
//             }
//         }
//     }

//     return $workers;
// }


// function feedWorker($workers, $getBeltItem, $index)
// {

//     for ($i = 0; $i < count($index); $i++) {

//         if ($worker[$index[$i]]) {

//             var_dump('workerfed');
//         }
//     }


//     // if ($getBeltitem->inventory == 'a') {
//     // }
// }