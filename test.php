<?php
/**
 * Created by PhpStorm.
 * User: Hiệp Nguyễn
 * Date: 17/09/2021
 * Time: 18:23
 */

require_once __DIR__ . "/vendor/autoload.php";

use Nguyenhiep\MakeTree\Converter;

try {
    $table_contents = json_decode(file_get_contents(__DIR__ . "/data/input.json"), true, 512, JSON_THROW_ON_ERROR);
    $classified_headings = (new Converter())->classifyHeadings($table_contents, 196);
    file_put_contents("output_" . time() . ".json", json_encode($classified_headings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    dd($classified_headings);
} catch (JsonException $e) {
    dd($e);
}
