<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductRedirect\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Type
    extends AbstractSource
{
    public const TYPE_NONE = 0;
    public const TYPE_INTERNAL = 1;
    public const TYPE_PERMANENT = 301;
    public const TYPE_TEMPORARY = 302;

    public function getAllOptions(): array
    {
        return [
            ['label' => __('None'), 'value' => self::TYPE_NONE],
            ['label' => __('Internal'), 'value' => self::TYPE_INTERNAL],
            ['label' => __('Permanent'), 'value' => self::TYPE_PERMANENT],
            ['label' => __('Temporary'), 'value' => self::TYPE_TEMPORARY],
        ];
    }

    public function toOptions(): array
    {
        return [
            self::TYPE_NONE      => __('None'),
            self::TYPE_INTERNAL  => __('Internal'),
            self::TYPE_PERMANENT => __('Permanent'),
            self::TYPE_TEMPORARY => __('Temporary')
        ];
    }
}
