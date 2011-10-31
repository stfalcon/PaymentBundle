<?php

namespace Stfalcon\Bundle\PaymentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stfalcon_payment', 'array');

        $rootNode
            ->children()
                ->arrayNode('interkassa')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('shop_id')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('secret_key')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('submit_url')
//                            ->isRequired()
//                            ->cannotBeEmpty()
                            ->defaultValue('http://www.interkassa.com/lib/payment.php')
                        ->end();

        return $treeBuilder;
    }
}
