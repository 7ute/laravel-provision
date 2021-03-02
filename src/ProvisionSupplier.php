<?php

namespace SevenUte\LaravelProvision;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ProvisionSupplier
{
    protected $connection;
    protected $folder;
    protected $table;

    public function __construct()
    {
        $this->reloadConfiguration();
    }

    public function reloadConfiguration()
    {
        $this->connection = config('provision.database.connection', 'default');
        $this->folder = config('provision.folder', 'database' . DIRECTORY_SEPARATOR . 'provisions');
        $this->table = config('provision.database.table', 'provisions');
    }

    /**
     * Returns the provision database base query
     * @return string The folder where the provisions are stored
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Returns the provision database base query
     */
    protected function getQuery()
    {
        try {
            return DB::connection(config("database.{$this->connection}"))
                ->table($this->table);
        } catch (Exception $error) {
            throw new Exception("Can’t connect to the database. Did you forget to run provision:install first ?", 1, $error);
        }
    }

    /**
     * Gets the current provisionning folder
     */
    public function getAlreadyRanProvisions()
    {
        return $this->getQuery()->get();
    }

    /**
     * Adds a provision to the database
     */
    public function add($name)
    {
        return $this->getQuery()->insert(['provision' => $name]);
    }

    /**
     * Removes a provision from the database
     */
    public function remove($name)
    {
        return $this->getQuery()->where(['provision' => $name])->delete();
    }

    /**
     * Gets the current provisionning folder
     * @param boolean $full Get the full absolute path
     */
    public function getProvisionsFolder($full = false)
    {
        if (!file_exists(base_path($this->folder))) {
            throw new Exception("The provision folder does not exist. Did you forget to run provision:install first ?", 1);
        }
        if (!$full) {
            return $this->folder;
        }
        return base_path($this->folder);
    }

    /**
     * Get all of the provision files in a given path.
     *
     * @param  string|array|null  $paths
     * @return Collection
     */
    public function getProvisionFiles($paths = '')
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$path] : glob(base_path($this->folder) . DIRECTORY_SEPARATOR . '*_*.php');
        })->filter()->values()->keyBy(function ($file) {
            return $this->getProvisionName($file);
        })->sortBy(function ($file, $key) {
            return $key;
        });
    }

    /**
     * Get the name of the provision.
     *
     * @param  string  $path
     * @return string
     */
    public function getProvisionName($path)
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Creates a provision name from a classname
     *
     * @param  string  $classname
     * @return string
     */
    public function makeNameFromClass($classname)
    {
        $date = Carbon::now()->format('Y_m_d_His');
        return "{$date}_" . Str::snake($classname);
    }

    /**
     * Get the class name of a provision name.
     *
     * @param string $filename
     * @return string
     */
    public function getClassFromName($filename)
    {
        return Str::studly(preg_replace('~[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_~ui', '', $filename));
    }

    /**
     * Creates a provision file for the classname
     *
     * @param  string  $classname
     * @return string path of the file relative to project root
     */
    public function createProvisionFile($classname)
    {
        $folder = $this->getProvisionsFolder();
        $provision_name = $this->makeNameFromClass($classname);
        $provision_origion = __DIR__ . '/../database/provisions/provision_class.php.stub';
        $provision_content = @file_get_contents($provision_origion);
        if (!$provision_content) {
            throw new Exception("The provision file stubs could not be found in the package", 1);
        }
        $provision_content = str_replace('{ProvisionClass}', $classname, $provision_content);
        $provision_path = $folder . DIRECTORY_SEPARATOR . "{$provision_name}.php";
        $provision_destination = base_path($provision_path);
        file_put_contents($provision_destination, $provision_content);
        return $provision_path;
    }
}
