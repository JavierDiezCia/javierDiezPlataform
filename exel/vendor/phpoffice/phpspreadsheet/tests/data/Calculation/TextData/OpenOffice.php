<?php

declare(strict_types=1);

return [
    'OO allows CHAR(0)' => ["\x00", '=CHAR(0)'],
    'OO treats CODE(bool) as 0/1' => ['48', '=CODE(FALSE)'],
    'OO treats bool as string as 0/1 to REPT' => ['111', '=REPT(true, 3)'],
    'OO treats bool as string as 0/1 to CLEAN' => ['0', '=CLEAN(false)'],
    'OO treats bool as string as 0/1 to TRIM' => ['1', '=TRIM(true)'],
    'OO treats bool as string as 0/1 to LEN' => ['1', '=LEN(false)'],
    'OO treats bool as string as 0/1 to EXACT parm 1' => [true, '=EXACT(true, 1)'],
    'OO treats bool as string as 0/1 to EXACT parm 2' => [true, '=EXACT(0, false)'],
    'OO treats bool as string as 0/1 to FIND parm 1' => [2, '=FIND(true, "210")'],
    'OO treats bool as string as 0/1 to FIND parm 2' => [1, '=FIND(0, false)'],
    'OO treats true as int 1 to FIND parm 3' => [1, '=FIND("a", "aba", true)'],
    'OO treats false as int 0 to FIND parm 3' => ['#VALUE!', '=FIND("a", "aba", false)'],
    'OO treats bool as string as 0/1 to SEARCH parm 1' => [2, '=SEARCH(true, "210")'],
    'OO treats bool as string as 0/1 to SEARCH parm 2' => [1, '=SEARCH(0, false)'],
    'OO treats true as int 1 to SEARCH parm 3' => [1, '=SEARCH("a", "aba", true)'],
    'OO treats false as int 0 to SEARCH parm 3' => ['#VALUE!', '=SEARCH("a", "aba", false)'],
    'OO treats true as 1 to REPLACE parm 1' => ['10', '=REPLACE(true, 3, 1, false)'],
    'OO treats false as 0 to REPLACE parm 4' => ['he0lo', '=REPLACE("hello", 3, 1, false)'],
    'OO treats false as 0 SUBSTITUTE parm 1' => ['6', '=SUBSTITUTE(true, "1", "6")'],
    'OO treats true as 1 SUBSTITUTE parm 4' => ['zbcade', '=SUBSTITUTE("abcade", "a", "z", true)'],
    'OO TEXT boolean in lieu of string' => ['0', '=TEXT(false, "@")'],
    'OO VALUE boolean in lieu of string' => ['0', '=VALUE(false)'],
    'OO NUMBERVALUE boolean in lieu of string' => ['1', '=NUMBERVALUE(true)'],
    'OO TEXTJOIN boolean in lieu of string' => ['1-0-1', '=TEXTJOIN("-", true, true, false, true)'],
];
