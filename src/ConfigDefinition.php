<?php

declare(strict_types=1);

namespace MyComponent;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigDefinition extends BaseConfigDefinition
{
    public const METADATA_KEY = 'bdm.scaffold.table.tag';

    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
                ->scalarNode('vendor')->isRequired()->end()
                ->scalarNode('app')->isRequired()->end()
                ->scalarNode('metadata_key')
                    ->defaultValue(self::METADATA_KEY)
                ->end()
                ->arrayNode('tables')->scalarPrototype()->end()
            ->end()
        ;
        // @formatter:on
        return $parametersNode;
    }
}
