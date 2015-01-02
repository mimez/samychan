<?php

namespace MM\SamyEditorBundle\Scm;

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
        $value = $yaml->parse(file_get_contents($kernel->locateResource('@MMSamyEditorBundle/Resources/config/channel_format.yml')));

        foreach ($value['scm_config']['series'] as $series => $files) {
            foreach ($files as $fileName => $fileConfigReference) {
                $value['scm_config']['series'][$series][$fileName] = $value['scm_config']['file_formats'][$fileConfigReference];
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

        if (!isset($config['series'][$series])) {
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