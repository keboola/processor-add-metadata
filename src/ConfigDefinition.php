<?php

declare(strict_types=1);

namespace Keboola\AddMetadataProcessor;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
                ->arrayNode('tables')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('table')->isRequired()->end()
                            ->arrayNode('metadata')
                                ->requiresAtLeastOneElement()
                                ->isRequired()
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('key')->isRequired()->end()
                                        ->scalarNode('value')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        // @formatter:on
        return $parametersNode;
    }
}
