<?php
function console_log2($prefix, $data)
{
    echo "<script>console.log(" . json_encode($prefix) . ", " . json_encode($data) . ");</script>";
}

function console_log1($data)
{
    echo "<script>console.log( " . json_encode($data) . ");</script>";
}
