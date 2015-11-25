<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 * @date      11/25/15
 * @time      3:36 PM
 */
namespace Zicht\Tool\Plugin\Ssh;

use Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;
use Symfony\Component\Process\Process;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Provides some utilities related to ssh socket connection
 */
class Plugin extends BasePlugin
{
    /**
     * @{inheritDoc}
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('ssh_socket')->end()
            ->end()
        ;
    }

    public function setContainer(Container $container)
    {
        $container->fn(array('ssh','is_initialized'), function($ssh) {
            return preg_match('#^Master running (pid=\d+)$#', trim(shell_exec('ssh -S tmp.sock -O check ' . $ssh . " 2>&1 1> /dev/null")));

        });

    }
}
