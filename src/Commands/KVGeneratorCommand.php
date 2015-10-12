<?php namespace Chalcedonyt\RedisTagger\Commands;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem as File;
use Illuminate\View\Factory as View;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SpecificationGeneratorCommand
 *
 * @package Chalcedony\RedisTagger\Commands
 */
class KVGeneratorCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:redis_tagger:keyvalue {classname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Redis key-value tagger';
    /**
     * @var
     */
    private $view;
    /**
     * @var
     */
    private $namespace;
    /**
     * @var
     */
    private $directory;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var File
     */
    private $file;

    /**
     * @param View $view
     */
    function __construct(Config $config, View $view, File $file)
    {
        parent::__construct();
        $this->config = $config;
        $this->view = $view;
        $this->file = $file;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            // replace all space after ucwords
            $classname = preg_replace('/\s+/', '', ucwords($this->argument('classname')));


            //retrieves store directory configuration
            if( strpos($classname, '\\') !== false ){
                $class_dirs = substr($classname, 0, strrpos( $classname, '\\'));
                $directory = $this->appPath($this->config->get('redis_tagger.base_path')).DIRECTORY_SEPARATOR.'KeyValue'.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class_dirs);
                $namespace = $this->config->get('redis_tagger.namespace').'\\KeyValue\\'.$class_dirs;
                $classname = substr($classname, strrpos($classname, '\\') + 1);
            }
            else {
                $directory = $this->appPath($this->config->get('redis_tagger.base_path')).DIRECTORY_SEPARATOR.'KeyValue';
                //retrieves namespace configuration
                $namespace = $this->config->get('redis_tagger.namespace').'\\KeyValue';
            }



            is_dir($directory) ?: $this->file->makeDirectory($directory, 0755, true);

            $create = true;

            if ($this->file->exists("{$directory}/{$classname}.php")) {
                if ($usrResponse = strtolower($this->ask("The file ['{$classname}'] already exists, overwrite? [y/n]",
                    null))
                ) {
                    switch ($usrResponse) {
                        case 'y' :
                            $tempFileName = "{$directory}/{$classname}.php";

                            $prefix = '_';
                            while ($this->file->exists($tempFileName)) {
                                $prefix .= '_';
                                $tempFileName = "{$directory}/{$prefix}{$classname}.php";
                            }
                            rename("{$directory}/{$classname}.php", $tempFileName);
                            break;
                        default:
                            $this->info('No file has been created.');
                            $create = false;
                    }
                }

            }
            $args = [
                'namespace' => $namespace,
                'classname' => $classname ];

            // loading template from views
            $view = $this->view->make('redis_tagger::keyvalue',$args);


            if ($create) {
                $this->file->put("{$directory}/{$classname}.php", $view->render());
                $this->info("The class {$classname} generated successfully.");
            }


        } catch (\Exception $e) {
            $this->error('RedisTagger Key value creation failed: '.$e -> getMessage());
        }


    }

    /**
     * get application path
     *
     * @param $path
     *
     * @return string
     */
    private function appPath($path)
    {
        return base_path('/app/' . $path);
    }



}
?>
