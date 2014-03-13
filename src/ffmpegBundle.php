<?php

use Doctrine\Common\Cache\ArrayCache;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;

class ffmpegBundle 
{
	public static function register($core)
	{

		$app = $core->getServiceContainer();

	    $app['ffmpeg.configuration'] = array();
        $app['ffmpeg.default.configuration'] = array(
            'ffmpeg.threads'   => 4,
            'ffmpeg.timeout'   => 300,
            'ffmpeg.binaries'  => array('avconv', 'ffmpeg'),
            'ffprobe.timeout'  => 30,
            'ffprobe.binaries' => array('avprobe', 'ffprobe'),
        );
        $app['ffmpeg.logger'] = null;

        $app['ffmpeg.configuration.build'] = $app->share(function (Application $app) {
            return array_replace($app['ffmpeg.default.configuration'], $app['ffmpeg.configuration']);
        });

        $app['ffmpeg'] = $app['ffmpeg.ffmpeg'] = $app->share(function (Application $app) {
            $configuration = $app['ffmpeg.configuration.build'];

            if (isset($configuration['ffmpeg.timeout'])) {
                $configuration['timeout'] = $configuration['ffmpeg.timeout'];
            }

            return FFMpeg::create($configuration, $app['ffmpeg.logger'], $app['ffmpeg.ffprobe']);
        });

        $app['ffprobe.cache'] = $app->share(function () {
            return new ArrayCache();
        });

        $app['ffmpeg.ffprobe'] = $app->share(function (Application $app) {
            $configuration = $app['ffmpeg.configuration.build'];

            if (isset($configuration['ffmpeg.timeout'])) {
                $configuration['timeout'] = $configuration['ffprobe.timeout'];
            }

            return FFProbe::create($configuration, $app['ffmpeg.logger'], $app['ffprobe.cache']);
        });
	}
}