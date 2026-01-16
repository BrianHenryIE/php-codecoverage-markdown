<?php

/**
 * Utility to filter CodeCoverage objects to include only specific files.
 */

namespace BrianHenryIE\CodeCoverageMarkdown;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\XdebugDriver;
use SebastianBergmann\CodeCoverage\Filter;

class CoverageFilter
{
    /**
     * Creates a new CodeCoverage object filtered to only files that are in the provided list.
     *
     * Works with full file paths or relative paths (matching the end of the file path).
     *
     * @param CodeCoverage $oldCoverage An existing CodeCoverage object.
     * @param string[] $coveredFilesList List of files to narrow the report to contain.
     */
    public static function filterCoverage(CodeCoverage $oldCoverage, array $coveredFilesList): CodeCoverage
    {
        if (empty($coveredFilesList)) {
            return $oldCoverage;
        }

        $data = $oldCoverage->getData();

        $lineCoverage = $data->lineCoverage();

        $filteredLineCoverage = [];
        foreach ($lineCoverage as $filepath => $lineData) {
            // Do full filepath match first.
            if (in_array($filepath, $coveredFilesList)) {
                $filteredLineCoverage[$filepath] = $lineData;
                continue;
            }
            // Then check for relative path.
            foreach ($coveredFilesList as $coveredFilePath) {
                if (str_ends_with($filepath, $coveredFilePath)) {
                    $filteredLineCoverage[$filepath] = $lineData;
                    continue 2; // No need to check other covered files
                }
            }
        }

        $diffFilePaths = array_keys($filteredLineCoverage);

        $filter = new Filter();
        $filter->includeFiles($diffFilePaths);
        // Would it be possible to edit the class with reflection instead?
        // This requires XDEBUG_MODE=coverage
        // In tests, XDEBUG_MODE=coverage,debug
        $xdebugDriver = new XdebugDriver(
            $filter
        );
        if ($oldCoverage->collectsBranchAndPathCoverage()) {
            $xdebugDriver->enableBranchAndPathCoverage();
        } else {
            $xdebugDriver->disableBranchAndPathCoverage();
        }

        $newCoverage = new CodeCoverage(
            $xdebugDriver,
            $filter
        );
        unset($xdebugDriver, $filter);

        $newCoverageData = $newCoverage->getData();
        $newCoverageData->setLineCoverage($filteredLineCoverage);
        $newCoverage->setData($newCoverageData);
        $newCoverage->setTests($oldCoverage->getTests());

        return $newCoverage;
    }
}
