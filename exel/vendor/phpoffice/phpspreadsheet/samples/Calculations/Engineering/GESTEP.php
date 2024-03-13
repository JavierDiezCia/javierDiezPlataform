<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

require __DIR__ . '/../../Header.php';

$category = 'Engineering';
$functionName = 'GESTEP';
$description = 'Returns 1 if number ≥ step; returns 0 (zero) otherwise';

$helper->titles($category, $functionName, $description);

// Create new PhpSpreadsheet object
$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();

// Add some data
$testData = [
    [5, 4],
    [5, 5],
    [4, 5],
    [-4, -5],
    [-5, -4],
    [1],
];
$testDataCount = count($testData);

$worksheet->fromArray($testData, null, 'A1', true);

for ($row = 1; $row <= $testDataCount; ++$row) {
    $worksheet->setCellValue('C' . $row, '=GESTEP(A' . $row . ',B' . $row . ')');
}

$comparison = [
    0 => 'Value %d is less than step %d',
    1 => 'Value %d is greater than or equal to step %d',
];

// Test the formulae
for ($row = 1; $row <= $testDataCount; ++$row) {
    /** @var int */
    $aValue = $worksheet->getCell('A' . $row)->getValue();
    /** @var int */
    $bValue = $worksheet->getCell('B' . $row)->getValue();
    /** @var int */
    $cValue = $worksheet->getCell('C' . $row)->getCalculatedValue();
    $helper->log(sprintf(
        '(E%d): Compare value %d and step %d - Result is %d - %s',
        $row,
        $aValue,
        $bValue,
        $cValue,
        sprintf(
            $comparison[$cValue],
            $aValue,
            $bValue,
        )
    ));
}
