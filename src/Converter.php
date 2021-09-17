<?php
/**
 * Created by PhpStorm.
 * User: Hiệp Nguyễn
 * Date: 17/09/2021
 * Time: 18:18
 */

namespace Nguyenhiep\MakeTree;

use Illuminate\Support\Arr;

class Converter
{
    public function classifyHeadings(array $headings, int $last_page): array
    {
        $classified_headings = [];
        $iMax                = count($headings);
        for ($i = 0; $i < $iMax; $i++) {
            if ($root = Arr::get($headings, $i)) {
                $i = $this->getChildren($i, $headings, $root);
                if ($last_root = Arr::get($classified_headings, $last_key = array_key_last($classified_headings))) {
                    $classified_headings[$last_key]["end_page"] = $root["start_page"];
                    if ($childs = Arr::get($last_root, "childs")) {
                        $classified_headings[$last_key]["childs"][array_key_last($childs)]["end_page"] = $root["start_page"];
                    }
                }
                if (($last_heading = Arr::get($classified_headings, $last_key = array_key_last($classified_headings)))
                    && $last_heading["start_page"] === $root["start_page"]
                    && $last_heading["left"] === $root["left"]
                    && empty($last_heading["childs"])) {
                    $root["title"]                  = "{$last_heading['title']} {$root['title']}";
                    $classified_headings[$last_key] = $root;
                } else {
                    $classified_headings[] = $root;
                }
            }
        }
        return $this->addEndPageForHeadings($classified_headings, $last_page);
    }

    protected function getChildren(int $i, array $headings, array &$root): int
    {
        if (!Arr::get($root, "childs")) {
            $root["childs"] = [];
        }
        for ($j = $i + 1, $jMax = count($headings); $j < $jMax; $j++) {
            if ($next = Arr::get($headings, $j)) {
                if (($next_left = $next["left"]) === $root["left"]) {
                    return $j - 1;
                }
                if ($next_left > $root["left"]) {
                    $first_child = Arr::get($root["childs"], array_key_first($root["childs"]));
                    $last_child  = Arr::get($root["childs"], $last_key = array_key_last($root["childs"]));
                    if ($first_child && $last_child) {
                        if ($next_left === $last_child["left"]
                            || $next_left === $first_child["left"]
                            || ($next_left > $last_child["left"] && $next["prefix"] === $last_child["prefix"])) {
                            $root["childs"][$last_key]["end_page"] = $next["start_page"];
                            $next["level"]                         = $root["level"] + 1;
                            $root["childs"][]                      = $next;
                        } elseif ($next_left > $last_child["left"]) {
                            $next["level"]             = $last_child["level"] + 1;
                            $last_child["childs"][]    = $next;
                            $j                         = $this->getChildren($j, $headings, $last_child);
                            $root["childs"][$last_key] = $last_child;
                        } else {
                            return $j - 1;
                        }
                    } else {
                        $next["level"]    = $root["level"] + 1;
                        $root["childs"][] = $next;
                    }
                } else {
                    return $j - 1;
                }
            }
        }
        return $i;
    }

    protected function addEndPageForHeadings(array $classified_headings, int $last_page): array
    {
        foreach ($classified_headings as $key => &$heading) {
            if (!($next = Arr::get($classified_headings, $key + 1))) {
                $heading["end_page"] = $last_page;
            }
            if (!Arr::get($heading, "end_page")) {
                $heading["end_page"] = $next["start_page"];
            }
            if ($childs = Arr::get($heading, "childs")) {
                $heading["childs"] = $this->addEndPageForHeadings($childs, $heading["end_page"]);
            }
        }
        return $classified_headings;
    }
}