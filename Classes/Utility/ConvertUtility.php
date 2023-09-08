<?php

namespace Faktore\FeJsonApiUtilities\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\Exception\UnknownClassException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class ConvertUtility
{
    /**
     * Flattens a file storage into a file array
     *
     * properties are an optional parameter,
     * but every file will always yield the key 'publicUrl'
     *
     * ```
     * ConvertUtility::flattenObjectStorage($obj->getMyFalStorageProperty(), ['title', 'description', 'alternative']);
     * ```
     *
     * @param mixed $fileStorage
     * @param array $propertiesToInclude
     * @return array
     */
    public static function flattenFileStorage(mixed $fileStorage, array $propertiesToInclude = []): array
    {
        $distilledImages = [];
        if (self::isStorage($fileStorage)) {
            foreach ($fileStorage as $image) {
                if ($originalResource = $image->getOriginalResource()) {
                    $props = $originalResource->getProperties();
                    $distilledImageProperties['publicUrl'] = $originalResource->getPublicUrl();
                    foreach ($propertiesToInclude as $k => $v) {
                        $distilledImageProperties[$v] = $props[$v] ?? '';
                    }
                    $distilledImages[] = $distilledImageProperties;
                }
            }
        }
        return $distilledImages;
    }

    /**
     * converts a storage and properties inside into an array
     *
     * @param mixed $objectStorage
     * @param array $propertiesToInclude
     * @return array
     * @throws PropertyNotAccessibleException
     * @throws UnknownClassException
     */
    public static function flattenObjectStorage(mixed $objectStorage, array $propertiesToInclude = []): array
    {
        $objectArray = [];
        if (self::isStorage($objectStorage)) {
            $objectArray = self::forceArray($objectStorage);
        }
        $result = [];
        foreach ($objectArray as $obj) {
            $result[] = self::objectToArray($obj, $propertiesToInclude);
        }
        return $result;
    }

    /**
     * Checks if an object is a storage object.
     * ObjectStorage or LazyObjectStorage
     * ```
     * ConvertUtility->isStorage( $obj );
     * ```
     * @return boolean
     */
    public static function isStorage($obj)
    {
        if (!is_object($obj) || is_string($obj)) return false;
        $type = get_class($obj);
        return is_a($obj, ObjectStorage::class) || $type == LazyObjectStorage::class || $type == ObjectStorage::class || $type == \TYPO3\CMS\Extbase\Persistence\ObjectStorage::class;
    }

    /**
     * Converts an object storage into an array
     * Single objects get wrapped into a one-key array
     *
     * @param mixed $obj
     *
     * @return array
     */
    public static function forceArray(mixed $obj): array
    {
        if (!$obj) return [];
        if (self::isStorage($obj)) {
            $tmp = [];
            foreach ($obj as $k => $v) {
                $tmp[] = $v;
            }
            return $tmp;
        }
        return is_array($obj) ? $obj : [$obj];
    }

    /**
     * Converts an object to array by iterating over all its properties,
     * or (optional and recommended): over a given list of properties
     *
     * @param mixed $obj
     * @param array $fields
     * @return array
     * @throws PropertyNotAccessibleException
     * @throws UnknownClassException
     */
    public static function objectToArray(mixed $obj, array $fields = []): array
    {
        $final = [];

        $keys = $fields ?: self::getKeys($obj);

        foreach ($keys as $field) {
            $val = self::prop($obj, $field);
            $final[$field] = $val;
        }

        return $final;
    }

    /**
     * Access all keys of a model
     *
     * ```
     * ConvertUtility::getKeys( $model );                                    // ['uid', 'title', 'text', ...]
     * ConvertUtility::getKeys( \MyExt\Domain\Model\Demo::class );        // ['uid', 'title', 'text', ...]
     * ```
     *
     * @param mixed $obj model, array or class name
     * @return array
     * @throws UnknownClassException
     */
    public static function getKeys($obj)
    {
        if (is_string($obj) && class_exists($obj)) {
            $obj = new $obj();
        }
        $keys = [];
        if (is_object($obj)) {
            return ObjectAccess::getGettablePropertyNames($obj);
        } else if (is_array($obj)) {
            return array_keys($obj);
        }
        return [];
    }

    /**
     * Access a key inside an object or array
     * Key may be a path: "img.0.uid"
     *
     * ```
     * ConvertUtility::prop( $obj, 'img.0.uid' );
     * ```
     *
     * @param mixed $obj model/array
     * @param string $key key to be retrieved
     *
     * @return mixed
     * @throws PropertyNotAccessibleException
     */
    public static function prop(mixed $obj, string $key): mixed
    {
        if ($key == '') return '';
        $key = explode('.', $key);
        if (count($key) == 1) return self::accessSingleProperty($obj, $key[0]);

        foreach ($key as $k) {
            $obj = self::accessSingleProperty($obj, $k);
            if (!$obj) return '';
        }
        return $obj;
    }

    /**
     * Accessing a single property of a model or object
     * Key must be a single key, no path
     *
     * ```
     * ConvertUtility::accessSingleProperty( $obj, 'uid' );
     * ```
     *
     * @param mixed $obj model or array
     * @param string $key the required key
     *
     * @return mixed
     * @throws PropertyNotAccessibleException
     */
    public static function accessSingleProperty(mixed $obj, string $key): mixed
    {
        if ($key == '') return '';

        if (is_object($obj)) {

            if (is_numeric($key)) {
                $obj = self::forceArray($obj);
                return $obj[intval($key)];
            }

            $gettable = ObjectAccess::isPropertyGettable($obj, $key);
            if ($gettable) return ObjectAccess::getProperty($obj, $key);

            $camelCaseKey = GeneralUtility::underscoredToLowerCamelCase($key);
            $gettable = ObjectAccess::isPropertyGettable($obj, $camelCaseKey);
            if ($gettable) return ObjectAccess::getProperty($obj, $camelCaseKey);

            return $obj->$key;

        } else {
            if (is_array($obj)) return $obj[$key];
        }
        return [];
    }
}