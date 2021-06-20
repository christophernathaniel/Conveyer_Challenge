<?php

// Format the answer;
function formatAnswer($getAnswers)
{

    $content = '';
    $content .= '<pre>';


    // Create buld Inventory
    $build_inventory = [];
    for ($i = 0; $i < count($getAnswers->return_all); $i++) {


        // Remove all inventory items that may return null
        if (!isset($getAnswers->return_all[$i]->inventory)) {
            continue;
        }

        // Push all inventory items to a single array
        if ($getAnswers->return_all[$i]->inventory) {
            array_push($build_inventory, $getAnswers->return_all[$i]->inventory);
        }
    }

    // Filter our similar items in to groups
    $build_inventory = array_count_values($build_inventory);
    foreach ($build_inventory as $key => $value) {
        $content .= 'Number of <b>' . $key . '</b> items is ' . $value . '<br>';
    }

    $content .= '</pre>';
    $content .= '<small>';

    $getLog = $getAnswers->log;

    // Loop around error logs
    for ($i = 0; $i < count($getLog); $i++) {
        $content .= 'The Conveyer Belt Moves....' . '<br>';
        for ($x = 0; $x < count($getLog[$i]); $x++) {
            for ($z = 0; $z < count($getLog[$i][$x]); $z++) {
                $content .= $getLog[$i][$x][$z] . '<br>';
            }
        }
    }
    $content .= '</small>';
    return $content;
}
