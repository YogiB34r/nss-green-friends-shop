<?php

namespace GF\Esir;

class ManualRefundAdmin
{
    public function init()
    {
        add_action('admin_menu', function () {
            $this->addMenuPage();
        });
    }
    private function addMenuPage(): void
    {
        add_submenu_page('nss-panel', 'Manualno refundiranje', 'Manualno refundiranje', 'manage_options', 'manual-refund',
            [$this, 'menuPage']);
    }

    public function menuPage(): void
    {
        include(__DIR__ . '/templates/manualRefund.php');
    }
}