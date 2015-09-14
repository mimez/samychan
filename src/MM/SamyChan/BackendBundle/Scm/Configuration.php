<?php

namespace MM\SamyChan\BackendBundle\Scm;

use Symfony\Component\Yaml;

class Configuration {

    /**
     * @var array
     */
    protected $config;

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Constructor
     * @param $kernel
     */
    public function __construct($kernel)
    {
        $yaml = new Yaml\Parser();
        $value = $yaml->parse(file_get_contents($kernel->locateResource('@MMSamyChanBackendBundle/Resources/config/channellists/config.yml')));

        foreach ($value['scm_config']['series'] as $series => $seriesData) {
            if (!is_array($seriesData['files'])) {
                continue;
            }
            foreach ($seriesData['files'] as $fileName => $fileConfigReference) {
                $fileYaml = $yaml->parse(file_get_contents($kernel->locateResource('@MMSamyChanBackendBundle/Resources/config/channellists/' . $fileConfigReference . '.yml')));
                $value['scm_config']['series'][$series][$fileName] = $fileYaml[$fileConfigReference];
            }
        }

        $this->setConfig($value['scm_config']);
    }

    /**
     * config by series
     *
     * @param $series
     * @return mixed
     * @throws \Exception
     */
    public function getConfigBySeries($series) {
        $config = $this->getConfig();

        if (!array_key_exists($series, $config['series'])) {
            throw new \Exception(sprintf('requested config for series=(%s) does not exist', $series));
        }

        return $config['series'][$series];
    }

    /**
     * Return all supported Series
     *
     * @return array
     */
    public function getSupportedSeries()
    {
        $config = $this->getConfig();

        return array_keys($config['series']);
    }
}