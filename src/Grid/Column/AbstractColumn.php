<?php
namespace Grid\Column;

use Grid\Util\Extractor\AbstractExtractor;
use Grid\Util\Traits\ExchangeArray;
use Grid\Util\Traits\Attributes;
use Grid\Util\Traits\Required;

use Grid\Util\Traits\GridAwareTrait;
use Grid\Interfaces\GridInterface;

use \Exception;

/**
 * Every column must extend this class
 * More features coming
 *
 * @author Ivan Gospodinow <ivangospodinow@gmail.com>
 */
abstract class AbstractColumn implements GridInterface
{
    use ExchangeArray, Attributes, GridAwareTrait, Required;

    const SEARCHABLE_STRATEGY_EQ   = '=';
    const SEARCHABLE_STRATEGY_LIKE = 'like';

    /**
     * Unique identifier within the grid
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $preLabel = '';

    /**
     *
     * @var string
     */
    protected $label = '';

    /**
     *
     * @var string
     */
    protected $postLabel = '';
    
    /**
     *
     * @var type
     */
    protected $labelIsTranslated = false;

    /**
     * Gets column value from array or object
     * @var type
     */
    protected $extract;
    
    /**
     * Called based on extract and row value
     * @var AbstractExtractor
     */
    protected $extractor;

    /**
     * Class that implements DataTypeInterface
     * @var type 
     */
    protected $dataType;

     /**
     * Format to add to dataType
     * @example Y-m-d for date
     * @var type
     */
    protected $format;

    /**
     * Corresponding field or fields for this column
     * For example User full name = user.name + user.lastName;
     * @example [user.name, user.lastName]
     * @var array
     */
    protected $dbFields;

    /**
     * If the column can be sortable
     * @var type 
     */
    protected $sortable = false;

    /**
     *
     * @var type
     */
    protected $searchable = false;

    /**
     *
     * @var type
     */
    protected $selectable = false;

    /**
     *
     * @var callback, array
     */
    protected $selectableSource;

    /**
     *
     * @var type
     */
    protected $exportable = true;

    /**
     *
     * @var string
     */
    protected $searchableStrategy = self::SEARCHABLE_STRATEGY_LIKE;
    
    /**
     *
     * @param array $config [name, label, extract, dbFields]
     */
    public function __construct(array $config)
    {
        $this->required('name', $config, $this);
        
        $config['extract']  = $config['extract'] ?? $config['name'];

        if (isset($config['dbFields']) && is_array($config['dbFields'])) {
            $config['dbFields'] = $config['dbFields'];
        } elseif(isset($config['dbFields']) && is_string($config['dbFields'])) {
            $config['dbFields'] = [$config['dbFields']];
        } else {
            $config['dbFields'] = [];
        }
        
        $this->exchangeArray($config);
    }

    /**
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     *
     * @return type
     */
    public function getLabel()
    {
        if (!$this->labelIsTranslated) {
            $this->labelIsTranslated = true;
            $this->label = $this->getGrid()->translate($this->label);
        }

        return $this->label;
    }

    /**
     *
     * @param string $label
     * @return \self
     */
    public function setLabel(string $label) : self
    {
        $this->label = $label;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getExtract()
    {
        return $this->extract;
    }

    /**
     *
     * @return string
     */
    public function getPreLabel() : string
    {
        return $this->preLabel;
    }

    /**
     *
     * @param string $string
     */
    public function setPreLabel(string $string)
    {
        $this->preLabel = $string;
    }

    /**
     *
     * @return string
     */
    public function getPostLabel() : string
    {
        return $this->postLabel;
    }

    /**
     *
     * @param string $string
     */
    public function setPostLabel(string $string)
    {
        $this->postLabel = $string;
    }

    /**
     *
     * @return array
     */
    public function getDbFields() : array
    {
        return $this->dbFields;
    }

   /**
     *
     * @return array
     */
    public function setDbFields(array $dbFields)
    {
        $this->dbFields = $dbFields;
    }

    /**
     *
     * @return bool
     */
    public function hasDbFields() : bool
    {
        return !empty($this->dbFields);
    }

    /**
     *
     * @return string
     */
    public function getDataType() : string
    {
        return $this->dataType;
    }

    /**
     *
     * @param string $class
     */
    public function setDataType(string $class)
    {
        $this->dataType = $class;
    }

    /**
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     *
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     *
     * @return bool
     */
    public function hasDataType() : bool
    {
        return $this->dataType && class_exists($this->dataType);
    }

    /**
     *
     * @return bool
     */
    public function isSortable() : bool
    {
        return $this->hasDbFields() && $this->sortable;
    }

    /**
     *
     * @return bool
     */
    public function isSearchable() : bool
    {
        return $this->hasDbFields() && $this->searchable;
    }
    
    /**
     *
     * @return bool
     */
    public function isSelectable() : bool
    {
        return ($this->hasDbFields() || !empty($this->getSelectableSource()))
            && $this->selectable;
    }

    /**
     * @return callback, array[key => val]
     */
    public function getSelectableSource()
    {
        return $this->selectableSource;
    }

    /**
     *
     * @param array $mixed $callback or [class, method]
     */
    public function setSelectableSource($mixed)
    {
        if (is_callable($mixed) || is_array($mixed)) {
            $this->selectableSource = $mixed;
        } else {
            throw new Exception('Invalid selectable source');
        }
    }
    
    /**
     *
     * @return bool
     */
    public function isExportable() : bool
    {
        return (bool) $this->exportable;
    }

    /**
     *
     * @return AbstractExtractor
     */
    public function getExtractor($source = null) : AbstractExtractor
    {
        if (null === $this->extractor) {
            if (null === $source) {
                throw new Exception('Source must have value');
            }
            $this->setExtractor(AbstractExtractor::factory($source));
        }
        return $this->extractor;
    }

    /**
     *
     * @param AbstractExtractor $extractor
     * @return \self
     */
    public function setExtractor(AbstractExtractor $extractor) : self
    {
        $this->extractor = $extractor;
        return $this;
    }
}
