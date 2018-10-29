<?php

/**
 * @TODO Some of these must be refactored and optimized
 */


function gf_insert_in_array_by_index($array, $index, $val) {
    $size = count($array); //because I am going to use this more than one time
    if (!is_int($index) || $index < 0 || $index > $size) {
        return -1;
    } else {
        $temp = array_slice($array, 0, $index);
        $temp[] = $val;
        return array_merge($temp, array_slice($array, $index, $size));
    }
}

//Testira razliku array-a order sensitive
function gf_array_reccursive_difrence(array $array1, array $array2, array $_ = null) {
    $diff = [];
    $args = array_slice(func_get_args(), 1);
    foreach ($array1 as $key => $value) {
        foreach ($args as $item) {
            if (is_array($item)) {
                if (array_key_exists($key, $item)) {
                    if (is_array($value) && is_array($item[$key])) {
                        $tmpDiff = gf_array_reccursive_difrence($value, $item[$key]);

                        if (!empty($tmpDiff)) {
                            foreach ($tmpDiff as $tmpKey => $tmpValue) {
                                if (isset($item[$key][$tmpKey])) {
                                    if (is_array($value[$tmpKey]) && is_array($item[$key][$tmpKey])) {
                                        $newDiff = array_diff($value[$tmpKey], $item[$key][$tmpKey]);
                                    } else if ($value[$tmpKey] !== $item[$key][$tmpKey]) {
                                        $newDiff = $value[$tmpKey];
                                    }

                                    if (isset($newDiff)) {
                                        $diff[$key][$tmpKey] = $newDiff;
                                    }
                                } else {
                                    $diff[$key][$tmpKey] = $tmpDiff;
                                }
                            }
                        }
                    } else if ($value !== $item[$key]) {
                        $diff[$key] = $value;

                    }
                } else {
                    $diff[$key] = $value;
                }
            }
        }
    }
    return $diff;
}

function gf_get_categories($exlcude = array()) {
    $args = array(
        'orderby' => 'name',
        'order' => 'asc',
        'hide_empty' => false,
        'exclude' => $exlcude,
    );
    $product_cats = get_terms('product_cat', $args);
    return $product_cats;
}

function gf_get_top_level_categories($exclude = array()) {
    $top_level_categories = [];
    foreach (gf_get_categories($exclude) as $category) {
        if (!$category->parent) {
            $top_level_categories[] = $category;
        }
    }
    return $top_level_categories;
}

function gf_get_second_level_categories($parent_id = null) {
    $categories = gf_get_categories();
    $top_level_ids = [];
    $second_level_categories = [];
    foreach ($categories as $category) {
        if (!$category->parent) {
            $top_level_ids[] = $category->term_id;
        }
    }
    foreach ($categories as $category) {
        if ($parent_id) {
            if ($category->parent == $parent_id) {
                $second_level_categories[] = $category;
            }
        } elseif (in_array($category->parent, $top_level_ids)) {
            $second_level_categories[] = $category;
        }
    }
    return $second_level_categories;
}

function gf_get_third_level_categories($parent_id = null) {
    $categories = gf_get_categories();
    $second_level_ids = [];
    foreach (gf_get_second_level_categories() as $cat) {
        $second_level_ids[] = $cat->term_id;
    }
    $third_level_categories = [];
    foreach ($categories as $category) {
        if ($parent_id) {
            if ($category->parent == $parent_id) {
                $third_level_categories[] = $category;
            }
        } elseif (in_array($category->parent, $second_level_ids)) {
            $third_level_categories[] = $category;
        }
    }
    return $third_level_categories;
}

function gf_check_level_of_category($cat_id) {
    $result = null;
    $top_level_ids = [];
    $second_level_ids = [];
    $third_level_ids = [];
    foreach (gf_get_top_level_categories() as $category) {
        $top_level_ids[] = $category->term_id;
    }
    foreach (gf_get_second_level_categories() as $category) {
        $second_level_ids[] = $category->term_id;
    }
    foreach (gf_get_third_level_categories() as $category) {
        $third_level_ids[] = $category->term_id;
    }
    if (in_array($cat_id, $top_level_ids)) {
        $result = 1;
    }
    if (in_array($cat_id, $second_level_ids)) {
        $result = 2;
    }
    if (in_array($cat_id, $third_level_ids)) {
        $result = 3;
    }
    return $result;
}

function gf_get_category_children_ids($slug) {
    $cat = get_term_by('slug', $slug, 'product_cat');
    $childrenIds = [];
    if ($cat) {
        $catChildren = get_term_children($cat->term_id, 'product_cat');
        $childrenIds[] = $cat->term_id;
        foreach ($catChildren as $child) {
            $childrenIds[] = $child;
        }
    }
    return $childrenIds;
}

/**
 * One time use function, in order to replace proper vendorid for products
 */
function gf_change_supplier_id_by_vendor_id() {
    global $wpdb;

    $failedMatchIds = [];
    $alreadySyncedIds = [];
//    for ($i = 0; $i < 10; $i++) {
    for ($i = 1; $i < 5; $i++) {
        $products_ids = wc_get_products(array(
            'limit' => 5000,
            'return' => 'ids',
            'paged' => $i
        ));
        foreach ($products_ids as $product_id) {
            if (get_post_meta($product_id, 'syncedVendors', true) != 1) {
                $vendorId = (int) get_post_meta($product_id, 'supplier', true);
                if ($vendorId === 0) {
                    var_dump($product_id);
                    continue;
                }

                //resolve duplicate vendors
                if ($vendorId === 142) {
                    $vendorId = 357;
                }
                if ($vendorId === 349) {
                    $vendorId = 350;
                }
                if ($vendorId === 11) {
                    $vendorId = 324;
                }
                if ($vendorId === 191) {
                    $vendorId = 192;
                }
                if ($vendorId === 356) {
                    $vendorId = 324;
                }
                if ($vendorId === 304) {
                    $vendorId = 391;
                }
                if ($vendorId === 78) {
                    $vendorId = 89;
                }
                if ($vendorId === 209) {
                    $vendorId = 208;
                }
                if ($vendorId === 287 || $vendorId === 286) {
                    $vendorId = 288;
                }
                $sql = "SELECT user_id FROM wp_usermeta WHERE meta_key = 'vendorid' AND meta_value = {$vendorId}";
                $userId = $wpdb->get_var($sql);
                if (!$userId) {
                    if (!in_array($vendorId, $failedMatchIds)) {
                        $failedMatchIds[$vendorId] = $product_id;
                    }
                    continue;
                }
                update_post_meta($product_id, 'supplier', $userId);
                add_post_meta($product_id, 'syncedVendors', true);
            } else {
                if (!in_array($product_id, $alreadySyncedIds)) {
                    $alreadySyncedIds[] = $product_id;
                }
                continue;
//                var_dump('product already synced' . );
//                die();
            }
        }
    }
    echo 'Nisu pronađeni dobavljachi za sledeće vendorid-eve: ' . count($failedMatchIds);
    echo '<ul>';
    foreach ($failedMatchIds as $vendorId => $ids) {
        echo '<li>' . $vendorId . print_r($ids, true) .'</li>';
    }
    echo '</ul>';

    echo 'Sledeći proizvodi su vec sinhronizovani: ' . count($alreadySyncedIds);
//    echo '<ul>';
//    foreach ($alreadySyncedIds as $syncedId) {
//        echo '<li>' . $syncedId . '</li>';
//    }
//    echo '</ul>';
}

