<?php
namespace Grid\Source;

use Grid\Source\Interfaces\QuerySourceInterface;
use Grid\Source\Traits\FilterGridQuery;
use Grid\Util\Traits\GridAwareTrait;
use Grid\Source\Traits\NamespaceAwareTrait;
use Grid\Interfaces\GridInterface;
use Grid\Column\AbstractColumn;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use \Exception;

/**
 * @todo TEST it
 * @author Ivan Gospodinow <ivangospodinow@gmail.com>
 */
class DoctrineSource extends AbstractSource implements GridInterface, QuerySourceInterface
{
    use FilterGridQuery, GridAwareTrait, NamespaceAwareTrait;
    
    /**
     * PSR2 class name
     * Entity name
     * App\Entity\User
     * @var string
     */
    protected $table;
    
    /**
     * Primary key
     * @var type
     */
    protected $pk;

    /**
     *
     * @var QueryBuilder
     */
    protected $query;
    
    /**
     *
     * @var EntityManager
     */
    protected $driver;

    /**
     *
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        
        if (!$this->driver instanceof EntityManager) {
            throw new Exception('driver must be instance of EntityManager');
        }

        if (!isset($config['namespace'])) {
            throw new Exception('namespace is required');
        }

        if (!$this->table) {
            throw new Exception('table is required');
        }
    }

    /**
     *
     * @return array
     */
    public function getRows()
    {
        if (null === $this->rows) {
            $this->order();
            $this->rows = $this->getQuery()->getQuery()->getResult();
        }
        return $this->rows;
    }

    /**
     *
     * @param array $rows
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;
    }
    
    /**
     * Added order to query
     */
    public function order()
    {
        $orderFields = $this->getOrderFields();
        if (!empty($orderFields)) {
            $query = $this->getQuery();
            $query->resetDQLPart('orderBy');
            foreach ($orderFields as $field => $direction) {
                $query->addOrderBy($field, $direction);
            }
        }
    }
    /**
     *
     * @param AbstractColumn $column
     * @param string $sign
     * @param string $value
     */
    public function andWhere(AbstractColumn $column, string $sign, string $value)
    {
        $where = [];
        foreach ($column->getDbFields() as $field) {
            $where[] = sprintf(
                '%s %s %s',
                $this->getDbFieldNamespace($field),
                $sign,
                $this->driver->getConnection()->quote($value)
            );
        }
        
        $this->getQuery()->andWhere('(' . implode(' OR ', $where) . ')');
    }

    /**
     *
     * @param AbstractColumn $column
     * @param string $value
     */
    public function andLike(AbstractColumn $column, string $value)
    {
        //        @TODO
    }
    
    /**
     * dbFields [name]                = [name => name]         WHERE name LIKE selectable
     * dbFields [id, name]            = [id => name]           WHERE id = selectable
     * dbFields [id, first, last ...] = [id => first last ...] WHERE id = selectable
     *
     * @param AbstractColumn $column
     * @return array
     */
    public function getColumnValues(AbstractColumn $column) : array
    {
        return
        $this->getGrid()->filter(
            ColumnValuesInterface::class,
            'filterColumnValues',
            []
        );
    }

    /**
     *
     * @return int
     */
    public function getCount() : int
    {
        if (null === $this->count) {
            $query = clone $this->getQuery();
            if ($this->pk) {
                $expr = 'COUNT(DISTINCT ' . $this->getDbFieldNamespace($this->pk) . ')';
            } else {
                throw new Exception('pk option is required');
            }
            $query->select("$expr AS count");
            $query->setFirstResult(null);
            $query->setMaxResults(null);
            $query->resetDQLParts(['groupBy', 'orderBy']);

            $result = $query->getQuery()->getScalarResult();
            $this->setCount((int) (isset($result[0]['count']) ? $result[0]['count'] : 0));
        }
        return $this->count;
    }

    public function setCount(int $count)
    {
        $this->count = $count;
    }
    
    /**
     *
     * @return Select
     */
    public function getQuery()
    {
        if (null === $this->query) {
            $query = $this->driver->createQueryBuilder();
            $query->from($this->table, $this->namespace);
            $query->select($this->namespace);
            
            if ($this->getOffset() || $this->getLimit()) {
                $query->setMaxResults($this->getLimit());
                $query->setFirstResult($this->getOffset());
            }
            $this->setQuery(
                $this->filterGridQuery(
                    $this->getGrid(),
                    $query
                )
            );
        }
        
        return $this->query;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }
}