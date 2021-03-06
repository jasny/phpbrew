<?php

namespace PhpBrew\BuildSettings;

use Exception;

class BuildSettings
{
    /**
     * TODO: should be renamed to enabledVariants.
     */
    public $variants = array();

    public $disabledVariants = array();

    public $extraOptions = array();

    public function __construct(array $settings = array())
    {
        if (isset($settings['enabled_variants'])) {
            $this->enableVariants($settings['enabled_variants']);
        }
        if (isset($settings['disabled_variants'])) {
            $this->disableVariants($settings['disabled_variants']);
        }
        if (isset($settings['extra_options'])) {
            $this->extraOptions = array_merge($this->extraOptions, $settings['extra_options']);
        }
    }

    public function toArray()
    {
        return array(
            'enabled_variants' => $this->variants,
            'disabled_variants' => $this->disabledVariants,
            'extra_options' => $this->extraOptions,
        );
    }

    public function enableVariants(array $settings)
    {
        foreach ($settings as $name => $value) {
            $this->enableVariant($name, $value);
        }
    }

    public function enableVariant($name, $value = null)
    {
        $this->variants[$name] = $value ?: true;
    }

    public function disableVariants(array $settings)
    {
        foreach ($settings as $name => $value) {
            $this->disableVariant($name);
        }
    }

    /**
     * Disable variant.
     *
     * @param string $name The variant name.
     */
    public function disableVariant($name)
    {
        $this->disabledVariants[$name] = true;
    }

    /**
     * Remove the enabled the variants since we've disabled
     * them.
     */
    public function resolveVariants()
    {
        $removed = array();
        foreach ($this->disabledVariants as $n => $true) {
            if ($this->hasVariant($n)) {
                $this->removeVariant($n);
                $removed[] = $n;
            }
        }

        return $removed;
    }

    public function isDisabledVariant($name)
    {
        return isset($this->disabledVariants[$name]);
    }

    public function isEnabledVariant($name)
    {
        return isset($this->variants[$name]);
    }

    /**
     * Check if we've enabled the variant.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasVariant($name)
    {
        return isset($this->variants[$name]);
    }

    /**
     * Remove enabled variant.
     */
    public function removeVariant($variantName)
    {
        unset($this->variants[$variantName]);
    }

    /**
     * Get enabled variants.
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * Get all disabled variants.
     */
    public function getDisabledVariants()
    {
        return $this->disabledVariants;
    }

    /**
     * Returns variant user value.
     *
     * @param string $n variant name
     *
     * @return string variant value
     */
    public function getVariant($n)
    {
        if (isset($this->variants[$n])) {
            return $this->variants[$n];
        }

        return;
    }

    public function getExtraOptions()
    {
        return $this->extraOptions;
    }

    public function grepExtraOptionsByPattern($pattern)
    {
        // preg_grep is available since PHP4
        return preg_grep($pattern, $this->extraOptions);
    }

    /**
     * Load and return the variant info from file.
     */
    public function loadVariantInfoFile($variantFile)
    {
        if (!is_readable($variantFile)) {
            throw new Exception(
                "Can't load variant info! Variants file {$variantFile} is not readable."
            );
        }
        $variantInfo = unserialize(file_get_contents($variantFile));

        return $this->loadVariantInfo($variantInfo);
    }

    public function writeVariantInfoFile($variantInfoFile)
    {
        return file_put_contents($variantInfoFile, serialize(array(
            'enabled_variants' => $this->variants,
            'disabled_variants' => $this->disabledVariants,
            'extra_options' => array_unique($this->extraOptions),
        )));
    }

    public function loadVariantInfo(array $variantInfo, $reset = false)
    {
        if ($reset) {
            $this->variants = array();
            $this->disabledVariants = array();
            $this->extraOptions = array();
        }
        if (isset($variantInfo['enabled_variants'])) {
            $this->enableVariants($variantInfo['enabled_variants']);
        }
        if (isset($variantInfo['disabled_variants'])) {
            $this->disableVariants($variantInfo['disabled_variants']);
        }
        if (isset($variantInfo['extra_options'])) {
            $this->extraOptions = array_unique(array_merge($this->extraOptions, $variantInfo['extra_options']));
        }

        return $this->resolveVariants(); // Remove the enabled variants
    }
}
