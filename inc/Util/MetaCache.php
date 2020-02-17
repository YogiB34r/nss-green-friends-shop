<?php

namespace GF\Util;


class MetaCache
{
    const CACHE_KEY = 'Gf-meta-cache#%s#%s';

    private $cache;

    public function __construct(\GF_Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getMetaFor($postId, $postType, $metaKey, $custom = false)
    {
        $container = $this->getCachedContainer($postId, $postType);
        if ($container === false) {
            $metaValue = $this->getFreshMeta($postId, $postType, $metaKey, $custom);
            $this->setCachedContainer($postId, $postType, [$metaKey => $metaValue]);
        } else if (!isset($container[$metaKey])) {
            $metaValue = $this->getFreshMeta($postId, $postType, $metaKey, $custom);
            $container[$metaKey] = $metaValue;
            $this->setCachedContainer($postId, $postType, $container);
        } else {
            $metaValue = $container[$metaKey];
        }

        return $metaValue;
    }

    private function getFreshMeta($id, $type, $metaKey, $custom = false)
    {
        if (!$custom) {
            if ($type === 'product') {
                return get_post_meta($id, $metaKey, true);
            }
        } else {
            switch ($metaKey) {
                case 'supplierName':
                    return get_user_by('ID', $this->getMetaFor($id, $type, 'supplier'))->display_name;

                    break;
            }
        }

//        var_dump('not implemented for: ' . $metaKey);
//        die();
    }

    private function setCachedContainer($postId, $postType, $data)
    {
        $key = sprintf(self::CACHE_KEY, $postId, $postType);
        if (!is_array($data)) {
            die('no arrray');
        }
        if (!$this->cache->redis->set($key, serialize($data), 60 * 30)) {
            var_dump('could not cache');
            die();
        }
    }

    private function getCachedContainer($postId, $postType)
    {
        $key = sprintf(self::CACHE_KEY, $postId, $postType);
        $data = $this->cache->redis->get($key);
        if ($data) {
            $data = unserialize($data);
        }

        return $data;
    }
}