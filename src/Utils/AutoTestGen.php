<?php

namespace A7\Utils;


class AutoTestGen
{
    /**
     * @param CallRecord[] $records
     * @param string $path
     * @return int
     */
    public static function saveCallRecords($records, $path)
    {
        $data  = self::getDataFromFile($path);

        foreach($records as $record) {
            $className = $record->getClassName();
            if(!isset($data[$className])) {
                $data[$className] = [
                    'Unit'        => [],
                    'Integration' => [],
                ];
            }

            $data[$className]['Unit'][$record->getKey()] = [$record->getUseList(), $record->getRecordAsUnitTestFunction()];
            $data[$className]['Integration'][$record->getKey()] = [$record->getUseList(), $record->getRecordAsIntegrationTestFunction()];
        }

        return self::saveData($data, $path);
    }


    public static function generate($dataFilePath, $path, $namespacePrefix, $type) {
        $data = self::getDataFromFile($dataFilePath);

        $perClass = [];
        foreach ($data as $c => $data1) {
            $perClass[$c] = [
                "content" => [],
                "useList" => []
            ];
            foreach($data1[$type] as $item) {
                $perClass[$c]["content"][] = $item[1];
                $perClass[$c]["useList"] = array_merge($perClass[$c]["useList"], $item[0]);
            }
        }

        foreach($perClass as $class => $row) {
            $classPath = $path.str_replace("\\", "/", $class);
            $testFileName = basename($classPath). "Test";
            $classPath = dirname($classPath). DIRECTORY_SEPARATOR . $testFileName.".php";
            if(!file_exists(dirname($classPath))) {
                mkdir(dirname($classPath), 0777, true);
            }
            $namespace = str_replace("/", "\\", dirname(str_replace("\\", "/", $class)));

            $testClassContent = "<?php\n";

            $testClassContent .= "namespace {$namespacePrefix}\\{$namespace};\n";

            $testClassContent .= "\n";
            foreach(array_unique($row["useList"]) as $use) {
                $testClassContent .= "use {$use};\n";
            }
            $testClassContent .= "use A7\\Tests\\Resources\\AbstractUnitTestCase;\n";
            $testClassContent .= "\n\n";

            $testClassContent .= "class {$testFileName} extends AbstractUnitTestCase \n{\n\n";
            $testClassContent .= implode("\n", $row["content"]);
            $testClassContent .= "\n}\n";

            file_put_contents($classPath, $testClassContent);
        }
    }


     /**
     * Get data from file
     *
     * @param string $path
     * @return array
     */
    private static function getDataFromFile($path)
    {
        $data = [];
        if(file_exists($path)) {
            $data = include $path;
        }
        if(!is_array($data)) {
            $data = [];
        }
        return $data;
    }

    /**
     * @param array $data
     * @param $path
     * @return int
     */
    private static function saveData(array $data, $path)
    {
        $content = "<?php return ".var_export($data, true)."; ";
        return file_put_contents($path, $content);
    }

}
