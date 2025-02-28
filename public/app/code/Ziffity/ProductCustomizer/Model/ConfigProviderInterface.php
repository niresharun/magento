<?php

namespace Ziffity\ProductCustomizer\Model;

/**
 * Interface ConfigProviderInterface
 */
interface ConfigProviderInterface
{
    /**
     * Retrieve assoc array of customframe configuration
     *
     * @return array
     */
    public function getConfig();
}
