<?php

namespace GF\Util;

/**
 * Class MetaCache.
 *
 * Caching container for wc products data, stored @ Redis.
 *
 * @package GF\Util
 */
class MetaCache
{
    const CACHE_KEY = 'Gf-meta-cache#%s#%s';

    private $cache;

    public function __construct(\GF_Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Caching product metadata in redis
     *
     * @param $postId
     * @param $postType
     * @param $metaKey
     * @param bool $custom
     * @return mixed|string
     */
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

    public function getWcProduct($productId)
    {
        $key = sprintf(self::CACHE_KEY, 'product', $productId);
        $product = $this->cache->redis->get($key);
        if ($product === false) {
            $product = wc_get_product($productId);
            $this->cache->redis->set($key, serialize($product), 30);
        } else {
            $product = unserialize($product);
        }

        return $product;
    }

    public function getWcProductsByIds($ids)
    {
        $products = [];
        foreach ($ids as $id) {
            $product = $this->getWcProduct($id);
            if (!$product) {
                var_dump($ids);
                var_dump($id);
                die();
            }
            $products[] = $this->getWcProduct($id);
        }
        return $products;
    }

    /**
     * @param int $id
     * @param string $type
     * @param string $metaKey
     * @param bool $custom true if custom meta data field
     * @return mixed|string
     */
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
                case 'thumbnail':
                    if (has_post_thumbnail($id)) {
                        return get_the_post_thumbnail($id,[150, 150]);
                    }
                    return '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="300px" height="300px" />';

                    break;

                case 'permalink':
                    $product = wc_get_product($id);
                    return $product->get_permalink();

                    break;
                case 'saleSticker':
                    $stickers = new \GfPluginsCore\ProductStickers();
                    return $stickers->addStickerToSaleProducts('',  $id);

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