<?php
$secret = "YOUR SECRET HERE";
$database_id = "YOUR DATABASE HERE";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"https://api.notion.com/v1/databases/$database_id/query");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = [
    'Authorization: Bearer '.$secret,
    'Notion-Version: 2022-06-28',
    'Accept-Encoding: application/json'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$server_output = curl_exec ($ch);

curl_close ($ch);

$tasks = json_decode($server_output, true);

$done = array();

foreach ($tasks["results"] as $task)
{
    if ($task["properties"]["Done"]["checkbox"] == true && $task["properties"]["Recurring Interval"]["number"] != NULL)
    {
        $done[] = $task;
    }
}

$new_tasks = array();

$i = 0;
foreach ($done as $done_task => $done_detail) {
    if ($done_detail["properties"]["Due Date"]["date"]["start"] != $done_detail["properties"]["Next Due Date"]["formula"]["date"]["start"])
    {
        $new_tasks[$i] = array(
            "parent" => array("database_id" => $database_id),
            "properties" => $done_detail["properties"]
        );
        $new_tasks[$i]["properties"]["Due Date"]["date"]["start"] = $done_detail["properties"]["Next Due Date"]["formula"]["date"]["start"];
        $new_tasks[$i]["properties"]["Done"]["checkbox"] = false;

        // unset formulas and relations
        unset($new_tasks[$i]["properties"]["Next Due Date"]);
        // unset($new_tasks[$i]["properties"]["Tags"]);
        // unset($new_tasks[$i]["properties"]["Sub-tasks"]);

        create_task($new_tasks[$i]);

        // archive the original task
        archive_task($done_detail["id"]);
        $i++;
    }
}

echo $i. " task has been created.";

function archive_task($page_id) {
    global $secret;
    $data = array("archived" => true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.notion.com/v1/pages/$page_id");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $headers = [
        'Authorization: Bearer '.$secret,
        'Notion-Version: 2022-06-28',
        'Accept-Encoding: application/json',
        'Content-Type: application/json'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $server_output = curl_exec ($ch);

    print_r($server_output);

    curl_close ($ch);
}

function create_task($data) {
    global $secret;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.notion.com/v1/pages");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $headers = [
        'Authorization: Bearer '.$secret,
        'Notion-Version: 2022-06-28',
        'Accept-Encoding: application/json',
        'Content-Type: application/json'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $server_output = curl_exec ($ch);

    print_r($server_output);

    curl_close ($ch);
}
