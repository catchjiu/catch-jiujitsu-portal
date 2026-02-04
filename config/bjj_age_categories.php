<?php

/**
 * BJJ age categories. Ranges are birth years for the base_year.
 * Each calendar year, ranges shift by +1 (e.g. Kid 1 in 2026 = 2022–2023).
 */
return [
    'base_year' => 2025,

    'kids' => [
        ['name' => 'Kid 1', 'min' => 2021, 'max' => 2022],
        ['name' => 'Kid 2', 'min' => 2019, 'max' => 2020],
        ['name' => 'Kid 3', 'min' => 2017, 'max' => 2018],
        ['name' => 'Kid 4', 'min' => 2015, 'max' => 2016],
        ['name' => 'Kid 5', 'min' => 2013, 'max' => 2014],
        ['name' => 'Kid 6', 'min' => 2011, 'max' => 2012],
    ],

    'adults' => [
        ['name' => 'Juvenile 16-17', 'min' => 2009, 'max' => 2010],
        ['name' => 'Adult 18', 'min' => 1997, 'max' => 2008],
        ['name' => 'Master 30', 'min' => 1991, 'max' => 1996],
        ['name' => 'Master 36', 'min' => 1986, 'max' => 1990],
        ['name' => 'Master 41', 'min' => 1981, 'max' => 1985],
        ['name' => 'Master 46', 'min' => 1976, 'max' => 1980],
        ['name' => 'Master 51', 'min' => 1971, 'max' => 1975],
        ['name' => 'Master 56', 'min' => null, 'max' => 1970], // ~1970 and older
    ],
];
