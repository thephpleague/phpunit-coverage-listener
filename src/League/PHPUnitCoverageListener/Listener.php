<?php namespace League\PHPUnitCoverageListener;

use League\PHPUnitCoverageListener\ListenerInterface;
use League\PHPUnitCoverageListener\PrinterInterface;
use League\PHPUnitCoverageListener\HookInterface;
use League\PHPUnitCoverageListener\Collection;
use \SimpleXMLElement;

/**
 * Main PHPUnit listener class
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class Listener implements ListenerInterface
{
	/**
	 * @var PrinterInterface
	 */
	protected $printer;

	/**
	 * @var HookInterface
	 */
	protected $hook;

    /**
     * Listener constructor
     *
     * @param array Argument that sent from phpunit.xml
     * @param bool Boot flag 
     */
    public function __construct($args = array(), $boot = true)
    {
        $this->ensurePrinter($args);

    	$this->printer = $args['printer'];

    	// @coverageIgnoreStart
        if ($boot) {
	    	$listener = $this;

	        // Register the method to collect code-coverage information
	        register_shutdown_function(function() use ($args, $listener) {
	            $listener->collectAndSendCoverage($args);
	        });
        }
    	// @coverageIgnoreEnd
    }

    /**
     * Printer getter
     *
     * @return PrinterInterface
     */
    public function getPrinter()
    {
    	return $this->printer;
    }

    /**
     * Main api for collecting code-coverage information
     *
     * @param array Contains repo secret hash, target url, coverage directory and optional Namespace
     */
    public function collectAndSendCoverage($args)
    {
        // Starting point!
        $this->printer->out("\n\n".'Collecting CodeCoverage information...');

        if (array_key_exists('repo_token', $args) 
            && array_key_exists('target_url', $args)
            && array_key_exists('coverage_dir', $args)
            && array_key_exists('namespace', $args)) {
            extract($args);

            // Check for exist and valid hook
            if (isset($hook) && $hook instanceof HookInterface) {
            	$this->hook = $hook;
            	unset($hook);
            }

            // Get the realpath coverage directory
            $coverage_dir = realpath($coverage_dir);
            $coverage_file = $coverage_dir.DIRECTORY_SEPARATOR.self::COVERAGE_FILE;
            $coverage_output = $coverage_dir.DIRECTORY_SEPARATOR.self::COVERAGE_OUTPUT;

            // Get the coverage information
            if (is_dir($coverage_dir) && is_file($coverage_file)) {
            	// Build the coverage xml object
		        $xml = file_get_contents($coverage_file);
		        $coverage = new SimpleXMLElement($xml);

                // Prepare the coveralls payload
                $data = $this->collect($coverage, $args);

                // Write the coverage output
                $this->printer->out('Writing coverage output...');
                file_put_contents($coverage_output, json_encode($data->all(), JSON_NUMERIC_CHECK));

                // Send it!
                $this->printer->out('Sending coverage output...');
                $payload = array('json_file'=>'@'.$coverage_output); 
                $ch = curl_init(); 
                curl_setopt($ch, CURLOPT_URL, $target_url); 
                curl_setopt($ch, CURLOPT_POST,1); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

                // Save output into output buffer
                ob_start();
                $result = curl_exec ($ch); 
                $curlOutput = ob_get_contents();
                ob_end_clean();

                curl_close ($ch); 
                $this->printer->printOut('cURL Output:'.$curlOutput); 
                $this->printer->printOut('cURL Result:'.$result);
            }
        }

        $this->printer->out('Done.');
    }

    /**
     * Printer validator
     *
     * @param array
     */
    protected function ensurePrinter($args)
    {
        if ( ! isset($args['printer'])) {
            throw new \RuntimeException('Printer class not found');
        }


        if ( ! $args['printer'] instanceof PrinterInterface) {
            throw new \RuntimeException('Invalid printer class');
        }
    }

    /**
     * Main collector method
     *
     * @param SimpleXMLElement Coverage report from PHPUnit
     * @param array
     * @return Collection
     */
    protected function collect(SimpleXMLElement $coverage, $args = array())
    {
    	extract($args);

    	$data = new Collection(array(
            'repo_token' => $repo_token,
            'source_files' => array(),
        ));

 		// Before collect hook
     	if ( ! empty($this->hook)) {
     		$data = $this->hook->beforeCollect($data);
     	}

        // Prepare temporary source_files holder
        $sourceArray = new Collection();

        if (count($coverage->project->package) > 0) {
            // Iterate over the package
            foreach ($coverage->project->package as $package) {
                // Then itterate on each package file
                foreach ($package->file as $packageFile) {
                    $this->printer->printOut('Checking:'.$packageFile['name']);

                    $sourceArray->add(array(
                        md5($packageFile['name']) => $this->collectFromFile($packageFile, $namespace)
                    ));
                }
            }
        }

        if (count($coverage->project->file) > 0) {
            // itterate over the files
            foreach ($coverage->project->file as $file) {
                $this->printer->printOut('Checking:'.$file['name']);

                $sourceArray->add(array(
                    md5($file['name']) => $this->collectFromFile($file, $namespace)
                ));
            }
        }

        // Last, pass the source information it it contains any information
        if ($sourceArray->count() > 0) {
            $data->set('source_files', array_values($sourceArray->all()));
        }

 		// After collect hook
        if ( ! empty($this->hook)) {
     		$data = $this->hook->afterCollect($data);
     	}

     	return $data;
    }

    /**
     * Collect code-coverage information from a file
     *
     * @param SimpleXMLElement contains coverage information
     * @param string Optional file namespace identifier
     * @return array contains code-coverage data with keys as follow : name, source, coverage
     */
    protected function collectFromFile(SimpleXMLElement $file, $namespace = '')
    {
        // Get current dir
        $currentDir = (isset($_SERVER['PWD'])) ? realpath($_SERVER['PWD']) : getcwd();

        // Initial return values
        $name = '';
        $source = '';
        $coverage = array();

        // #1 Get the relative file name
        $pathComponents = explode($currentDir, $file['name']);
        $relativeName = count($pathComponents) == 2 ? $pathComponents[1] : current($pathComponents);

        if (empty($namespace)) {
            $name = trim($relativeName, DIRECTORY_SEPARATOR);
        } else {
            // Replace backslash with directory separator
            $ns = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            list($path, $namespacedName) = explode($ns, $relativeName);

            $name = $ns.DIRECTORY_SEPARATOR.trim($namespacedName, DIRECTORY_SEPARATOR);
        }

        // #2 Build coverage data and the source code
        $count = 0;
        $handle = fopen($file['name'], "r");
        while(!feof($handle)){
            $source .= fgets($handle);
            $count++;
        }

        fclose($handle);

        // Here we build the default coverage values
        $coverage = array_fill(0, $count, null);

        // Then, we will overwrite any coverage block into it!
        if (count($file->line) > 0) {
            foreach ($file->line as $line) {
                $attributes = current($line->attributes());

                // Only stmt would be count
                if (isset($attributes['type']) 
                    && isset($attributes['count']) 
                    && $attributes['type'] === 'stmt') {

                    // Decrease the line number by one
                    // since key 0 (within coverage array) is actually line number 1
                    $num = (int) $attributes['num'] - 1;

                    // Ensure it match count boundaries
                    if ($num > 0 && $num <= $count) {
                        $coverage[$num] = (int) $attributes['count'];
                    }
                }
            }
        }

        return compact('name', 'source', 'coverage');
    }
}