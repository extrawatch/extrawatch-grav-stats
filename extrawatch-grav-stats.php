<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;

/**
 * Class ExtrawatchGravStatsPlugin
 * @package Grav\Plugin
 */
class ExtrawatchGravStatsPlugin extends Plugin
{

    const SNIPPET = "var _extraWatchParams = _extraWatchParams || [];
    _extraWatchParams.projectId = '%s';
    (function() {
        var ew = document.createElement('script'); ew.type = 'text/javascript'; ew.async = true;
        ew.src = 'https://agent.extrawatch.com/agent/js/ew.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ew, s);
    })();";
    const PROJECT_ID_FIELD_NAME = "projectId";


    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->enable([
                'onAdminTwigTemplatePaths' => [['onAdminTwigTemplatePaths', 10]],
                'onAdminMenu' => [['onAdminMenu', 0]]
            ]);
            return;
        }
        // Enable the main events we are interested in
        $this->enable([
            'onPageInitialized' => [['onPageInitialized', 10]],
        ]);
    }

    public function onAdminMenu() {
        $this->grav['twig']->plugins_hooked_nav['PLUGIN_EXTRAWATCH.PAGE.TITLE'] = [
            'route' => '/extrawatch', 'icon' => 'fa-bar-chart'
        ];
    }

    public function onAdminTwigTemplatePaths($event) {
        $paths[] = __DIR__ . '/admin/templates';
        $event['paths'] = $paths;

    }

    public function onPageInitialized() {
        $this->addHeaderWithExtraWatchProjectId();
    }


    public function addHeaderWithExtraWatchProjectId() {
        $config = $this->grav['config'];
        $extraWatchPlugin = $config->plugins['extrawatch-grav-stats'];
        if ($extraWatchPlugin) {
            $projectId = $extraWatchPlugin[self::PROJECT_ID_FIELD_NAME];
            if ($projectId) {
                $assets = $this->grav['assets'];
                $assets->addInlineJs(sprintf(ExtrawatchGravStatsPlugin::SNIPPET, $projectId));
            }
        }
    }

}
