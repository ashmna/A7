<?php

namespace A7\Utils;


class AutoTestGen
{
    /**
     * Save call records
     *
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

    /**
     * Generate
     *
     * @param string $dataFilePath
     * @param string $path
     * @param string $namespacePrefix
     * @param string $type
     */
    public static function generate($dataFilePath, $path, $namespacePrefix, $type) {
        $data = self::formattingDataPerClass(self::getDataFromFile($dataFilePath), $type);

        foreach($data as $class => $row) {
            list($testFileName, $classPath, $namespace) = self::formatClassNames($class, $path);

            $row["useList"][] = "A7\\Tests\\Resources\\AbstractUnitTestCase";

            self::createDirs($classPath);
            $content = self::generateClassFile(
                $namespacePrefix."\\".$namespace,
                $row["useList"],
                $testFileName,
                $row["content"]
            );

            file_put_contents($classPath, $content);
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

    /**
     * Formatting data per class
     *
     * @param array $data
     * @param string $type
     * @return array
     */
    private static function formattingDataPerClass(array $data, $type)
    {
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
        return $perClass;
    }

    /**
     * Format class names
     *
     * @param string $class
     * @param string $path
     * @return string[3]
     */
    private static function formatClassNames($class, $path)
    {
        $classPath = $path.str_replace("\\", "/", $class);
        $testFileName = basename($classPath). "Test";
        $classPath = dirname($classPath). DIRECTORY_SEPARATOR . $testFileName.".php";
        $namespace = str_replace("/", "\\", dirname(str_replace("\\", "/", $class)));

        return [$testFileName, $classPath, $namespace];
    }

    /**
     * Attempts to create the directory specified by pathname.
     *
     * @param $path
     */
    private static function createDirs($path)
    {
        if(!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
    }

    /**
     * Generate class file
     *
     * @param string $namespace
     * @param string[] $useList
     * @param string $className
     * @param string[] $contentData
     * @return string
     */
    private static function generateClassFile($namespace, $useList, $className, $contentData)
    {
        $c = [];

        $c[] = "<?php";
        $c[] = "";
        $c[] = "namespace {$namespace};";
        $c[] = "";
        $c[] = "";
        foreach(array_unique($useList) as $use) {
            $c[] =  "use {$use};\n";
        }
        $c[] = "";
        $c[] = "";
        $c[] = "";
        $c[] = "class {$className} extends AbstractUnitTestCase";
        $c[] = "{";
        $c[] = implode("\n", $contentData);
        $c[] = "}";

        return implode("\n", $c);
    }

}
