<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 * @date      11/25/15
 * @time      3:36 PM
 */
namespace Zicht\Tool\Plugin\Tunnel;

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
                ->arrayNode('tunnel')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('socket')->end()
                        ->arrayNode('options')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function setContainer(Container $container)
    {
        $container->fn(array('tunnel','is_acitve'), function(Container $c, $ssh, $socket) {
            return preg_match(
                '#^Master running (pid=\d+)$#',
                trim(shell_exec(sprintf('%s 2>&1 1> /dev/null', $c->call($c->resolve(['tunnel','cmd','check']), $socket, $ssh))))
            );
        }, true);
        $container->decl(['tunnel','get','options'], function(Container $c) {
            if (!empty($c->resolve(['tunnel', 'options']))) {
                foreach ($c->resolve(['tunnel', 'options']) as $name => $value) {
                    $options[] =  sprintf('-o "%s %s"', $name, $value);
                }
                return !empty($options) ? implode(" ", $options) : null;
            } else {
                return null;
            }
        });
        $container->decl(['tunnel','get','socket'], function(Container $c) {

            if (false !== $c->has(['tunnel','socket']) && !empty($c->resolve(['tunnel', 'socket']))) {
                return $c->resolve(['tunnel','socket']);
            } else {
                $folder = sprintf('%s/%s', sys_get_temp_dir(), sha1(getenv('USER')));
                if (false === is_dir($folder)) {
                    mkdir($folder, 0700, true);
                }
                return sprintf('%s/%s.socket', $folder, $c->resolve('envs')[$c->resolve('target_env')]['ssh']);
            }
        });
        $container->fn(['tunnel','cmd','check'], function($socket, $ssh) {
           return sprintf('ssh -S %s -O check %s', $socket, $ssh);
        });
    }
}