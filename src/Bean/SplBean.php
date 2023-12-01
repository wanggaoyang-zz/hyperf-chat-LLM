<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace  AI\Chat\Bean;

class SplBean implements \JsonSerializable
{
    public const FILTER_NOT_NULL = 1;

    public const FILTER_NOT_EMPTY = 2; //0 不算empty

    private array $_keyMap = [];

    private array $_classMap = [];

    public function __construct(array $data = null, $autoCreateProperty = true)
    {
        $this->_keyMap = $this->setKeyMapping();
        $this->_classMap = $this->setClassMapping();
        if ($data) {
            $this->arrayToBean($data, $autoCreateProperty);
        }
        $this->initialize();
        $this->classMap();
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    final public function allProperty(): array
    {
        $data = [];
        foreach ($this as $key => $item) {
            array_push($data, $key);
        }
        $data = array_flip($data);
        unset($data['_keyMap'], $data['_classMap']);

        return array_flip($data);
    }

    public function toArray(array $columns = null, $filter = null): array
    {
        $data = $this->jsonSerialize();
        if ($columns) {
            $data = array_intersect_key($data, array_flip($columns));
        }
        if ($filter === self::FILTER_NOT_NULL) {
            return array_filter($data, function ($val) {
                return ! is_null($val);
            });
        } elseif ($filter === self::FILTER_NOT_EMPTY) {
            return array_filter($data, function ($val) {
                if ($val === 0 || $val === '0') {
                    return true;
                }
                return ! empty($val);
            });
        } elseif (is_callable($filter)) {
            return array_filter($data, $filter);
        }
        return $data;
    }

    /*
     * 返回转化后的array
     */
    public function toArrayWithMapping(array $columns = null, $filter = null)
    {
        $array = $this->toArray();
        if (! empty($this->_keyMap)) {
            foreach ($this->_keyMap as $beanKey => $dataKey) {
                if (array_key_exists($beanKey, $array)) {
                    $array[$dataKey] = $array[$beanKey];
                    unset($array[$beanKey]);
                }
            }
        }
        if ($columns) {
            $array = array_intersect_key($array, array_flip($columns));
        }
        if ($filter === self::FILTER_NOT_NULL) {
            return array_filter($array, function ($val) {
                return ! is_null($val);
            });
        } elseif ($filter === self::FILTER_NOT_EMPTY) {
            return array_filter($array, function ($val) {
                if ($val === 0 || $val === '0') {
                    return true;
                }
                return ! empty($val);
            });
        } elseif (is_callable($filter)) {
            return array_filter($array, $filter);
        }
        return $array;
    }

    final public function addProperty($name, $value = null): void
    {
        $this->{$name} = $value;
    }

    final public function getProperty($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }

    final public function jsonSerialize(): array
    {
        $data = [];
        foreach ($this as $key => $item) {
            if ($key == '_keyMap' || $key == '_classMap') {
                continue;
            }
            $data[$key] = $item;
        }
        return $data;
    }

    /*
     * 恢复到属性定义的默认值
     */
    public function restore(array $data = [], $autoCreateProperty = false)
    {
        $this->clear();
        $this->arrayToBean($data + get_class_vars(static::class), $autoCreateProperty);
        $this->initialize();
        $this->classMap();
        return $this;
    }

    /*
     * 在子类中重写该方法，可以在类初始化的时候进行一些操作
     */
    protected function initialize(): void
    {
    }

    /*
     * 如果需要用到keyMap  请在子类重构并返回对应的map数据
     * return ['dataKey'=>'beanKey']
     */
    protected function setKeyMapping(): array
    {
        return [];
    }

    /*
     * return ['property'=>class string]
     */
    protected function setClassMapping(): array
    {
        return [];
    }

    private function arrayToBean(array $data, $autoCreateProperty = false): SplBean
    {
        if ($autoCreateProperty == false) {
            $data = array_intersect_key($data, array_flip($this->allProperty()));
        }
        foreach ($data as $key => $item) {
            $this->addProperty($key, $item);
        }
        return $this;
    }

    private function clear()
    {
        $keys = $this->allProperty();
        $ref = new \ReflectionClass(static::class);
        $fields = array_keys($ref->getDefaultProperties());
        $fields = array_merge($fields, array_values($this->_keyMap));
        // 多余的key
        $extra = array_diff($keys, $fields);
        foreach ($extra as $key => $value) {
            unset($this->{$value});
        }
    }

    private function classMap()
    {
        if (! empty($this->_classMap)) {
            $propertyList = $this->allProperty();
            foreach ($this->_classMap as $property => $class) {
                if (in_array($property, $propertyList)) {
                    $val = $this->{$property};
                    $force = true;
                    if (str_contains($class, '@')) {
                        $force = false;
                        $class = substr($class, 1);
                    }
                    if (is_object($val)) {
                        if (! $val instanceof $class) {
                            throw new \Exception("value for property:{$property} dot not match in " . (static::class));
                        }
                    } elseif ($val === null) {
                        if ($force) {
                            $this->{$property} = $this->createClass($class);
                        }
                    } else {
                        $this->{$property} = $this->createClass($class, $val);
                    }
                } else {
                    throw new \Exception("property:{$property} not exist in " . (static::class));
                }
            }
        }
    }

    /**
     * @param null $arg
     * @throws \ReflectionException
     * @return object
     */
    private function createClass(string $class, $arg = null)
    {
        $ref = new \ReflectionClass($class);
        return $ref->newInstance($arg);
    }
}
