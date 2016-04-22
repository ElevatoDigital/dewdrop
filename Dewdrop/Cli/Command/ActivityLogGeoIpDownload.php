<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Exception;
use Dewdrop\Paths;
use Dewdrop\Pimple;

class ActivityLogGeoIpDownload extends CommandAbstract
{
    /**
     * @var string
     */
    private $dbUrl = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-%s.mmdb.gz';

    /**
     * @var string
     */
    private $dbMd5Url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-%s.md5';

    /**
     * @var string
     */
    private $granularity = 'City';

    public function init()
    {
        $this
            ->setCommand('activity-log-geo-ip-download')
            ->addAlias('activity-log-geoip-download')
            ->addAlias('activity-log-geo-ip-update')
            ->addAlias('activity-log-geoip-update')
            ->setDescription("Download this month's update to the free geo IP database from MaxMind.");

        $this->addArg(
            'granularity',
            'Whether you prefer "Country" or "City" level detail for geo IP results.',
            self::ARG_OPTIONAL
        );
    }

    public function setGranularity($granularity)
    {
        $granularity = ucfirst($granularity);

        if (!in_array($granularity, ['City', 'Country'])) {
            throw new Exception('Granularity must be either "City" or "Country".');
        }

        $this->granularity = $granularity;

        return $this;
    }

    public function execute()
    {
        /* @var $paths Paths */
        $paths = Pimple::getResource('paths');
        $path  = $paths->getData() . '/activity-log';

        if (!file_exists($path) || !is_dir($path)) {
            mkdir($path, 0777);
        }

        $filename  = 'GeoLite2.mmdb';
        $gzPath    = $path . '/' . $filename . '.gz';
        $tmpPath   = $path . '/' . $filename . '.tmp';
        $finalPath = $path . '/' . $filename;

        file_put_contents($gzPath, file_get_contents($this->getDbUrl()), LOCK_EX);
        $this->decompressFile($gzPath, $tmpPath);

        if ($this->computedMd5MatchesPublishedMd5($tmpPath)) {
            rename($tmpPath, $finalPath);
            unlink($gzPath);
        } else {
            if (file_exists($gzPath)) {
                unlink($gzPath);
            }

            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }

            throw new Exception('Downloaded file does not match MD5 published by MaxMind.');
        }
    }

    private function decompressFile($gzPath, $tmpPath)
    {
        $gz  = gzopen($gzPath, 'r');
        $tmp = fopen($tmpPath, 'w');

        while (!feof($gz)) {
            fwrite($tmp, fread($gz, 1024));
        }

        fclose($gz);
        fclose($tmp);
    }

    private function computedMd5MatchesPublishedMd5($file)
    {
        $computedMd5  = md5(file_get_contents($file));
        $publishedMd5 = trim(file_get_contents($this->getDbMd5Url()));
        return $computedMd5 === $publishedMd5;
    }

    private function getDbUrl()
    {
        return sprintf($this->dbUrl, $this->granularity);
    }

    private function getDbMd5Url()
    {
        return sprintf($this->dbMd5Url, $this->granularity);
    }
}
