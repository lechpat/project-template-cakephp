<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * DblistsAdd shell command.
 *
 * Adding dblists(<list_name>) records into dblists table
 * in case the record doesn't exist.
 */
class DblistsAddShell extends Shell
{
    protected $modules;

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $this->out("Starting dblist records check...");

        $modules = $this->_findCsvModules();

        $this->modules = $modules;

        if (empty($this->modules)) {
            $this->out("Couldn't find CSV modules to parse");
            exit();
        }

        $dblists = [];

        foreach ($modules as $k => $module) {
            $lists = $this->_getDbListsFromCsv($module);

            if (!empty($lists)) {
                $dblists[$module] = $lists;
            }
        }

        $table = TableRegistry::get('Dblists');

        if (empty($dblists)) {
            $this->out("No lists found in the migrations of the project");
            exit();
        }

        foreach ($dblists as $module => $lists) {
            foreach ($lists as $list) {
                $record = $table->find()
                    ->where(['name' => $list])->first();

                if (!empty($record)) {
                    $this->out("Dblist record [$list] already exists in the dblists table");
                    continue;
                }

                $dblistEntity = $table->newEntity();
                $dblistEntity->name = $list;
                $dblistEntity->created = date('Y-m-d H:i:s', time());
                $dblistEntity->modified = date('Y-m-d H:i:s', time());

                if ($table->save($dblistEntity)) {
                    $this->out("Added [$list] to dblists table");
                }
            }
        }

        $this->out("Completed Dblist addition script");
    }

    /**
     * Get an array of dblists from migrations config
     *
     * @param string $module where to look for dblists
     * @return array $result containing indexed array of dblists
     */
    protected function _getDbListsFromCsv($module = null)
    {
        $result = [];

        if (empty($module)) {
            return $result;
        }

        $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MIGRATION, $module);
        $fields = json_decode(json_encode($mc->parse()), true);

        if (empty($fields)) {
            return $result;
        }

        foreach ($fields as $field) {
            if (preg_match('/^dblist\((.+)\)$/', $field['type'], $matches)) {
                $result[] = $matches[1];
            }
        }

        return $result;
    }

    /**
     * Find the list of CSV modules
     *
     * @return array List of modules
     */
    protected function _findCsvModules()
    {
        $result = [];

        $path = Configure::read('CsvMigrations.modules.path');
        if (!is_readable($path)) {
            throw new \RuntimeException("[$path] is not readable");
        }
        if (!is_dir($path)) {
            throw new \RuntimeException("[$path] is not a directory");
        }

        foreach (new \DirectoryIterator($path) as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            $result[] = $fileinfo->getFilename();
        }
        asort($result);

        return $result;
    }
}
