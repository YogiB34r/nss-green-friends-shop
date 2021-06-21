<?php

function gf_remove_processing_status($statuses){
    if(isset($statuses['wc-processing'])){
        unset($statuses['wc-processing']);
    }
    if(isset($statuses['wc-pending'])){
        unset($statuses['wc-pending']);
    }
    if(isset($statuses['wc-cancelled'])){
        unset($statuses['wc-cancelled']);
    }
    if(isset($statuses['wc-failed'])){
        unset($statuses['wc-failed']);
    }
    return $statuses;
}