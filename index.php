<?php
require "youtube.php";

$youtube = new youtube();
$list = $youtube->search("inna");

echo '<pre>';
print_r($list);
